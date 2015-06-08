<?php

namespace Tokenly\CounterpartyTransactionParser;

use \Exception;

/*
* Parser
*/
class Parser
{

    static $B58_DIGITS    = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    static $UNSPENDABLE   = '1CounterpartyXXXXXXXXXXXXXXXUWLpVr';
    static $UNIT          = 100000000;
    static $PREFIX        = 'CNTRPRTY';
    static $PREFIX_LENGTH = 8;

    const DEBUG_LOGGING_ENABLED    = false;
    const DEFAULT_PROTOCOL_VERSION = 2;

    /**
     * parses a transaction and determines the counterparty transaction type
     * @param  array  $tx Transaction data from insight
     * @param  int|null  $protocol_version the procol version.  Leave blank for the latest protocol.
     * @return string|null Counterparty transaction type
     */
    public function lookupCounterpartyTransactionType(array $tx, $protocol_version=null) {
        $data = $this->parseBitcoinTransaction($tx, $protocol_version);
        if ($data === null) { return null; }
        return $data['type'];
    } 

    /**
     * Determine if the transaction is a counterparty transaction
     * @param  array  $tx Transaction data from insight
     * @param  int|null  $protocol_version the procol version.  Leave blank for the latest protocol.
     * @return boolean
     */
    public function isCounterpartySend(array $tx, $protocol_version=null) {
        return ($this->parseCounterpartyTransactionType($tx, $protocol_version) === 'send');
    }

    /**
     * parses a transaction and returns the raw counterparty data
     * @param  array $tx Transaction data from insight
     * @param  int|null  $protocol_version the procol version.  Leave blank for the latest protocol.
     * @return array transaction data including type, destination, asset and quantity  
     */
    public function parseBitcoinTransaction(array $tx, $protocol_version=null) {
        if ($protocol_version === null) { $protocol_version = self::DEFAULT_PROTOCOL_VERSION; }

        switch ($protocol_version) {
            case 1:
                return $this->parseBitcoinTransactionVersion1($tx);
            case 2:
                return $this->parseBitcoinTransactionVersion2($tx);
            
            default:
                throw new Exception("Unknown protocol version", 1);
        }
    }


    /**
     * Determine the protocol version by block index
     * @param  int $block_index The block height
     * @return integer protocol version (1 or 2)
     */
    public function protocolVersion($block_index) {
        if ($block_index !== null AND $block_index < 333500) {
            return 1;
        }

        // assume latest version
        return 2;
    }

    protected function parseBitcoinTransactionVersion1(array $tx) {
        try {
            $fee = 0;

            # Get destination output and data output.
            $destination = null;
            $btc_amount = null;
            $data = '';
            $pubkeyhash_encoding = false;

            foreach ($tx['vout'] as $vout) {
                $fee -= $vout['value'] * self::$UNIT;

                # Sum data chunks to get data. (Can mix OP_RETURN and multi-sig.)
                $asm = explode(' ', $vout['scriptPubKey']['asm']);
                if (count($asm) == 2 and $asm[0] == 'OP_RETURN') {
                    # OP_RETURN
                    // self::wlog("OP_RETURN");

                    $data_chunk = hex2bin($asm[1]);
                    $data .= $data_chunk;
                } else if (count($asm) == 5 and $asm[0] == '1' and $asm[3] == '2' and $asm[4] == 'OP_CHECKMULTISIG') {
                    # Multi-sig
                    // self::wlog("Multi-sig");

                    $data_pubkey = hex2bin($asm[2]);
                    self::wlog("\$data_pubkey=".self::dumpText($data_pubkey));
                    if ($data_pubkey === false) { continue; }

                    $data_chunk_length = unpack('c', substr($data_pubkey, 0, 1))[1];
                    $data_chunk = substr($data_pubkey, 1, $data_chunk_length + 1);
                    $data .= $data_chunk;

                } else if (count($asm) == 5) {
                    self::wlog("Other...");

                    $pubkeyhash_string = self::get_pubkeyhash($vout['scriptPubKey']);
                    $pubkeyhash = hex2bin($pubkeyhash_string);
                    if ($pubkeyhash === false) { continue; }

                    if (isset($tx['vin'][0]['coinbase'])) { throw new Exception("coinbase transaction", 1); }

                    $data_pubkey = self::arc4decrypt(hex2bin($tx['vin'][0]['txid']), $pubkeyhash);
                    self::wlog("data_pubkey=".self::dumpText($data_pubkey));
                    if (substr($data_pubkey, 1, 8) == self::$PREFIX or $pubkeyhash_encoding) {
                        $pubkeyhash_encoding = true;
                        $data_chunk_length = $data_pubkey[0];
                        $data_chunk = substr($data_pubkey, 1, $data_chunk_length + 1);
    
                        if (substr($data_chunk, -8) == self::$PREFIX) {
                            $data .= substr($data_chunk, 0, -8);
                            break;
                        } else {
                            $data .= $data_chunk;
                        }
                    }
                }

                # Destination is the first output before the data.
                if (!$destination and !$btc_amount and !$data) {
                    $address = self::get_address($vout['scriptPubKey']);
                    if ($address) {
                        $destination = $address;
                        $btc_amount = round($vout['value'] * self::$UNIT);
                    }
                }

            } // end foreach loop through vin

            self::wlog('$data='.self::dumpText($data));

            # Check for, and strip away, prefix (except for burns).
            if ($destination == self::$UNSPENDABLE) {
                // pass
            } else if (substr($data, 0, self::$PREFIX_LENGTH) == self::$PREFIX) {
                $data = substr($data, self::$PREFIX_LENGTH);
            } else {
                throw new Exception("no prefix", 1);
            }

            # Only look for source if data were found or destination is UNSPENDABLE, for speed.
            if (!$data and $destination != self::$UNSPENDABLE) {
                throw new Exception('no data and not unspendable', 1);
            }


            # Collect all possible source addresses; ignore coinbase transactions and anything but the simplest Pay‐to‐PubkeyHash inputs.
            $sources = [];
            foreach ($tx['vin'] as $vin) {
                # Loop through input transactions.
                if (isset($vin['coinbase'])) { throw new Exception("coinbase transaction", 1); }

                $vin_txid = $vin['txid'];
                $vin_utxo_offset = $vin['vout'];
                $sources[] = $vin['addr'];
            }
            $sources = array_unique($sources);
            if (count($sources) > 1) { throw new Exception("Multiple sources are not allowed", 1); }
            $source = $sources[0];

            return $this->parseTransactionData($data, $source, $destination);

        } catch (Exception $e) {
            self::wlog("ERROR: ".$e->getMessage()." at line ".$e->getLine());
            return null;
        }

        return null;
    }

