<?php

use Tokenly\CounterpartyTransactionParser\Parser;
use \PHPUnit_Framework_Assert as PHPUnit;
use \Exception;

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


    protected function getSampleCounterpartyTransaction() {
        // 533.83451959 LTBCOIN
        return json_decode('{"txid":"1886737bb2a4be1af89b1d0e5af427ef2a7fc439e2ed10a42d3efeb1f71b69aa","version":1,"locktime":0,"vin":[{"txid":"26bc3e4933c68d503d0c24bc039a64ace18b2899dc54f799a80f20fc047d7688","vout":2,"scriptSig":{"asm":"3045022100b1514287d58b56c8bb2df00349cdebd1c7fded0d7fe92320743dd631836ef62002200f56062249ce5a64b81d0921ac65151633c1c54dea3a93bc51844863d201d87301 0370a00e36f0ca37c2d80631e0209ede134dbd927d50a364c6747f9c5f3c2c7a9c"},"sequence":4294967295,"n":0,"addr":"1F9UWGP1YwZsfXKogPFST44CT3WYh4GRCz","valueSat":9114000,"value":0.09114,"doubleSpentTxID":null}],"vout":[{"value":"0.00001250","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 c56cb39f9b289c0ec4ef6943fa107c904820fe09 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD"]}},{"value":"0.00001250","n":1,"scriptPubKey":{"asm":"1 0370a00e36f0ca37c2d80631e0209ede134dbd927d50a364c6747f9c5f3c2c7a9c 1c434e5452505254590000000000000000d806c1d50000000c6de6d53700000000 2 OP_CHECKMULTISIG","reqSigs":1,"type":"multisig","addresses":["1F9UWGP1YwZsfXKogPFST44CT3WYh4GRCz","1HT7xU2Ngenf7D4yocz2SAcnNLW7rK8d4E"]}},{"value":"0.09108000","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 9b2c0c5f30a2dde09c2a9d3618ba390c9a688754 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1F9UWGP1YwZsfXKogPFST44CT3WYh4GRCz"]},"spentTxId":"fbd318eb157becef3e26460756eaee2f4910d8995d23414ce1938563ecc61784","spentIndex":0,"spentTs":1416091287}],"blockhash":"00000000000000000347e702fdc4d6ed74dca01844857deb5fec560c25b14d51","confirmations":25,"time":1416091287,"blocktime":1416091287,"valueOut":0.091105,"size":306,"valueIn":0.09114,"fees":0.000035}', true);
    }

    protected function getSampleCounterpartyTransaction2() {
        // 533.83451959 LTBCOIN
        return json_decode('{"txid":"c57679bfc68a62025df34303b914aa22bc7ebd840e148460a6be2fa4dc1cc9b5","version":1,"locktime":0,"vin":[{"txid":"1aedd05e9c610329f972654dd0b42df1f932db6915e15a6f2f799efca9ab84bf","vout":2,"scriptSig":{"asm":"3045022100de88671974012e75ae17672d06eaf797927233c7cae4c6f2e87cdacc5843bde50220017be96f8dae45678495cdba270672627f9e3a3b4b72fd68a33237a876f275d301 02dc751a9373996e6ba4b5050f99d07a7c289004adeac1ab7dff66975b69deecfc"},"sequence":4294967295,"n":0,"addr":"17XotRS4RaeG6EAshBrWE4KMfizaqfLk5T","valueSat":18872000,"value":0.18872,"doubleSpentTxID":null}],"vout":[{"value":"0.00007800","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 e2faa809bd83e00281fc381ae488590132db8cab OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1Mh9zGQPS1HRLU8ivZ52MaCcomrGGEyJQs"]}},{"value":"0.00007800","n":1,"scriptPubKey":{"asm":"1 02dc751a9373996e6ba4b5050f99d07a7c289004adeac1ab7dff66975b69deecfc 1c434e5452505254590000000000fa1f18a3ed95f0000000003b9aca0000000000 2 OP_CHECKMULTISIG","reqSigs":1,"type":"multisig","addresses":["17XotRS4RaeG6EAshBrWE4KMfizaqfLk5T","1HT7xU2Ngenf7D4yocz2SAcnNLW7rK8d4E"]}},{"value":"0.18846400","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 47a45f5bef95b261d5ba654c47f198b75077616b OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["17XotRS4RaeG6EAshBrWE4KMfizaqfLk5T"]},"spentTxId":"8031d1e1d12d38d3fc712ee47f7364c1b6979c8b2422955c2eccd9d22642105a","spentIndex":0,"spentTs":1416531752}],"blockhash":"00000000000000001acb1484131a0b69c7666e1a9b94e483babae59adf1fe160","confirmations":7,"time":1416531752,"blocktime":1416531752,"valueOut":0.18862,"size":306,"valueIn":0.18872,"fees":0.0001}', true);
    }

    protected function getSampleBitcoinTransaction() {
        return json_decode('{"txid":"cf9d9f4d53d36d9d34f656a6d40bc9dc739178e6ace01bcc42b4b9ea2cbf6741","version":1,"locktime":0,"vin":[{"txid":"cc669b824186886407ad7edd46796437e20ad73c89080420c45e5803f917228d","vout":2,"scriptSig":{"asm":"3045022100a37bcfd3087fa4ba9480ce09c7adf02ba3ce2208d6170b42e50b5b2633b91ee6022025d409d3d9dae0a159982c7ab079787948b6b6c5f87fa583d3886ebf1e074c8901 02f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6"},"sequence":4294967295,"n":0,"addr":"1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1","valueSat":781213,"value":0.00781213,"doubleSpentTxID":null}],"vout":[{"value":"0.00400000","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 c56cb39f9b289c0ec4ef6943fa107c904820fe09 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1JztLWos5K7LsqW5E78EASgiVBaCe6f7cD"]},"spentTxId":"e90bc279294d704d09b227ad0e37459f61cccb85008605656dc8b024235eefe8","spentIndex":2,"spentTs":1403958484},{"value":"0.00361213","n":1,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 6ca4b6b20eac497e9ca94489c545a3372bdd2fa7 OP_EQUALVERIFY OP_CHECKSIG","reqSigs":1,"type":"pubkeyhash","addresses":["1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1"]},"spentTxId":"3587bfa8d96c10b6696728651900db2ad6b41321ea44f26693de4f90d2b63526","spentIndex":0,"spentTs":1405081243}],"blockhash":"00000000000000003a1e5abc2d7af7f38a614d2fcbafe309b7e8aa147d508a9c","confirmations":22369,"time":1403957896,"blocktime":1403957896,"valueOut":0.00761213,"size":226,"valueIn":0.00781213,"fees":0.0002}', true);
    }

    protected function getSampleCounterpartyTransactionProtocol2() {
        return json_decode('{"txid":"e0082d1fc37172ccf0f5ebfc3cc54291e463384712f44f32ba4996c02045966f","version":1,"locktime":0,"vin":[{"txid":"8fd9f689f158a426867215dbdee58e9eab6c818097d4bf2bcf0bd1458f3c55ab","vout":2,"scriptSig":{"asm":"3045022100a178c9accd7972cfe30a03c98ff5f684bdf0b144eb415f4a4b7fcff596283f720220267f68a6413093b97a42ed5c2f2811193c1bbdd07d668e3076f99751044c347a01 02f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6","hex":"483045022100a178c9accd7972cfe30a03c98ff5f684bdf0b144eb415f4a4b7fcff596283f720220267f68a6413093b97a42ed5c2f2811193c1bbdd07d668e3076f99751044c347a012102f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6"},"sequence":4294967295,"n":0,"addr":"1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1","valueSat":95270,"value":0.0009527,"doubleSpentTxID":null}],"vout":[{"value":"0.00005430","n":0,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 1407ec32be440f32fc70f4eea810acd98f32aa32 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9141407ec32be440f32fc70f4eea810acd98f32aa3288ac","reqSigs":1,"type":"pubkeyhash","addresses":["12pv1K6LTLPFYXcCwsaU7VWYRSX7BuiF28"]}},{"value":"0.00002500","n":1,"scriptPubKey":{"asm":"1 0276d539826e5ec10fed9ef597d5bfdac067d287fb7f06799c449971b9ddf9fec6 02af7efeb1f7cf0d5077ae7f7a59e2b643c5cd01fb55221bf76221d8c8ead92bf0 02f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e6 3 OP_CHECKMULTISIG","hex":"51210276d539826e5ec10fed9ef597d5bfdac067d287fb7f06799c449971b9ddf9fec62102af7efeb1f7cf0d5077ae7f7a59e2b643c5cd01fb55221bf76221d8c8ead92bf02102f4aef682535628a7e0492b2b5db1aa312348c3095e0258e26b275b25b10290e653ae","reqSigs":1,"type":"multisig","addresses":["17MPn1QXt1SLqKWy3NPmJQ7iT5dJKRhCU7","12oEzNKh5TQpKDP1vfeTGnSjoxkboo1m5u","1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1"]}},{"value":"0.00086340","n":2,"scriptPubKey":{"asm":"OP_DUP OP_HASH160 6ca4b6b20eac497e9ca94489c545a3372bdd2fa7 OP_EQUALVERIFY OP_CHECKSIG","hex":"76a9146ca4b6b20eac497e9ca94489c545a3372bdd2fa788ac","reqSigs":1,"type":"pubkeyhash","addresses":["1AuTJDwH6xNqxRLEjPB7m86dgmerYVQ5G1"]}}],"valueOut":0.0009427,"size":340,"valueIn":0.0009527,"fees":0.00001}', true);
    }


}
