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

        $tx_data = $this->getSampleCounterpartyTransaction4();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);

        // 1 SOUP
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('1MFHQCPGtcSfNPXAS6NryWja3TbUN9239Y', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('1Q7VHJDEzVj7YZBVseQWgYvVj3DWDCLwDE', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals('SOUP', $counterparty_data['asset']);
        PHPUnit::assertEquals(1 * self::SATOSHI, $counterparty_data['quantity']);


        $tx_data = $this->getSampleCounterpartyTransaction5();
        $counterparty_data = $parser->parseBitcoinTransaction($tx_data, 2);
        PHPUnit::assertNotEmpty($counterparty_data);

        // 50 SOUP
        PHPUnit::assertEquals('send', $counterparty_data['type']);
        PHPUnit::assertEquals('12iVwKP7jCPnuYy7jbAbyXnZ3FxvgLwvGK', $counterparty_data['sources'][0]);
        PHPUnit::assertEquals('1KUsjZKrkd7LYRV7pbnNJtofsq1HAiz6MF', $counterparty_data['destinations'][0]);
        PHPUnit::assertEquals('SOUP', $counterparty_data['asset']);
        PHPUnit::assertEquals(51 * self::SATOSHI, $counterparty_data['quantity']);
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



}