    protected function parseBitcoinTransactionVersion2(array $tx) {

        // def arc4_decrypt(cyphertext):
        $arc4_decrypt_key = null;
        if (isset($tx['vin'][0]['txid'])) {
            $arc4_decrypt_key = hex2bin($tx['vin'][0]['txid']);
        }
        // $arc4_decrypt_key = substr($arc4_decrypt_key, 0, -1);
        self::wlog("\$tx['vin'][0]['txid']=".json_encode(isset($tx['vin'][0]['txid']) ? $tx['vin'][0]['txid'] : null, 192)." \$arc4_decrypt_key=".self::dumpText($arc4_decrypt_key));
        $fn_arc4_decrypt = function($cyphertext) use ($arc4_decrypt_key) {
            return self::arc4decrypt($arc4_decrypt_key, $cyphertext);
        };

        // def get_opreturn(asm):
        $fn_get_opreturn = function($asm) {
            if (count($asm) == 2 and $asm[0] == 'OP_RETURN') {
                $pubkeyhash = hex2bin($asm[1]);
                return $pubkeyhash;
            }
            throw new Exception("Invalid OP_RETURN", 1);
        };

        // def decode_opreturn(asm):
        $fn_decode_opreturn = function($asm) use ($fn_get_opreturn, $fn_arc4_decrypt) {
            $chunk = $fn_get_opreturn($asm);
            $data = $fn_arc4_decrypt($chunk);
            if (substr($data, 0, self::$PREFIX_LENGTH) == self::$PREFIX) {
                $destination = null;
                $data = substr($data, self::$PREFIX_LENGTH);
            } else {
                throw new Exception("unrecognised OP_RETURN output", 1);
            }
            return [$destination, $data];
        };


        // def decode_checksig(asm):
        $fn_decode_checksig = function($asm) use ($fn_arc4_decrypt) {
            $pubkeyhash_text = self::get_pubkeyhash_from_asm($asm);
            $pubkeyhash = hex2bin($pubkeyhash_text);
            $chunk = $fn_arc4_decrypt($pubkeyhash);
            self::wlog("\$fn_decode_checksig \$pubkeyhash=".self::dumpText($pubkeyhash)." \$chunk=".self::dumpText($chunk));
            if (substr($chunk, 1, 8) == self::$PREFIX) {
                $data_chunk_length = $chunk[0];
                $data_chunk = substr($chunk, 1, $data_chunk_length + 1);
                $destination = null;
                $data = substr($data_chunk, 8);
            } else {
                $pubkeyhash = bin2hex($pubkeyhash);
                $ADDRESSVERSION = "00";
                $destination = self::base58_check_encode($pubkeyhash_text, $ADDRESSVERSION);
                $data = null;
                self::wlog("\$fn_decode_checksig \$destination=".self::dumpText($destination));
            }

            return [$destination, $data];
        };

        // def decode_checkmultisig(asm):
        $fn_decode_checkmultisig = function($asm) use ($fn_arc4_decrypt) {
            list($pubkey_strings, $signatures_required) = self::get_checkmultisig($asm);
            self::wlog("\$pubkey_strings=".json_encode($pubkey_strings, 192)."\n\$signatures_required=".json_encode($signatures_required, 192));

            $chunk = '';
            foreach(array_slice($pubkey_strings, 0, -1) as $pubkey) { # (No data in last pubkey.)
                $chunk .= substr(hex2bin($pubkey), 1, -1); # Skip sign byte and nonce byte.
            }
            $chunk = $fn_arc4_decrypt($chunk);
            self::wlog("\$chunk=".self::dumpText($chunk));
            if (substr($chunk, 1, self::$PREFIX_LENGTH) == self::$PREFIX) {
                // data
                # Padding byte in each output (instead of just in the last one) so that encoding methods may be mixed. Also, it’s just not very much data.
                $chunk_length = unpack('c', substr($chunk, 0, 1))[1];
                $chunk = substr($chunk, 1, $chunk_length);
                $destination = null;
                $data = substr($chunk, self::$PREFIX_LENGTH);
            } else {
                $pubkeyhashes = [];
                foreach($pubkey_strings as $pubkey) {
                    $pubkeyhashes[] = self::pubkey_to_pubkeyhash($pubkey);
                }
                $destination = implode('_', array_merge([$signatures_required], $pubkeyhashes, [count($pubkeyhashes)]));
                $data = null;
            }

            return [$destination, $data];

        };


        try {
            // ignore coinbase txs
            if (isset($tx['vin'][0]['coinbase'])) { throw new Exception("coinbase transaction", 1); }


            $fee = 0;

            # Get destination output and data output.
            $destinations = [];
            $btc_amount = null;
            $fee = 0;
            $data = '';

            foreach ($tx['vout'] as $vout) {
                # Fee is the input values minus output values.
                $output_value = $vout['value'];
                $fee -= $output_value * self::$UNIT;

                // get asm
                $asm = explode(' ', $vout['scriptPubKey']['asm']);
                // self::wlog("\$asm=".json_encode($asm, 192));

                if ($asm[0] == 'OP_RETURN') {
                    list($new_destination, $new_data) = $fn_decode_opreturn($asm);
                } else if ($asm[count($asm) - 1] == 'OP_CHECKSIG') {
                    self::wlog("====== BEGIN OP_CHECKSIG      ======");
                    list($new_destination, $new_data) = $fn_decode_checksig($asm);
                    self::wlog("OP_CHECKSIG \$new_destination=".self::dumpText($new_destination)." \$new_data=".self::dumpText($new_data));
                } else if ($asm[count($asm) - 1] == 'OP_CHECKMULTISIG') {
                    self::wlog("====== BEGIN OP_CHECKMULTISIG ======");
                    list($new_destination, $new_data) = $fn_decode_checkmultisig($asm);
                    self::wlog("OP_CHECKMULTISIG \$new_destination=".self::dumpText($new_destination)." \$new_data=".self::dumpText($new_data));
                } else {
                    throw new Exception("unrecognised output type", 1);
                }
                if ($new_destination and $new_data) { throw new Exception("Found \$new_destination and \$new_data", 1); }
                if (!$new_destination and !$new_data) { throw new Exception("Did not find \$new_destination or \$new_data", 1); }

                # All destinations come before all data.
                if (!$data and !$new_data and !self::isUnspendable($destinations)) {
                    $destinations[] = $new_destination;
                    $btc_amount += $output_value;
                } else {
                    if ($new_destination) {
                        // change
                        self::wlog("Change to $new_destination");
                        break;
                    } else {
                        $data .= $new_data;
                    }
                }

            } // end foreach loop through vout

            self::wlog('$data='.self::dumpText($data));

            # Only look for source if data were found or destination is `UNSPENDABLE`,
            # for speed.
            if (!$data and !self::isUnspendable($destinations)) {
                throw new Exception('no data and not unspendable', 1);
            }

            # Collect all (unique) source addresses.
            $sources = [];
            foreach ($tx['vin'] as $vin) {
                $vin_txid = $vin['txid'];
                $vin_utxo_offset = $vin['vout'];
                $sources[] = $vin['addr'];
            }

            $sources = array_unique($sources);

            // we don't handle multiple sources yet
            if (count($sources) > 1) { throw new Exception("Multiple sources are not allowed", 1); }

            return $this->parseTransactionData($data, $sources, $destinations);

        } catch (Exception $e) {
            self::wlog("ERROR: ".$e->getMessage()." at line ".$e->getLine());
            return null;
        }

        return null;
    }

