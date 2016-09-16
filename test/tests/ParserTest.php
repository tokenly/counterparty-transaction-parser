<?php

use Tokenly\CounterpartyTransactionParser\Parser;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class ParserTest extends \PHPUnit_Framework_TestCase
{
    static $B58_DIGITS  = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    const SATOSHI = 100000000;

    public function testParser1() {
        $parser = new Parser();
        
        $tx_data = $this->getSampleCounterpartyTransaction();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 1);
        PHPUnit::assertNotEmpty($counterparty_data);

        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('1F9UWGP1YwZsfXKogPFST44CT3WYh4GRCz', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals('LTBCOIN', $counterparty_data['asset']);
        PHPUnit::assertEquals(53383451959, $counterparty_data['quantity']);
    }

    public function testParser2() {
        $parser = new Parser();
        
        $tx_data = $this->getSampleCounterpartyTransaction2();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 1);
        PHPUnit::assertNotEmpty($counterparty_data);

        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('17XotRS4RaeG6EAshBrWE4KMfizaqfLk5T', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('1Mh9zGQPS1HRLU8ivZ52MaCcomrGGEyJQs', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals('TESTCURRENCY', $counterparty_data['asset']);
        PHPUnit::assertEquals(1000000000, $counterparty_data['quantity']);
    }

    public function testProtocol2Parser() {
        $parser = new Parser();
        
        $tx_data = $this->getSampleCounterpartyTransactionProtocol2();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);

        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('12pv1K6LTLPFYXcCwsaU7VWYRSX7BuiF28', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals('SOUP', $counterparty_data['asset']);
        PHPUnit::assertEquals(1000 * self::SATOSHI, $counterparty_data['quantity']);


        // 2 SOUP

        $tx_data = $this->getSampleCounterpartyTransaction3();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);

        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('1291Z6hofAAvH8E886cN9M5uKB1VvwBnup', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('1FEbYaghvr7V53B9csjQTefUtBBQTaDFvN', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals('SOUP', $counterparty_data['asset']);
        PHPUnit::assertEquals(2 * self::SATOSHI, $counterparty_data['quantity']);
    }

    public function testOpReturnParser() {
        $parser = new Parser();

        // ----------------------------------------------------------------
        // 1 SOUP

        $tx_data = $this->getSampleCounterpartyTransaction4();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('1MFHQCPGtcSfNPXAS6NryWja3TbUN9239Y', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('1Q7VHJDEzVj7YZBVseQWgYvVj3DWDCLwDE', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals(1 * self::SATOSHI, $counterparty_data['quantity']);
        PHPUnit::assertEquals('SOUP', $counterparty_data['asset']);


        // ----------------------------------------------------------------
        // 50 SOUP

        $tx_data = $this->getSampleCounterpartyTransaction5();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('12iVwKP7jCPnuYy7jbAbyXnZ3FxvgLwvGK', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('1KUsjZKrkd7LYRV7pbnNJtofsq1HAiz6MF', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals(50 * self::SATOSHI, $counterparty_data['quantity']);
        PHPUnit::assertEquals('SOUP', $counterparty_data['asset']);


        // ----------------------------------------------------------------
        // 0.5 XCP 

        $tx_data = $this->getSampleCounterpartyTransaction6();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals(['1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1'], $counterparty_data['sources']);
        PHPUnit::assertEquals(['19nMhSywqDVkPaSjczSp9dm1SEUBukEsJP'], $counterparty_data['destinations']);
        PHPUnit::assertEquals(0.5 * self::SATOSHI, $counterparty_data['quantity']);
        PHPUnit::assertEquals('XCP', $counterparty_data['asset']);


        // ----------------------------------------------------------------
        // 77,119 TATIANACOIN

        $tx_data = $this->getSampleCounterpartyTransaction7();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals(['1LHpvZP8QV3CBuDryYdgUs56sd6UYvXuGS'], $counterparty_data['sources']);
        PHPUnit::assertEquals(['1Max4vpkTbFgMjDpfaEjfPcdwd3ktTBQ4o'], $counterparty_data['destinations']);
        PHPUnit::assertEquals(77119 * self::SATOSHI, $counterparty_data['quantity']);
        PHPUnit::assertEquals('TATIANACOIN', $counterparty_data['asset']);


        // ----------------------------------------------------------------
        // 20 CLEFCARD

        $tx_data = $this->getSampleCounterpartyTransaction8();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals(['17ZiuvTg4ye1BouogSHj8Uqn6ZnH28BipR'], $counterparty_data['sources']);
        PHPUnit::assertEquals(['1GLEGqSaGieJFdLG4Ws94jHnZqkL5Favi5'], $counterparty_data['destinations']);
        PHPUnit::assertEquals(20 * self::SATOSHI, $counterparty_data['quantity']);
        PHPUnit::assertEquals('CLEFCARD', $counterparty_data['asset']);

        // ----------------------------------------------------------------
        // 677.87455108 LTBCOIN

        $tx_data = $this->getSampleCounterpartyTransaction9();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals(['14vmJJYa8y1dLBEgnTcq6xM2P3e1BBw39c'], $counterparty_data['sources']);
        PHPUnit::assertEquals(['15fx1Gqe4KodZvyzN6VUSkEmhCssrM1yD7'], $counterparty_data['destinations']);
        PHPUnit::assertEquals(67787455108, $counterparty_data['quantity']);
        PHPUnit::assertEquals('LTBCOIN', $counterparty_data['asset']);
    }

    public function testTransactionType() {
        $parser = new Parser();
        
        $tx_data = $this->getSampleCounterpartyTransaction();
        $type = $parser->lookupCounterpartyTransactionType($tx_data, 1);
        PHPUnit::assertEquals('send', $type);
    }

    public function testBitcoinTransaction() {
        $parser = new Parser();
        
        $tx_data = $this->getSampleBitcoinTransaction();
        $type = $parser->lookupCounterpartyTransactionType($tx_data, 1);
        PHPUnit::assertNull($type);
    }

    public function testIssuanceParser() {
        $parser = new Parser();
        
        $tx_data = $this->getIssuanceTransaction1();
        $type = $parser->lookupCounterpartyTransactionType($tx_data);
        PHPUnit::assertEquals('issuance', $type);

        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertEquals("LTBCOIN", $counterparty_data['asset']);
        PHPUnit::assertEquals("Crypto-Rewards Program http://ltbcoin.com", $counterparty_data['description']);
        PHPUnit::assertEquals(140952713000000, $counterparty_data['quantity']);
        PHPUnit::assertEquals(true, $counterparty_data['divisible']);

    }

    public function testNumericIssuanceParser() {
        $parser = new Parser();

        $tx_data = $this->getIssuanceTransaction2();
        $type = $parser->lookupCounterpartyTransactionType($tx_data);
        PHPUnit::assertEquals('issuance', $type);

        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertEquals("A229152867617021630", $counterparty_data['asset']);
        PHPUnit::assertEquals(500000000000, $counterparty_data['quantity']);
        PHPUnit::assertEquals(true, $counterparty_data['divisible']);

    }

    public function testOrderParser() {
        $parser = new Parser();

        $tx_data = $this->getOrder1();
        $type = $parser->lookupCounterpartyTransactionType($tx_data);
        PHPUnit::assertEquals('order', $type);

    }

    public function testBroadcastParser1() {
        $parser = new Parser();

        $tx_data = $this->getBroadcast1();
        $data = $parser->parseBitcoinTransaction($tx_data);
        // echo "\$data: ".json_encode($data, 192)."\n";

        PHPUnit::assertEquals('1Pq7HXD5i9ZXXfnt71xPmtEkyATWc57bRw', $data['source']);
        PHPUnit::assertEquals('broadcast', $data['type']);
        PHPUnit::assertEquals(1473158100, $data['timestamp']);
        PHPUnit::assertEquals(-1, $data['value']);
        PHPUnit::assertEquals(0, $data['fee_fraction']);
        PHPUnit::assertEquals("BLOCKSCAN VERIFY-ADDRESS 7a4exlyjw97esst", $data['message']);
    }

    public function testBroadcastParser2() {
        $parser = new Parser();

        $tx_data = $this->getBroadcast2();
        $data = $parser->parseBitcoinTransaction($tx_data);
        // echo "\$data: ".json_encode($data, 192)."\n";

        PHPUnit::assertEquals('1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu', $data['source']);
        PHPUnit::assertEquals('broadcast', $data['type']);
        PHPUnit::assertEquals(1471660320, $data['timestamp']);
        PHPUnit::assertEquals(-1, $data['value']);
        PHPUnit::assertEquals("This is a test of the emergency broadcast system. ABC123", $data['message']);
    }

    public function testAssetNames() {
        $parser = new Parser();
        PHPUnit::assertEquals("LTBCOIN", $parser->asset_name('3624321493'));
        PHPUnit::assertEquals("BAAAAAAAAAAAA", $parser->asset_name('95428956661682176'));
        PHPUnit::assertEquals("A95428956661682177", $parser->asset_name('95428956661682177'));
        PHPUnit::assertEquals("A95428956661682177", $parser->asset_name('95428956661682177'));
    }

    // ------------------------------------------------------------------------

    protected function getSampleCounterpartyTransaction() {
        // 533.83451959 LTBCOIN
        return json_decode('{"txid":"1886737bb2a4be1af89b1d0e5af427ef2a7fc439e2ed10a42d3efeb1f71b69aa","version":1,"locktime":0,"vin":[{"txid":"26bc3e4933c68d503d0c24bc039a64ace18b2899dc54f799a80f20fc047d7688","vout":2,"scriptSig":{"asm":"3045022100b1514287d58b56c8bb2df00349cdebd1c7fded0d7fe92320743dd631836ef62002200f56062249ce5a64b81d0921ac65151633c1c54dea3a93bc51844863d201d87301 0370a00e36f0ca37c2d80631e0209ede134dbd927d50a364c6747f9c5f3c2c7a9c"},"sequence":4294967295,"n":0,"addr":"1F9UWGP1YwZsfXKogPFST44CT3WYh4GRCz","valueSat":9114000,"value":0.09114,"doubleSpentTxID":null}],"vout":[{"value":"0.00001250","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 c56cb39f9b289c0ec4ef6943fa107c904820fe09 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD"]}},{"value":"0.00001250","n":1,"scriptPubKey":{"asm":"1 0370a00e36f0ca37c2d80631e0209ede134dbd927d50a364c6747f9c5f3c2c7a9c 1c434e5452505254590000000000000000d806c1d50000000c6de6d53700000000 2 OP_CHECKMULTISIG","reqSigs":1,"type":"multisig","addresses":["1F9UWGP1YwZsfXKogPFST44CT3WYh4GRCz","1HT7xU2Ngenf7D4yocz2SAcnNLW7rK8d4E"]}},{"value":"0.09108000","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 9b2c0c5f30a2dde09c2a9d3618ba390c9a688754 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1F9UWGP1YwZsfXKogPFST44CT3WYh4GRCz"]},"spentTxId":"fbd318eb157becef3e26460756eaee2f4910d8995d23414ce1938563ecc61784","spentIndex":0,"spentTs":1416091287}],"blockhash":"00000000000000000347e702fdc4d6ed74dca01844857deb5fec560c25b14d51","confirmations":25,"time":1416091287,"blocktime":1416091287,"valueOut":0.091105,"size":306,"valueIn":0.09114,"fees":0.000035}', true);
    }

    protected function getSampleCounterpartyTransaction2() {
        // 533.83451959 LTBCOIN
        return json_decode('{"txid":"c57679bfc68a62025df34303b914aa22bc7ebd840e148460a6be2fa4dc1cc9b5","version":1,"locktime":0,"vin":[{"txid":"1aedd05e9c610329f972654dd0b42df1f932db6915e15a6f2f799efca9ab84bf","vout":2,"scriptSig":{"asm":"3045022100de88671974012e75ae17672d06eaf797927233c7cae4c6f2e87cdacc5843bde50220017be96f8dae45678495cdba270672627f9e3a3b4b72fd68a33237a876f275d301 02dc751a9373996e6ba4b5050f99d07a7c289004adeac1ab7dff66975b69deecfc"},"sequence":4294967295,"n":0,"addr":"17XotRS4RaeG6EAshBrWE4KMfizaqfLk5T","valueSat":18872000,"value":0.18872,"doubleSpentTxID":null}],"vout":[{"value":"0.00007800","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 e2faa809bd83e00281fc381ae488590132db8cab OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1Mh9zGQPS1HRLU8ivZ52MaCcomrGGEyJQs"]}},{"value":"0.00007800","n":1,"scriptPubKey":{"asm":"1 02dc751a9373996e6ba4b5050f99d07a7c289004adeac1ab7dff66975b69deecfc 1c434e5452505254590000000000fa1f18a3ed95f0000000003b9aca0000000000 2 OP_CHECKMULTISIG","reqSigs":1,"type":"multisig","addresses":["17XotRS4RaeG6EAshBrWE4KMfizaqfLk5T","1HT7xU2Ngenf7D4yocz2SAcnNLW7rK8d4E"]}},{"value":"0.18846400","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 47a45f5bef95b261d5ba654c47f198b75077616b OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["17XotRS4RaeG6EAshBrWE4KMfizaqfLk5T"]},"spentTxId":"8031d1e1d12d38d3fc712ee47f7364c1b6979c8b2422955c2eccd9d22642105a","spentIndex":0,"spentTs":1416531752}],"blockhash":"00000000000000001acb1484131a0b69c7666e1a9b94e483babae59adf1fe160","confirmations":7,"time":1416531752,"blocktime":1416531752,"valueOut":0.18862,"size":306,"valueIn":0.18872,"fees":0.0001}', true);
    }

    protected function getSampleBitcoinTransaction() {
        // 2 SOUP
        return json_decode('{"txid":"cf9d9f4d53d36d9d34f656a6d40bc9dc739178e6ace01bcc42b4b9ea2cbf6741","version":1,"locktime":0,"vin":[{"txid":"cc669b824186886407ad7edd46796437e20ad73c89080420c45e5803f917228d","vout":2,"scriptSig":{"asm":"3045022100a37bcfd3087fa4ba9480ce09c7adf02ba3ce2208d6170b42e50b5b2633b91ee6022025d409d3d9dae0a159982c7ab079787948b6b6c5f87fa583d3886ebf1e074c8901 02f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6"},"sequence":4294967295,"n":0,"addr":"1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1","valueSat":781213,"value":0.00781213,"doubleSpentTxID":null}],"vout":[{"value":"0.00400000","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 c56cb39f9b289c0ec4ef6943fa107c904820fe09 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD"]},"spentTxId":"e90bc279294d704d09b227ad0e37459f61cccb85008605656dc8b024235eefe8","spentIndex":2,"spentTs":1403958484},{"value":"0.00361213","n":1,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 6ca4b6b20eac497e9ca94489c545a3372bdd2fa7 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1"]},"spentTxId":"3587bfa8d96c10b6696728651900db2ad6b41321ea44f26693de4f90d2b63526","spentIndex":0,"spentTs":1405081243}],"blockhash":"00000000000000003a1e5abc2d7af7f38a614d2fcbafe309b7e8aa147d508a9c","confirmations":22369,"time":1403957896,"blocktime":1403957896,"valueOut":0.00761213,"size":226,"valueIn":0.00781213,"fees":0.0002}', true);
    }

    protected function getSampleCounterpartyTransactionProtocol2() {
        return json_decode('{"txid":"e0082d1fc37172ccf0f5ebfc3cc54291e463384712f44f32ba4996c02045966f","version":1,"locktime":0,"vin":[{"txid":"8fd9f689f158a426867215dbdee58e9eab6c818097d4bf2bcf0bd1458f3c55ab","vout":2,"scriptSig":{"asm":"3045022100a178c9accd7972cfe30a03c98ff5f684bdf0b144eb415f4a4b7fcff596283f720220267f68a6413093b97a42ed5c2f2811193c1bbdd07d668e3076f99751044c347a01 02f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6","hex":"483045022100a178c9accd7972cfe30a03c98ff5f684bdf0b144eb415f4a4b7fcff596283f720220267f68a6413093b97a42ed5c2f2811193c1bbdd07d668e3076f99751044c347a012102f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6"},"sequence":4294967295,"n":0,"addr":"1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1","valueSat":95270,"value":0.0009527,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 1407ec32be440f32fc70f4eea810acd98f32aa32 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9141407ec32be440f32fc70f4eea810acd98f32aa3288ac","reqSigs":1,"type":"pubkeyhash","addresses":["12pv1K6LTLPFYXcCwsaU7VWYRSX7BuiF28"]}},{"value":"0.00002500","n":1,"scriptPubKey":{"asm":"1 0276d539826e5ec10fed9ef597d5bfdac067d287fb7f06799c449971b9ddf9fec6 02af7efeb1f7cf0d5077ae7f7a59e2b643c5cd01fb55221bf76221d8c8ead92bf0 02f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6 3 OP_CHECKMULTISIG","hex":"51210276d539826e5ec10fed9ef597d5bfdac067d287fb7f06799c449971b9ddf9fec62102af7efeb1f7cf0d5077ae7f7a59e2b643c5cd01fb55221bf76221d8c8ead92bf02102f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e653ae","reqSigs":1,"type":"multisig","addresses":["17MPn1QXt1SLqKWy3NPmJQ7iT5dJKRhCU7","12oEzNKh5TQpKDP1vfeTGnSjoxkboo1m5u","1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1"]}},{"value":"0.00086340","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 6ca4b6b20eac497e9ca94489c545a3372bdd2fa7 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9146ca4b6b20eac497e9ca94489c545a3372bdd2fa788ac","reqSigs":1,"type":"pubkeyhash","addresses":["1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1"]}}],"valueOut":0.0009427,"size":340,"valueIn":0.0009527,"fees":0.00001}', true);
    }

    protected function getSampleCounterpartyTransaction3() {
        // 2 SOUP
        return json_decode('{"txid":"24c9f9dd130591959b8f2e2e6d0133de028138bf73dfe14cc3de0fbb151812bd","version":1,"locktime":0,"vin":[{"txid":"e7f9319de85661c33130e80b7df16733164892eeb2e1fb044e5ac5de7a3269b7","vout":0,"scriptSig":{"asm":"304402205267657bb7ea5542b8fd32502e78dc3903e5781de8b8a5a119abbd382691a98e02204c47b7aa5940c030def956a4e91be16a692c09c7194a6fdec3054457c99e70c701 0257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690","hex":"47304402205267657bb7ea5542b8fd32502e78dc3903e5781de8b8a5a119abbd382691a98e02204c47b7aa5940c030def956a4e91be16a692c09c7194a6fdec3054457c99e70c701210257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690"},"sequence":4294967295,"n":0,"addr":"1291Z6hofAAvH8E886cN9M5uKB1VvwBnup","valueSat":5430,"value":0.0000543,"doubleSpentTxID":null},{"txid":"d800ef4c33542c90bcfe4cd0c2fc2c0d120877ec933ca869177a77eb8b42077e","vout":1,"scriptSig":{"asm":"3045022100e43310957b541c51e00c3076393bf93c23dd72306a012f0ebe1024218269045d022029b5218047adf59186d53dbff7a249f82003e923a782cdfd75f8871b89c819b801 0257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690","hex":"483045022100e43310957b541c51e00c3076393bf93c23dd72306a012f0ebe1024218269045d022029b5218047adf59186d53dbff7a249f82003e923a782cdfd75f8871b89c819b801210257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690"},"sequence":4294967295,"n":1,"addr":"1291Z6hofAAvH8E886cN9M5uKB1VvwBnup","valueSat":801320,"value":0.0080132,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 9c2401388e6d2752a496261e9130cd54ddb2b262 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9149c2401388e6d2752a496261e9130cd54ddb2b26288ac","reqSigs":1,"type":"pubkeyhash","addresses":["1FEbYaghvr7V53B9csjQTefUtBBQTaDFvN"]}},{"value":"0.00002500","n":1,"scriptPubKey":{"asm":"1 035240874259b8f93dffc6ffa29b09d0d06dfb072d9646ae777480a2c521bfdbb1 0264f1dd503423e8305e19fc77b4e26dc8ec8a8f500b1bec580112af8c64e74b1b 0257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690 3 OP_CHECKMULTISIG","hex":"5121035240874259b8f93dffc6ffa29b09d0d06dfb072d9646ae777480a2c521bfdbb1210264f1dd503423e8305e19fc77b4e26dc8ec8a8f500b1bec580112af8c64e74b1b210257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c69053ae","reqSigs":1,"type":"multisig","addresses":["1J7TTrHcWQgs37Es8PFKcaoJmUvGRGsEzY","126HYzAnxybKoHpkmrMBSSu3KnqzUgEHGc","1291Z6hofAAvH8E886cN9M5uKB1VvwBnup"]}},{"value":"0.00793820","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 0c7bea5ae61ccbc157156ffc9466a54b07bfe951 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9140c7bea5ae61ccbc157156ffc9466a54b07bfe95188ac","reqSigs":1,"type":"pubkeyhash","addresses":["1291Z6hofAAvH8E886cN9M5uKB1VvwBnup"]}}],"valueOut":0.0080175,"size":487,"valueIn":0.0080675,"fees":0.00005}', true);
    }

    protected function getSampleCounterpartyTransaction4() {
        // OP_RETURN 1 SOUP
        return json_decode('{"txid":"2d1fea72ea4be25793d8a2e254c6dc4aacfac8978ce0cc4729fe1418649c8f1e","version":1,"locktime":0,"vin":[{"txid":"a6edb86171fc7736389f20bc63542255dbbfa2dcd173fc079414adab7f548071","vout":0,"scriptSig":{"asm":"3044022038397429f0a49e80690eb143e63d83c097288b2c1ea90167688997e8a75a3c8402204e48260b4440001d402b40a33183dc5d54d5ced64670b0e5b72373392c8481d501 034bf23d14fa8aa9e8ebfee6e0bf986fa36ad61434068b89b7a8801506ed5060bb","hex":"473044022038397429f0a49e80690eb143e63d83c097288b2c1ea90167688997e8a75a3c8402204e48260b4440001d402b40a33183dc5d54d5ced64670b0e5b72373392c8481d50121034bf23d14fa8aa9e8ebfee6e0bf986fa36ad61434068b89b7a8801506ed5060bb"},"sequence":4294967295,"n":0,"addr":"1MFHQCPGtcSfNPXAS6NryWja3TbUN9239Y","valueSat":5430,"value":0.0000543,"doubleSpentTxID":null},{"txid":"31aa31fb22cd24d54b68ef7390ed39db09f2e4cbae1c227b2f78087f3edf4077","vout":1,"scriptSig":{"asm":"30440220189f79d7c86884d69f35cb7d8b96916bc3001c522a2025471675a6835fc45b7d02203345a31b0abb79035722a9ed5381fd34380f4e9cc418a5ea522034e50ea1f2e901 034bf23d14fa8aa9e8ebfee6e0bf986fa36ad61434068b89b7a8801506ed5060bb","hex":"4730440220189f79d7c86884d69f35cb7d8b96916bc3001c522a2025471675a6835fc45b7d02203345a31b0abb79035722a9ed5381fd34380f4e9cc418a5ea522034e50ea1f2e90121034bf23d14fa8aa9e8ebfee6e0bf986fa36ad61434068b89b7a8801506ed5060bb"},"sequence":4294967295,"n":1,"addr":"1MFHQCPGtcSfNPXAS6NryWja3TbUN9239Y","valueSat":1717640,"value":0.0171764,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 fd84ff1f2cc1b0299165e2804fccd6fb0bd48d0b OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914fd84ff1f2cc1b0299165e2804fccd6fb0bd48d0b88ac","reqSigs":1,"type":"pubkeyhash","addresses":["1Q7VHJDEzVj7YZBVseQWgYvVj3DWDCLwDE"]}},{"value":"0.00000000","n":1,"scriptPubKey":{"asm":"OP_RETURN 2c54fff6d3e165e008f5ec45a06951e6b6fb4a162fcfec34aa1f0dd8","hex":"6a1c2c54fff6d3e165e008f5ec45a06951e6b6fb4a162fcfec34aa1f0dd8","type":"nulldata"}},{"value":"0.01712640","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 de1607744008a3edeabae06365a9aa2b131d5dc2 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914de1607744008a3edeabae06365a9aa2b131d5dc288ac","reqSigs":1,"type":"pubkeyhash","addresses":["1MFHQCPGtcSfNPXAS6NryWja3TbUN9239Y"]}}],"blockhash":"00000000000000000d7fbe3a59b9d37380fb46a79289e2011396721c433a7883","confirmations":1,"time":1428589045,"blocktime":1428589045,"valueOut":0.0171807,"size":411,"valueIn":0.0172307,"fees":0.00005}', true);
    }
    protected function getSampleCounterpartyTransaction5() {
        // OP_RETURN 50 SOUP
        return json_decode('{"txid":"48e07bdad04f869850ccdb11b5e6a9e89ee8023111eb4f2e293d3e8ef75befee","version":1,"locktime":0,"vin":[{"txid":"cf8fa29cf63938a787e732c85071695668b9e00bfedff807dfbdc85a6ad6a696","vout":0,"scriptSig":{"asm":"304502210089af043911012c558bd11392d4346da725cd35822c1c6c0140293fed3344176d02207e6ad3945cb6580e654ac579a3ffdc01b93e54c4babc55bde6aa2b97ddf3f0af01 02b0610c458d67d76e348c6bb2f29f66d272e2c66bd23747ac401fefa45d2fc7a0","hex":"48304502210089af043911012c558bd11392d4346da725cd35822c1c6c0140293fed3344176d02207e6ad3945cb6580e654ac579a3ffdc01b93e54c4babc55bde6aa2b97ddf3f0af012102b0610c458d67d76e348c6bb2f29f66d272e2c66bd23747ac401fefa45d2fc7a0"},"sequence":4294967295,"n":0,"addr":"12iVwKP7jCPnuYy7jbAbyXnZ3FxvgLwvGK","valueSat":5430,"value":0.0000543,"doubleSpentTxID":null},{"txid":"d6e1e95bae2a4715dce049f464525af1f118876b7e79c311e1fa4c2bf77f4df4","vout":2,"scriptSig":{"asm":"30440220459ef45b40a80a9dc1cb2c33d1f5ddd8d464c4a607dbe2df83049d69b35c7b960220134b69e807eb2bebc3339bfb60a7de5b86c801f64d176fc704a3ce9132e8509301 02b0610c458d67d76e348c6bb2f29f66d272e2c66bd23747ac401fefa45d2fc7a0","hex":"4730440220459ef45b40a80a9dc1cb2c33d1f5ddd8d464c4a607dbe2df83049d69b35c7b960220134b69e807eb2bebc3339bfb60a7de5b86c801f64d176fc704a3ce9132e85093012102b0610c458d67d76e348c6bb2f29f66d272e2c66bd23747ac401fefa45d2fc7a0"},"sequence":4294967295,"n":1,"addr":"12iVwKP7jCPnuYy7jbAbyXnZ3FxvgLwvGK","valueSat":1760000,"value":0.0176,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 cab7d87116e620e10a69e666ec6494d4607631e7 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914cab7d87116e620e10a69e666ec6494d4607631e788ac","reqSigs":1,"type":"pubkeyhash","addresses":["1KUsjZKrkd7LYRV7pbnNJtofsq1HAiz6MF"]}},{"value":"0.00000000","n":1,"scriptPubKey":{"asm":"OP_RETURN fb706d6b2839cfd52a4b50cb135f18cf9e0b280ca45b2da5694229f0","hex":"6a1cfb706d6b2839cfd52a4b50cb135f18cf9e0b280ca45b2da5694229f0","type":"nulldata"}},{"value":"0.01755000","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 12d155cefea286e9d3cda6cb64cd8d26a5b95780 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a91412d155cefea286e9d3cda6cb64cd8d26a5b9578088ac","reqSigs":1,"type":"pubkeyhash","addresses":["12iVwKP7jCPnuYy7jbAbyXnZ3FxvgLwvGK"]}}],"valueOut":0.0176043,"size":412,"valueIn":0.0176543,"fees":0.00005}', true);
    }
    protected function getSampleCounterpartyTransaction6() {
        // OP_RETURN 0.5 XCP
        return json_decode('{"txid":"ed17315c28db9a61f40c2a78021de703c2ad9386b2945d642ffd732c8832f2e9","version":1,"locktime":0,"vin":[{"txid":"a71624023b36f58ef1c1881329261e885a77dc038a5312763a6aa8f4397f89ed","vout":2,"scriptSig":{"asm":"3045022100be988396fc5536198d3bf662620d82ad95cb3c8e05e393ebbd8fecb83f9a7aeb0220332a166767239122c11fe6df5df34421025d42b631f4f1a1f36ede2f3086ff8801 02f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6","hex":"483045022100be988396fc5536198d3bf662620d82ad95cb3c8e05e393ebbd8fecb83f9a7aeb0220332a166767239122c11fe6df5df34421025d42b631f4f1a1f36ede2f3086ff88012102f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6"},"sequence":4294967295,"n":0,"addr":"1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1","valueSat":66163,"value":0.00066163,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 60550079658a3deed658979dd56bb3b367a1ee80 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a91460550079658a3deed658979dd56bb3b367a1ee8088ac","reqSigs":1,"type":"pubkeyhash","addresses":["19nMhSywqDVkPaSjczSp9dm1SEUBukEsJP"]}},{"value":"0.00000000","n":1,"scriptPubKey":{"asm":"OP_RETURN 822682f94c0e981f9f18b4710c9ba306deef4e1d055693fd8b38d8d8","hex":"6a1c822682f94c0e981f9f18b4710c9ba306deef4e1d055693fd8b38d8d8","type":"nulldata"}},{"value":"0.00050733","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 6ca4b6b20eac497e9ca94489c545a3372bdd2fa7 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9146ca4b6b20eac497e9ca94489c545a3372bdd2fa788ac","reqSigs":1,"type":"pubkeyhash","addresses":["1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1"]}}],"blockhash":"000000000000000010c545b6fa3ef1f7cf45a2a8760b1ee9f2e89673218207ce","confirmations":48,"time":1435766223,"blocktime":1435766223,"valueOut":0.00056163,"size":265,"valueIn":0.00066163,"fees":0.0001}', true);
    }

    protected function getSampleCounterpartyTransaction7() {
        // OP_RETURN 77,119 TATIANACOIN
        return json_decode('{"txid":"32e34ca5001a0c46b88e77b48bd1bf8ca5769d06e6eaea85c2d319a3eb145010","version":1,"locktime":0,"vin":[{"txid":"e5a737c60e6fdc31ceccc2de4c9770381d26d909c8f89616b99d1c6eafbff4fb","vout":2,"scriptSig":{"asm":"3045022100c73bbc46e2389ea0fc68b0ecc34e4be66f5b8193e73d9a3c92e86c5e34d4d6430220369ecb76462ac835156ad5fd3981304f403193db27b7e45bb03163eb61a4f17a01 031d44961fbbba74285107c3503f15f7253361ebff7dad9f5b9a347ffe6cb0e264","hex":"483045022100c73bbc46e2389ea0fc68b0ecc34e4be66f5b8193e73d9a3c92e86c5e34d4d6430220369ecb76462ac835156ad5fd3981304f403193db27b7e45bb03163eb61a4f17a0121031d44961fbbba74285107c3503f15f7253361ebff7dad9f5b9a347ffe6cb0e264"},"sequence":4294967295,"n":0,"addr":"1LHpvZP8QV3CBuDryYdgUs56sd6UYvXuGS","valueSat":830270,"value":0.0083027,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 e1ce35ec36aa1d37a8b2a6463f0becc5484945f2 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914e1ce35ec36aa1d37a8b2a6463f0becc5484945f288ac","reqSigs":1,"type":"pubkeyhash","addresses":["1Max4vpkTbFgMjDpfaEjfPcdwd3ktTBQ4o"]},"spentTxId":"21924128f24a29ea08fedda7e62197c928157a55afe8a085ba4a143f55bb5f5d","spentIndex":0,"spentTs":1433027395},{"value":"0.00000000","n":1,"scriptPubKey":{"asm":"OP_RETURN 714417279299d6efe1868aa1cd197ff0094b62fb7679217fe184ae69","hex":"6a1c714417279299d6efe1868aa1cd197ff0094b62fb7679217fe184ae69","type":"nulldata"}},{"value":"0.00814840","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 d3990cbb7f291d61e51fc17180553256efcdc590 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914d3990cbb7f291d61e51fc17180553256efcdc59088ac","reqSigs":1,"type":"pubkeyhash","addresses":["1LHpvZP8QV3CBuDryYdgUs56sd6UYvXuGS"]},"spentTxId":"662309ca52e84d0bd7561e9a4fde517afb97dc8b8d5a94c322e795e20660dfd0","spentIndex":0,"spentTs":1433013021}],"blockhash":"00000000000000001416c0cfd9f2dca68d57877a82211143df4938a721b9ee3e","confirmations":4717,"time":1433011985,"blocktime":1433011985,"valueOut":0.0082027,"size":265,"valueIn":0.0083027,"fees":0.0001}', true);
    }

    protected function getSampleCounterpartyTransaction8() {
        // OP_RETURN 20 CLEFCARD
        return json_decode('{"txid":"da4b633c9dea0c4d00d59267571214d8c03e07246e15ac0784c0766e2d0dabdd","version":1,"locktime":0,"vin":[{"txid":"8af1981da0590886b09582abdcd7543b7fe0f965c80d4463bd50d641338d8b8b","vout":2,"scriptSig":{"asm":"3045022100e468aabe11daa6287d6b1a092dae64cf74b4c329dfab4517b68d9120df9eded30220481df791c04392235669011fc17b6838696b44f6699c553150c5afc54a0dcb8201 02b4339aa4b94bded436a53b3aed804365f567a4774cd5202b5576bfee8ae8f80a","hex":"483045022100e468aabe11daa6287d6b1a092dae64cf74b4c329dfab4517b68d9120df9eded30220481df791c04392235669011fc17b6838696b44f6699c553150c5afc54a0dcb82012102b4339aa4b94bded436a53b3aed804365f567a4774cd5202b5576bfee8ae8f80a"},"sequence":4294967295,"n":0,"addr":"17ZiuvTg4ye1BouogSHj8Uqn6ZnH28BipR","valueSat":43747430,"value":0.4374743,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 a82cdeaa91ec2053f931b759170c5c533f96d67e OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914a82cdeaa91ec2053f931b759170c5c533f96d67e88ac","reqSigs":1,"type":"pubkeyhash","addresses":["1GLEGqSaGieJFdLG4Ws94jHnZqkL5Favi5"]}},{"value":"0.00000000","n":1,"scriptPubKey":{"asm":"OP_RETURN 228ea5c4128a22c54bc82353d89f6ea52d3672688ff4ad325f53d293","hex":"6a1c228ea5c4128a22c54bc82353d89f6ea52d3672688ff4ad325f53d293","type":"nulldata"}},{"value":"0.43732000","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 48010d5eabf22609534c32d35b05ccdb424c341f OP_EQUALVERIFY OP_CHECKSIG","hex":"76a91448010d5eabf22609534c32d35b05ccdb424c341f88ac","reqSigs":1,"type":"pubkeyhash","addresses":["17ZiuvTg4ye1BouogSHj8Uqn6ZnH28BipR"]},"spentTxId":"6e355c94701e2c07df028426d311e7b1580a32972a39cdb47345fa222a54d022","spentIndex":0,"spentTs":1437526914}],"blockhash":"00000000000000000708da5be7f31ecabdc802a5f03eab36b8a5e4b0c556ce04","confirmations":3,"time":1437526914,"blocktime":1437526914,"valueOut":0.4373743,"size":265,"valueIn":0.4374743,"fees":0.0001}', true);
    }

    protected function getSampleCounterpartyTransaction9() {
        // OP_CHECKMULTISIG 677.87455108 LTBCOIN
        return json_decode('{"txid":"93b2a82ee7686eeedb966d7adb1dab4f63ba0932fa500f181fe3799954b7ba25","version":1,"locktime":0,"vin":[{"txid":"0b959e61bbcc189f1ff81ad6edb8fed511369b32696ed97a3b6ba278627fe42d","vout":2,"scriptSig":{"asm":"3044022004c9dd812cffccf8f03dd8184f6d857ed21cc55a8d6878eafd08bbbaac994aac02205fcf88e8db68ae77e610da50a7b4a23f0b31e1006eaea4d32274180bf085213701 0380a5c5fe2476e88ce737c389c4574bcd20319f9f070ad476e04a659ea576bf8a","hex":"473044022004c9dd812cffccf8f03dd8184f6d857ed21cc55a8d6878eafd08bbbaac994aac02205fcf88e8db68ae77e610da50a7b4a23f0b31e1006eaea4d32274180bf085213701210380a5c5fe2476e88ce737c389c4574bcd20319f9f070ad476e04a659ea576bf8a"},"sequence":4294967295,"n":0,"addr":"14vmJJYa8y1dLBEgnTcq6xM2P3e1BBw39c","valueSat":14509000,"value":0.14509,"doubleSpentTxID":null}],"vout":[{"value":"0.00003500","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 333e39ad580867430fb75fabd4d43bb7f8070e5f OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914333e39ad580867430fb75fabd4d43bb7f8070e5f88ac","reqSigs":1,"type":"pubkeyhash","addresses":["15fx1Gqe4KodZvyzN6VUSkEmhCssrM1yD7"]},"spentTxId":"7f04c6a76a8035f8281fd7a201ea4d4d89c69061c8024d390ca00fa36722ea3a","spentIndex":0,"spentTs":1442341604},{"value":"0.00003500","n":1,"scriptPubKey":{"asm":"1 028b428f3590133dbc78f6cb8cf9d9d6e916a26072cb21c96c0b07910c163126b0 0302573bdb852b5e86293e50294d8a0665f7550e78f35ff1c430b0022052d3a4af 0380a5c5fe2476e88ce737c389c4574bcd20319f9f070ad476e04a659ea576bf8a 3 OP_CHECKMULTISIG","hex":"5121028b428f3590133dbc78f6cb8cf9d9d6e916a26072cb21c96c0b07910c163126b0210302573bdb852b5e86293e50294d8a0665f7550e78f35ff1c430b0022052d3a4af210380a5c5fe2476e88ce737c389c4574bcd20319f9f070ad476e04a659ea576bf8a53ae","reqSigs":1,"type":"multisig","addresses":["1DCcWdH3HNcRo7chgfTLWoNxnYmJwbMi6x","1CrxgZWBGhKxzwqX3nGPcpJgaUDB8Qmadk","14vmJJYa8y1dLBEgnTcq6xM2P3e1BBw39c"]}},{"value":"0.14498000","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 2b1366d5d74574e5afc6f1ce72b52092ff807656 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9142b1366d5d74574e5afc6f1ce72b52092ff80765688ac","reqSigs":1,"type":"pubkeyhash","addresses":["14vmJJYa8y1dLBEgnTcq6xM2P3e1BBw39c"]},"spentTxId":"3d39dd47fdc356d035657d9aabdf2abcbdcd42cd656b43c0fd58d8e51abf1e29","spentIndex":0,"spentTs":1442172223}],"blockhash":"0000000000000000124077295f85b76ab4879f8a59003c144a5f2bdd2d2471cc","confirmations":464,"time":1442172223,"blocktime":1442172223,"valueOut":0.14505,"size":339,"valueIn":0.14509,"fees":0.00004}', true);
    }

    protected function getIssuanceTransaction1() {
        // OP_RETURN issue 1,409,527.13000000 LTBCOIN
        return json_decode('{"hex":"0100000001404746d7a3ca588e685c9c7837264a59f414193799fff758072765aea15be02b020000006b483045022100bdcc1274f03b7c11c1e36fde682cd2c43cf6677971fca5d6d8496a12ca68f65e0220230a184b058397d26685deb0603c710ee0dc70c47777b8440908685e22a97194012103429636ae01031ac5117414cea2da1b65dc50f7b86ea73c0d7c2b77a4ffacb3ddffffffff020000000000000000536a4c502d4df4adc288c989ed3c7063940ba4259cb15a558340cf2fed9be05a4899c8ec997b6d1d1377f4ee937fc3e458275dfae9bbe4ef50367a0a04b9b991d4ffc02213357e97a67c9b61b13d08223d08e3e29cfa0900000000001976a914b91d42f5ed440bfde8e67e0e009ce303ee24889188ac00000000","txid":"d9f3f3e592e1427a25cfe18c1e15d4fd5dc8db494e855b7f5cb650c638f74319","version":1,"locktime":0,"vin":[{"txid":"2be05ba1ae65270758f7ff99371914f4594a2637789c5c688e58caa3d7464740","vout":2,"scriptSig":{"asm":"3045022100bdcc1274f03b7c11c1e36fde682cd2c43cf6677971fca5d6d8496a12ca68f65e0220230a184b058397d26685deb0603c710ee0dc70c47777b8440908685e22a9719401 03429636ae01031ac5117414cea2da1b65dc50f7b86ea73c0d7c2b77a4ffacb3dd","hex":"483045022100bdcc1274f03b7c11c1e36fde682cd2c43cf6677971fca5d6d8496a12ca68f65e0220230a184b058397d26685deb0603c710ee0dc70c47777b8440908685e22a97194012103429636ae01031ac5117414cea2da1b65dc50f7b86ea73c0d7c2b77a4ffacb3dd"},"sequence":4294967295,"n":0,"addr":"1Hso4cqKAyx9bsan8b5nbPqMTNNce8ZDto","value":0.0066398,"valueSat":663980}],"vout":[{"value":0,"n":0,"scriptPubKey":{"asm":"OP_RETURN 2d4df4adc288c989ed3c7063940ba4259cb15a558340cf2fed9be05a4899c8ec997b6d1d1377f4ee937fc3e458275dfae9bbe4ef50367a0a04b9b991d4ffc02213357e97a67c9b61b13d08223d08e3e2","hex":"6a4c502d4df4adc288c989ed3c7063940ba4259cb15a558340cf2fed9be05a4899c8ec997b6d1d1377f4ee937fc3e458275dfae9bbe4ef50367a0a04b9b991d4ffc02213357e97a67c9b61b13d08223d08e3e2","type":"nulldata"}},{"value":0.0065398,"n":1,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 b91d42f5ed440bfde8e67e0e009ce303ee248891 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914b91d42f5ed440bfde8e67e0e009ce303ee24889188ac","reqSigs":1,"type":"pubkeyhash","addresses":["1Hso4cqKAyx9bsan8b5nbPqMTNNce8ZDto"]}}],"blockhash":"0000000000000000053ad78773db9405f92c0c979c90c65076fc7824b6c9426d","confirmations":353,"time":1467521389,"blocktime":1467521389,"valueIn":0.0066398,"valueInSat":663980,"valueOut":0.0065398,"valueOutSat":653980,"fees":0.0001,"feesSat":10000}', true);
    }

    protected function getIssuanceTransaction2() {
        // OP_RETURN issue 500 A229152867617021630
        return json_decode('{"hex":"01000000020a450c7b66dd6b33b724d9beb432b0f207fee04e760d93634190101706501c23000000006a473044022063d416f389258a0fc4f539665320a81e1ab5e7b7e834601af567d2cdda8f577d02201380a593facf82cf195b7251c17627c2e889977c79a3e9c03bf4d08fcfb377dd01210257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690ffffffffb2784603ba010e14d2385624772f016f9c3d16d3f1219eef7eb80d6207555745020000006a47304402201ad904d9d4924d8e6356a138bc35acebe35b13882dadb10ab23678b9cff987a702200cea7e8d54505dac43833a37b8bbb84f2ff8c1bddc9540b114b4eb1bcbfa59f801210257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690ffffffff020000000000000000456a43b0839d003c474bc0b6aa91154fc2100f8b34a1eb6e66df2d3e2cef15c21f590ed42256f1c5222b48515301740e4d87ee80966acf5848ef43f2415df7d90facbc668ad98eda1a00000000001976a9140c7bea5ae61ccbc157156ffc9466a54b07bfe95188ac00000000","txid":"2777ea0f1cb7cc9c9c7c0cd1cab7547e3f862342c67936fecb07b9766b963ae4","size":416,"version":1,"locktime":0,"vin":[{"txid":"231c50061710904163930d764ee0fe07f2b032b4bed924b7336bdd667b0c450a","vout":0,"scriptSig":{"asm":"3044022063d416f389258a0fc4f539665320a81e1ab5e7b7e834601af567d2cdda8f577d02201380a593facf82cf195b7251c17627c2e889977c79a3e9c03bf4d08fcfb377dd[ALL] 0257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690","hex":"473044022063d416f389258a0fc4f539665320a81e1ab5e7b7e834601af567d2cdda8f577d02201380a593facf82cf195b7251c17627c2e889977c79a3e9c03bf4d08fcfb377dd01210257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690"},"sequence":4294967295,"n":0,"addr":"1291Z6hofAAvH8E886cN9M5uKB1VvwBnup","value":5.43e-5,"valueSat":5430},{"txid":"45575507620db87eef9e21f1d3163d9c6f012f77245638d2140e01ba034678b2","vout":2,"scriptSig":{"asm":"304402201ad904d9d4924d8e6356a138bc35acebe35b13882dadb10ab23678b9cff987a702200cea7e8d54505dac43833a37b8bbb84f2ff8c1bddc9540b114b4eb1bcbfa59f8[ALL] 0257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690","hex":"47304402201ad904d9d4924d8e6356a138bc35acebe35b13882dadb10ab23678b9cff987a702200cea7e8d54505dac43833a37b8bbb84f2ff8c1bddc9540b114b4eb1bcbfa59f801210257b0d96d1fe64fbb95b2087e68592ee016c50f102d8dcf776ed166473f27c690"},"sequence":4294967295,"n":1,"addr":"1291Z6hofAAvH8E886cN9M5uKB1VvwBnup","value":0.0177476,"valueSat":1774760}],"vout":[{"value":0,"n":0,"scriptPubKey":{"asm":"OP_RETURN b0839d003c474bc0b6aa91154fc2100f8b34a1eb6e66df2d3e2cef15c21f590ed42256f1c5222b48515301740e4d87ee80966acf5848ef43f2415df7d90facbc668ad9","hex":"6a43b0839d003c474bc0b6aa91154fc2100f8b34a1eb6e66df2d3e2cef15c21f590ed42256f1c5222b48515301740e4d87ee80966acf5848ef43f2415df7d90facbc668ad9","type":"nulldata"}},{"value":0.01759886,"n":1,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 0c7bea5ae61ccbc157156ffc9466a54b07bfe951 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9140c7bea5ae61ccbc157156ffc9466a54b07bfe95188ac","reqSigs":1,"type":"pubkeyhash","addresses":["1291Z6hofAAvH8E886cN9M5uKB1VvwBnup"]}}],"blockhash":"0000000000000000016d77b2883586c30efb2fa13a9efcb03936607e6f00337c","confirmations":1,"time":1474030077,"blocktime":1474030077,"valueIn":0.0178019,"valueInSat":1780190,"valueOut":0.01759886,"valueOutSat":1759886,"fees":0.00020304,"feesSat":20304}', true);
    }

    protected function getOrder1() {
        // Order (Sell KIZUNA for XCP)
        return json_decode('{"hex":"0100000001423b3e930d1700c09bd24410f4428c6aea7a9232e49f6d2725979c713d2949ed010000006b483045022100c24444e3fe4688aba1cc6d83a0e84dd867d4c1049049cfc0d062bca82cff5322022072b497e8cf57aac44ff47b713fb5d11817b4a10f071b8c11b416444d922ce868012103350141a03d955481cfe1bcc2f801b50d10980e36916e492b5b7fd0568ebf6881ffffffff020000000000000000386a36ef16f2ed482a02c8ec8b2edd9298dedb4242eb5d0ce0bee3b2e4af453bdc948d8024b2af91a008510f27520fc1422318c1fecb8756baf21d1000000000001976a9149e9754ff905f9d46c9462f3e4df87e52683786d188ac00000000","txid":"e00d78050115fb08990e2f216783b6d8a12479d8bc9054a7b0a9d895151d0867","version":1,"locktime":0,"vin":[{"txid":"ed49293d719c9725276d9fe432927aea6a8c42f41044d29bc000170d933e3b42","vout":1,"scriptSig":{"asm":"3045022100c24444e3fe4688aba1cc6d83a0e84dd867d4c1049049cfc0d062bca82cff5322022072b497e8cf57aac44ff47b713fb5d11817b4a10f071b8c11b416444d922ce86801 03350141a03d955481cfe1bcc2f801b50d10980e36916e492b5b7fd0568ebf6881","hex":"483045022100c24444e3fe4688aba1cc6d83a0e84dd867d4c1049049cfc0d062bca82cff5322022072b497e8cf57aac44ff47b713fb5d11817b4a10f071b8c11b416444d922ce868012103350141a03d955481cfe1bcc2f801b50d10980e36916e492b5b7fd0568ebf6881"},"sequence":4294967295}],"vout":[{"value":0,"n":0,"scriptPubKey":{"asm":"OP_RETURN ef16f2ed482a02c8ec8b2edd9298dedb4242eb5d0ce0bee3b2e4af453bdc948d8024b2af91a008510f27520fc1422318c1fecb8756ba","hex":"6a36ef16f2ed482a02c8ec8b2edd9298dedb4242eb5d0ce0bee3b2e4af453bdc948d8024b2af91a008510f27520fc1422318c1fecb8756ba","type":"nulldata"}},{"value":0.01056242,"n":1,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 9e9754ff905f9d46c9462f3e4df87e52683786d1 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9149e9754ff905f9d46c9462f3e4df87e52683786d188ac","reqSigs":1,"type":"pubkeyhash","addresses":["1FTZ43ULuQprLiHPtW7bR9o2Sz8HuycqZ3"]}}],"blockhash":"000000000000000001fe0130099fd4ed10579639af202dd00d7fbd9a6c4e8f3e","confirmations":1048,"time":1467788392,"blocktime":1467788392}', true);
    }

    protected function getBroadcast1() {
        // OP_RETURN "BLOCKSCAN VERIFY-ADDRESS 7a4exlyjw97esst"
        return json_decode('{"hex":"0100000001b78a8302b19b96946eadd07639eb7cba5912b0289e06651b43397eafaa5c2263000000006a47304402202bcb838d5cde360a441a3a2078c850da7d5beab07b59d0fe9c65a76cc2773ee90220243dd885f5da5d498e42f9ab9168f4ab4ee7059f2570ff98df116a13c55978fc012102bf6dd08324d2ba2dde233bd2e0679e2b9566569832a5df95e66067205cc37cfdffffffff020000000000000000476a453278869e7fd274e341da1146748d71feec55a24cac50559905938bd422261e6141e6511a4dfc44ed7def29b4ca07116be1a0bde2c0222ccb08e246ceba787d06d44f58d58d8d610100000000001976a914fa6bfacfa3fc9a6a1998ca6a88d26d168aff71f888ac00000000","txid":"40c592beaf966697c2d052321dec817d41dd7257ec8a4ae667cb5f0af56a0496","size":271,"version":1,"locktime":0,"vin":[{"txid":"63225caaaf7e39431b65069e28b01259ba7ceb3976d0ad6e94969bb102838ab7","vout":0,"scriptSig":{"asm":"304402202bcb838d5cde360a441a3a2078c850da7d5beab07b59d0fe9c65a76cc2773ee90220243dd885f5da5d498e42f9ab9168f4ab4ee7059f2570ff98df116a13c55978fc[ALL] 02bf6dd08324d2ba2dde233bd2e0679e2b9566569832a5df95e66067205cc37cfd","hex":"47304402202bcb838d5cde360a441a3a2078c850da7d5beab07b59d0fe9c65a76cc2773ee90220243dd885f5da5d498e42f9ab9168f4ab4ee7059f2570ff98df116a13c55978fc012102bf6dd08324d2ba2dde233bd2e0679e2b9566569832a5df95e66067205cc37cfd"},"sequence":4294967295,"n":0,"addr":"1Pq7HXD5i9ZXXfnt71xPmtEkyATWc57bRw","value":0.00104,"valueSat":104000}],"vout":[{"value":0,"n":0,"scriptPubKey":{"asm":"OP_RETURN 3278869e7fd274e341da1146748d71feec55a24cac50559905938bd422261e6141e6511a4dfc44ed7def29b4ca07116be1a0bde2c0222ccb08e246ceba787d06d44f58d58d","hex":"6a453278869e7fd274e341da1146748d71feec55a24cac50559905938bd422261e6141e6511a4dfc44ed7def29b4ca07116be1a0bde2c0222ccb08e246ceba787d06d44f58d58d","type":"nulldata"}},{"value":0.00090509,"n":1,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 fa6bfacfa3fc9a6a1998ca6a88d26d168aff71f8 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914fa6bfacfa3fc9a6a1998ca6a88d26d168aff71f888ac","reqSigs":1,"type":"pubkeyhash","addresses":["1Pq7HXD5i9ZXXfnt71xPmtEkyATWc57bRw"]}}],"blockhash":"000000000000000003a7396578ea73978eb3ec326aa10b072a32202fab1f7fe7","confirmations":179,"time":1473158671,"blocktime":1473158671,"valueIn":0.00104,"valueInSat":104000,"valueOut":0.00090509,"valueOutSat":90509,"fees":0.00013491,"feesSat":13491}', true);
    }

    protected function getBroadcast2() {
        // OP_CHECKMULTISIG "http://blockscan.com/feedinfo?q=11957471"
        return json_decode('{"hex":"0100000006a3c8465b25d1ce657fda49ed06c0c427e336567610c0cf490803b20f51d07a5c000000006b483045022100df911aa1a4f8f5a074cfc647167f27887846aac686f6848437e1f87e5836939b022001dc8c7b040c5113cac1c275d6ea723a6317c721942c3595ec9f747d4b311476012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1ffffffffe9868b32f20a96492dbfa8f111f033b454e1e7f0a6664c785abf83438b811a96000000006b4830450221008fa476aa8cc20b2f0113cf8c24c32cc5427c5421dde52a537d66c4db5bfb39b402201f84db663b84a0aaa779fbcb1e8ecdd50d7783e105feeecca5b448adae7118de012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1ffffffff7044fdeee4a1758441ae965138a9f7fa6ce9b721fb589287d1e0819a4867eb9b000000006b4830450221008aaac76bd27ef73ae3cefeb4dd3586c89f98c6a13f3141e10c9a321d358500e002200e246ddaceb3b3ffaee4cb6a0434b6334a3c7b0a629974bac6f4d7bfc15411ff012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1ffffffffc319367ca4177d255268a2e611f905bc35ccc9de708e5c0b716221351c05cdee000000006a47304402206d010cc12be05e1a8e92c7bd579e80fbfcabd4d8d0ff4089ad26d18d5861d849022000bbecc6b173014bdabc9f7a12e49616eaa7a6356e4042d60a2a907080ab8fc9012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1ffffffff55f8956eddf1343923e5ca2fa86e95f7357d93cef7c9b5ee53d211e82a4d07f0000000006a47304402201a76d5a342d623853a849d02ac23155f1e52d0b12b74fd260eeff46f8338d99b022008bf1bc43467a7090c8dd31f68ba0d15615e88d287ed405e6723e7be69d46c18012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1ffffffffa3c8465b25d1ce657fda49ed06c0c427e336567610c0cf490803b20f51d07a5c020000006a47304402204427fbdde5fff08e7d89dd5f6a630ac0de502bbeb457f78691f18b384e6cf85f022012b6ae80b883842d5f50edfa79869ff7cee88dd098bd157f5c5e0f2d8fc1c936012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1ffffffff03781e00000000000069512103188cb5248eb49e479835639471799f0ebb7e99ab9c753bb73598d1f50021e2a32103c8600ca0c34c52636441cb97ebe360dfa1b108d85a7817e490476dcd440001042103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d153ae781e00000000000069512102058cb5248eb49e47985711fb0e4a4baee8b549d8e5064fd258b6f1b4425a87eb2103923a7f80aa3f72024435aee49fc30fb981c560bd7a1d7a81e22008a3277921c62103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d153ae349b0300000000001976a914a3ec60fb522fdf62c90eec1981577813d8f8a58a88ac00000000","txid":"2f9664e37b297468c95876b0d89c59633dcb3a65ce5de8630d003432ce7b11ac","size":1157,"version":1,"locktime":0,"vin":[{"txid":"5c7ad0510fb2030849cfc010765636e327c4c006ed49da7f65ced1255b46c8a3","vout":0,"scriptSig":{"asm":"3045022100df911aa1a4f8f5a074cfc647167f27887846aac686f6848437e1f87e5836939b022001dc8c7b040c5113cac1c275d6ea723a6317c721942c3595ec9f747d4b311476[ALL] 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1","hex":"483045022100df911aa1a4f8f5a074cfc647167f27887846aac686f6848437e1f87e5836939b022001dc8c7b040c5113cac1c275d6ea723a6317c721942c3595ec9f747d4b311476012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1"},"sequence":4294967295,"n":0,"addr":"1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu","value":5.43e-5,"valueSat":5430},{"txid":"961a818b4383bf5a784c66a6f0e7e154b433f011f1a8bf2d49960af2328b86e9","vout":0,"scriptSig":{"asm":"30450221008fa476aa8cc20b2f0113cf8c24c32cc5427c5421dde52a537d66c4db5bfb39b402201f84db663b84a0aaa779fbcb1e8ecdd50d7783e105feeecca5b448adae7118de[ALL] 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1","hex":"4830450221008fa476aa8cc20b2f0113cf8c24c32cc5427c5421dde52a537d66c4db5bfb39b402201f84db663b84a0aaa779fbcb1e8ecdd50d7783e105feeecca5b448adae7118de012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1"},"sequence":4294967295,"n":1,"addr":"1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu","value":5.43e-5,"valueSat":5430},{"txid":"9beb67489a81e0d1879258fb21b7e96cfaf7a9385196ae418475a1e4eefd4470","vout":0,"scriptSig":{"asm":"30450221008aaac76bd27ef73ae3cefeb4dd3586c89f98c6a13f3141e10c9a321d358500e002200e246ddaceb3b3ffaee4cb6a0434b6334a3c7b0a629974bac6f4d7bfc15411ff[ALL] 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1","hex":"4830450221008aaac76bd27ef73ae3cefeb4dd3586c89f98c6a13f3141e10c9a321d358500e002200e246ddaceb3b3ffaee4cb6a0434b6334a3c7b0a629974bac6f4d7bfc15411ff012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1"},"sequence":4294967295,"n":2,"addr":"1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu","value":5.43e-5,"valueSat":5430},{"txid":"eecd051c352162710b5c8e70dec9cc35bc05f911e6a26852257d17a47c3619c3","vout":0,"scriptSig":{"asm":"304402206d010cc12be05e1a8e92c7bd579e80fbfcabd4d8d0ff4089ad26d18d5861d849022000bbecc6b173014bdabc9f7a12e49616eaa7a6356e4042d60a2a907080ab8fc9[ALL] 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1","hex":"47304402206d010cc12be05e1a8e92c7bd579e80fbfcabd4d8d0ff4089ad26d18d5861d849022000bbecc6b173014bdabc9f7a12e49616eaa7a6356e4042d60a2a907080ab8fc9012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1"},"sequence":4294967295,"n":3,"addr":"1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu","value":5.43e-5,"valueSat":5430},{"txid":"f0074d2ae811d253eeb5c9f7ce937d35f7956ea82fcae5233934f1dd6e95f855","vout":0,"scriptSig":{"asm":"304402201a76d5a342d623853a849d02ac23155f1e52d0b12b74fd260eeff46f8338d99b022008bf1bc43467a7090c8dd31f68ba0d15615e88d287ed405e6723e7be69d46c18[ALL] 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1","hex":"47304402201a76d5a342d623853a849d02ac23155f1e52d0b12b74fd260eeff46f8338d99b022008bf1bc43467a7090c8dd31f68ba0d15615e88d287ed405e6723e7be69d46c18012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1"},"sequence":4294967295,"n":4,"addr":"1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu","value":5.43e-5,"valueSat":5430},{"txid":"5c7ad0510fb2030849cfc010765636e327c4c006ed49da7f65ced1255b46c8a3","vout":2,"scriptSig":{"asm":"304402204427fbdde5fff08e7d89dd5f6a630ac0de502bbeb457f78691f18b384e6cf85f022012b6ae80b883842d5f50edfa79869ff7cee88dd098bd157f5c5e0f2d8fc1c936[ALL] 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1","hex":"47304402204427fbdde5fff08e7d89dd5f6a630ac0de502bbeb457f78691f18b384e6cf85f022012b6ae80b883842d5f50edfa79869ff7cee88dd098bd157f5c5e0f2d8fc1c936012103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1"},"sequence":4294967295,"n":5,"addr":"1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu","value":0.00303715,"valueSat":303715}],"vout":[{"value":7.8e-5,"n":0,"scriptPubKey":{"asm":"1 03188cb5248eb49e479835639471799f0ebb7e99ab9c753bb73598d1f50021e2a3 03c8600ca0c34c52636441cb97ebe360dfa1b108d85a7817e490476dcd44000104 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1 3 OP_CHECKMULTISIG","hex":"512103188cb5248eb49e479835639471799f0ebb7e99ab9c753bb73598d1f50021e2a32103c8600ca0c34c52636441cb97ebe360dfa1b108d85a7817e490476dcd440001042103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d153ae","reqSigs":1,"type":"multisig","addresses":["16crWdVomZQEC3Rb6VemMG3MXRfV9Q3TEA","14EV4uK5TCRowUVjC4Pr5wfyXeZdm8Frkx","1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu"]}},{"value":7.8e-5,"n":1,"scriptPubKey":{"asm":"1 02058cb5248eb49e47985711fb0e4a4baee8b549d8e5064fd258b6f1b4425a87eb 03923a7f80aa3f72024435aee49fc30fb981c560bd7a1d7a81e22008a3277921c6 03f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d1 3 OP_CHECKMULTISIG","hex":"512102058cb5248eb49e47985711fb0e4a4baee8b549d8e5064fd258b6f1b4425a87eb2103923a7f80aa3f72024435aee49fc30fb981c560bd7a1d7a81e22008a3277921c62103f33ea0d35ac04f9bcfb4b161bfe526ab4d1c67adcfcf45f6f19615d54fd0a5d153ae","reqSigs":1,"type":"multisig","addresses":["1BMGNVZKeMiVR732e2XJujy7UyKiEqZhDM","16F3PXUgLRuTJDwYvCqmV2FTuCFRvWDWFX","1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu"]}},{"value":0.0023634,"n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 a3ec60fb522fdf62c90eec1981577813d8f8a58a OP_EQUALVERIFY OP_CHECKSIG","hex":"76a914a3ec60fb522fdf62c90eec1981577813d8f8a58a88ac","reqSigs":1,"type":"pubkeyhash","addresses":["1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu"]}}],"blockhash":"000000000000000003726a6dcb29b0e79ed9e632bcef165a0d5230c3126edcc2","confirmations":2734,"time":1471660740,"blocktime":1471660740,"valueIn":0.00330865,"valueInSat":330865,"valueOut":0.0025194,"valueOutSat":251940,"fees":0.00078925,"feesSat":78925}', true);
    }



}
