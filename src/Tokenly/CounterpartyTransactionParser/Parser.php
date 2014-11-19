<?php

namespace Tokenly\CounterpartyTransactionParser;

use \Exception;

/*
* Parser
*/
class Parser
{

    static $B58_DIGITS  = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    static $UNSPENDABLE = '1CounterpartyXXXXXXXXXXXXXXXUWLpVr';
    static $UNIT        = 100000000;
    static $PREFIX      = 'CNTRPRTY';

    const DEBUG_ENABLED = false;

    /**
     * parses a transaction and determines the counterparty transaction type
     * @param  array  $tx Transaction data from insight
     * @return string|null Counterparty transaction type
     */
    public function lookupCounterpartyTransactionType(array $tx) {
        $data = $this->parseBitcoinTransaction($tx);
        if ($data === null) { return null; }

        $type_id = unpack('l', $data)[1];
        $type = self::typeIDToType($type_id);
        return $type;
    } 

    /**
     * Determine if the transaction is a counterparty transaction
     * @param  array  $tx Transaction data from insight
     * @return boolean
     */
    public function isCounterpartySend(array $tx) {
        return ($this->parseCounterpartyTransactionType($tx) === 'send');
    }

    /**
     * parses a transaction and returns the raw counterparty data
     * @param  array  $tx [description]
     * @return [type]     [description]
     */
    public function parseBitcoinTransaction(array $tx) {
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
            } else if (substr($data, 0, strlen(self::$PREFIX)) == self::$PREFIX) {
                $data = substr($data, strlen(self::$PREFIX));
            } else {
                throw new Exception("no prefix", 1);
            }

            # Only look for source if data were found or destination is UNSPENDABLE, for speed.
            if (!$data and $destination != self::$UNSPENDABLE) {
                throw new Exception('no data and not unspendable', 1);
            }

            self::wlog("returning data: ".self::dumpText($data));
            return $data;

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
    public static function typeIDToType($type_id) {
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
    

    protected static function get_pubkeyhash($scriptpubkey) {
        $asm = explode(' ', $scriptpubkey['asm']);
        if (count($asm) != 5 or $asm[0] != 'OP_DUP' or $asm[1] != 'OP_HASH160' or $asm[3] != 'OP_EQUALVERIFY' or $asm[4] != 'OP_CHECKSIG') {
            return false;
        }
        return $asm[2];
    }

    protected static function get_address($scriptpubkey) {
        $pubkeyhash = self::get_pubkeyhash($scriptpubkey);
        if (!$pubkeyhash) { return false; }

        $ADDRESSVERSION = "\x00";

        $address = self::base58_check_encode($pubkeyhash, $ADDRESSVERSION);

        # Test decoding of address.
        if ($address != self::$UNSPENDABLE and hex2bin($pubkeyhash) != self::base58_check_decode($address, $ADDRESSVERSION)) {
            return false;
        }

        return $address;

    }
    protected static function arc4decrypt($key, $encrypted_text)
    {
        $init_vector = '';
        return mcrypt_decrypt(MCRYPT_ARCFOUR, $key, $encrypted_text, MCRYPT_MODE_STREAM, $init_vector);
    }

    protected static function base58_check_encode($original, $version) {
        $b = hex2bin($original);
        $d = $version . $b;

        $binary = $d . substr(self::dhash($d), 0, 4);
        $res = self::base58_encode(bin2hex($binary));
        $address = $res;

        if ($original !== bin2hex(self::base58_check_decode($address, $version))) {
            throw new Exception("encoded address does not decode properly", 1);
        }

        return $address;
    }

    protected static function base58_check_decode($s, $version) {
        $k = hex2bin(self::base58_decode($s));
        $addrbyte = substr($k, 0, 1);
        $data = substr($k, 1, -4);
        $chk0 = substr($k, -4);

        if ($addrbyte !== $version) {
            throw new Exception("incorrect version byte", 1);
        }

        //     chk1 = dhash(addrbyte + data)[:4]
        $chk1 = substr(self::dhash($addrbyte . $data), 0, 4);

        if ($chk0 != $chk1) {
            throw new Exception("Checksum mismatch: $chk0 ≠ $chk1", 1);
        }

        return $data;
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
    public static function base58_decode($base58)
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

    /**
     * Base58 Encode
     *
     * Encodes a $hex string in base58 format. Borrowed from prusnaks
     * addrgen code: https://github.com/prusnak/addrgen/blob/master/php/addrgen.php
     *
     * @param    string $hex
     * @return    string
     * @author    Pavel Rusnak
     */
    protected static function base58_encode($hex)
    {
        if (strlen($hex) == 0) {
            return '';
        }
        // Convert the hex string to a base10 integer
        $num = gmp_strval(gmp_init($hex, 16), 58);
        // Check that number isn't just 0 - which would be all padding.
        if ($num != '0') {
            $num = strtr(
                $num,
                '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv',
                self::$B58_DIGITS
            );
        } else {
            $num = '';
        }
        // Pad the leading 1's
        $pad = '';
        $n = 0;
        while (substr($hex, $n, 2) == '00') {
            $pad .= '1';
            $n += 2;
        }
        return $pad . $num;
    }

    protected static function dhash($x) {
        return hash('sha256', hash('sha256', $x));
    }


    protected static function wlog($text) {
        if (!Parser::DEBUG_ENABLED) { return; }
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