    /**
     * map type id number to counterparty transaction type
     * @param  int $type_id The type id
     * @return string|null the type
     */
    protected static function typeIDToType($type_id) {
        if ($type_id === 0) {
            return 'send';
        } else if ($type_id === 10) {
            return 'order';
        } else if ($type_id === 11) {
            return 'btcpay';
        } else if ($type_id === 20) {
            return 'issuance';
        } else if ($type_id === 30) {
            return 'broadcast';
        } else if ($type_id === 40) {
            return 'bet';
        } else if ($type_id === 50) {
            return 'dividend';
        } else if ($type_id === 70) {
            return 'cancel';
        } else if ($type_id === 21) {
            return 'callback';
        } else if ($type_id === 80) {
            return 'rps';
        } else if ($type_id === 81) {
            return 'rpsresolve';
        }

        return null;
    }

    protected function parseTransactionData($binary_data, $source, $destination) {
        list($type_id, $asset_id_hi, $asset_id_lo, $quantity_hi, $quantity_lo) = array_values(unpack('I1t/N4aq', $binary_data));
        $type = self::typeIDToType($type_id);
        $asset_id = $asset_id_hi << 32 | $asset_id_lo; 
        $quantity = $quantity_hi << 32 | $quantity_lo; 


        $parsed_data = [
            'type'         => $type,
            'sources'      => is_array($source) ? $source : [$source],
            'destinations' => is_array($destination) ? $destination : [$destination],
        ];
        if ($type !== 'send') {
            return $parsed_data;
        }

        $parsed_data['quantity'] = $quantity;
        $parsed_data['asset'] = self::asset_name($asset_id);
        return $parsed_data;


        // 'tx_index': tx['tx_index'],
        // 'tx_hash': tx['tx_hash'],
        // 'block_index': tx['block_index'],
        // 'source': tx['source'],
        // 'destination': tx['destination'],
        // 'asset': asset,
        // 'quantity': quantity,
        // 'status': status,

    }
    

