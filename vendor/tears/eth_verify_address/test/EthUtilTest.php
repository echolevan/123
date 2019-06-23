<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/9/27
 * Time: 下午1:27
 */

namespace eth_verify_address\test;

use eth_verify_address\EthUtil;
use PHPUnit\Framework\TestCase;

class EthUtilTest extends TestCase
{

    public function testIsAddress()
    {
        $isAddress = EthUtil::isAddress('');
        $this->assertEquals($isAddress, false);
        $isAddress = EthUtil::isAddress('0x78C62b998077C1E89d2A6c0df18e8c3BB6Bc3F72');
        $this->assertEquals($isAddress, true);
        $isAddress = EthUtil::isAddress('0x78C62b998077C1E89d2A6c0df18e8c3BB6Bc3F71');
        $this->assertEquals($isAddress, false);
        $isAddress = EthUtil::isAddress('0xe39a0f652d1d5815cd238d726230a4df51de2db1');
        $this->assertEquals($isAddress, true);
        $isAddress = EthUtil::isAddress('0xe39a0f652d1d5815cd238d726230a4df51de2db2');
        $this->assertEquals($isAddress, true);
        $isAddress = EthUtil::isAddress('0xe39a0f652d1d5815cd238d726230a4df51de2db');
        $this->assertEquals($isAddress, false);
        $isAddress = EthUtil::isAddress('0xe39a0f652d1d5815cd238d726230a4df51de2db23');
        $this->assertEquals($isAddress, false);
        $isAddress = EthUtil::isAddress('0xe39a0f652d1d5815cd238d726230a4df51de2dbQ');
        $this->assertEquals($isAddress, false);
        $isAddress = EthUtil::isAddress('0xe39a0f652d1d5815cd238d726230a4df51de2dbq');
        $this->assertEquals($isAddress, false);
        $isAddress = EthUtil::isAddress('aae39a0f652d1d5815cd238d726230a4df51de2db2');
        $this->assertEquals($isAddress, false);

    }
}