    protected static function get_pubkeyhash($scriptpubkey) {
        $asm = explode(' ', $scriptpubkey['asm']);
        return self::get_pubkeyhash_from_asm($asm);
    }
    protected static function get_pubkeyhash_from_asm($asm) {
        if (count($asm) != 5 or $asm[0] != 'OP_DUP' or $asm[1] != 'OP_HASH160' or $asm[3] != 'OP_EQUALVERIFY' or $asm[4] != 'OP_CHECKSIG') {
            return false;
        }
        return $asm[2];
    }

    protected static function get_address($scriptpubkey) {
        $pubkeyhash = self::get_pubkeyhash($scriptpubkey);
        if (!$pubkeyhash) { return false; }

        $ADDRESSVERSION = "00";

        $address = self::base58_check_encode($pubkeyhash, $ADDRESSVERSION);

        # Test decoding of address.
        if ($address != self::$UNSPENDABLE and hex2bin($pubkeyhash) != hex2bin(self::base58_check_decode($address, $ADDRESSVERSION))) {
            return false;
        }

        return $address;

    }
    protected static function get_checkmultisig($asm) {
        # N‐of‐2
        if (count($asm) == 5 and $asm[3] == 2 and $asm[4] == '') {
            $pubkeys = array_slice($asm, 1, 2);
            $signatures_required = (int)$asm[0];
            return [$pubkeys, $signatures_required];
        }
        # N‐of‐3
        if (count($asm) == 6 and $asm[4] == 3 and $asm[5] == 'OP_CHECKMULTISIG') {
            $pubkeys = array_slice($asm, 1, 3);
            $signatures_required = (int)$asm[0];
            return [$pubkeys, $signatures_required];
        }
        throw new Exception("invalid OP_CHECKMULTISIG", 1);
    }


// def pubkey_to_pubkeyhash(pubkey):
//     pubkeyhash = hash160(pubkey)
//     pubkey = base58_check_encode(binascii.hexlify(pubkeyhash).decode('utf-8'), config.ADDRESSVERSION)
//     return pubkey
//    
    protected static function pubkey_to_pubkeyhash($pubkey) {
        $pubkeyhash = hash('ripemd160', $pubkey);
        $ADDRESSVERSION = "00";
        $pubkey = self::base58_check_encode($pubkeyhash, $ADDRESSVERSION);
        return $pubkey;
    }


    protected static function arc4decrypt($key, $encrypted_text)
    {
        $init_vector = '';
        return mcrypt_decrypt(MCRYPT_ARCFOUR, $key, $encrypted_text, MCRYPT_MODE_STREAM, $init_vector);
    }

    public static function base58_check_encode($hash160, $address_version) {
        $address = self::hash160_to_address($hash160, $address_version);


        return $address;
    }

    public static function base58_check_decode($address, $address_version) {

        // Check the address is decoded correctly.
        $decode = self::base58_decode($address);
        if (strlen($decode) !== 50) {
            return FALSE;
        }

        // Compare the version.
        $version = substr($decode, 0, 2);
        if (hexdec($version) > hexdec($address_version)) {
            return FALSE;
        }

        // Finally compare the checksums.
        if (substr($decode, -8) == substr(self::hash256(substr($decode, 0, 42)), 0, 8)) {
            return substr($decode, 2, -8);
        }

        return FALSE;
    }

    public static function hash160_to_address($hash160, $address_version) {
        $hash160 = $address_version . $hash160;
        return self::base58_encode_checksum($hash160);
    }

    public static function base58_encode_checksum($hex) {
        $checksum = self::hash256($hex);
        $checksum = substr($checksum, 0, 8);
        $hash = $hex.$checksum;
        return self::base58_encode($hash);
    }

    public static function hash256($string) {
        $bs = @pack("H*", $string);
        return hash("sha256", hash("sha256", $bs, true));
    }


    public static function base58_encode($hex) {
        if(strlen($hex) == 0)
            return '';

        // Convert the hex string to a base10 integer
        $num = gmp_strval(gmp_init($hex, 16), 58);

        // Check that number isn't just 0 - which would be all padding.
        if($num != '0') {
            $num = strtr($num
            , '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv'
            , self::$B58_DIGITS);
        } else {
            $num = '';
        }

        // Pad the leading 1's
        $pad = ''; $n = 0;
        while (substr($hex, $n, 2) == '00') {
            $pad .= '1';
            $n += 2;
        }

        return $pad . $num;
    }


    /**
     * Base58 Decode
     *
     * This function accepts a base58 encoded string, and decodes the
     * string into a number, which is converted to hexadecimal. It is then
     * padded with zero's.
     *
     * @param $base58
     * @return string
     */
    protected static function base58_decode($base58)
    {
        $origbase58 = $base58;
        $return = "0";
        for ($i = 0; $i < strlen($base58); $i++) {
            $return = gmp_add(gmp_mul($return, 58), strpos(self::$B58_DIGITS, $base58[$i]));
        }
        $return = gmp_strval($return, 16);
        for ($i = 0; $i < strlen($origbase58) && $origbase58[$i] == "1"; $i++) {
            $return = "00" . $return;
        }
        if (strlen($return) % 2 != 0) {
            $return = "0" . $return;
        }
        return $return;
    }


    protected static function dhash($x) {
        return hash('sha256', hash('sha256', $x));
    }

    protected static function asset_name($asset_id) {
        // BTC = 'BTC'
        // XCP = 'XCP'
        if ($asset_id === 0) { return 'BTC'; }
        if ($asset_id === 1) { return 'XCP'; }

        $b26_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($asset_id < pow(26, 3)) { throw new Exception("asset ID was too low", 1); }

        # Divide that integer into Base 26 string.
        $asset_name = '';
        $n = gmp_init($asset_id);
        while (gmp_cmp($n, 0) > 0) {
            list($n, $r) = gmp_div_qr($n, 26, GMP_ROUND_ZERO);
            $asset_name = substr($b26_digits, gmp_intval($r), 1).$asset_name;
        }
        return $asset_name;
    }


    protected static function isUnspendable($destinations) {
        return (count($destinations) == 1 AND $destinations[0] == self::$UNSPENDABLE);
    }


    protected static function wlog($text) {
        if (!Parser::DEBUG_LOGGING_ENABLED) { return; }
        $line = debug_backtrace()[0]['line'];
        echo "[Line $line]: ".rtrim($text)."\n";
    }


    protected static function dumpText($text) {
        $out = '';
        $length = strlen($text);
        for($i=0;$i<$length;++$i) {
            $char = $text[$i];
            $ord = ord($char);
            if ($ord < 32 OR $ord > 126) {
                $out .= "\x".dechex($ord);
            } else {
                $out .= $char;
            }
        }
        return $out;
    }

}


