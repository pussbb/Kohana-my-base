<?php

class ValidTest extends Unittest_TestCase
{
    /**
     * @covers Valid::ascii
     */
    function testASCII()
    {
        $this->assertTrue(Valid::ascii('sdsd'));
        $this->assertFalse(Valid::ascii('ывавыа'));
    }

    /**
     * @covers Valid::jssafe
     */
    function testJSsafe()
    {
        $this->assertTrue(Valid::jssafe('asdasdasdasd'));
        $this->assertFalse(Valid::jssafe('<script>dcdc=0;</script>sdasdas'));
    }

    /**
     * @covers Valid::macaddress
     */
    function testMacaddress()
    {
        $this->assertTrue(Valid::macaddress('01-23-45-67-89-ab'));
        $this->assertTrue(Valid::macaddress('01:23:45:67:89:ab'));
        $this->assertFalse(Valid::macaddress('dsdas'));
    }

    /**
     * @covers Valid::md5
     */
    function testMD5()
    {
        $this->assertTrue(Valid::md5('d41d8cd98f00b204e9800998ecf8427e'));
        $this->assertFalse(Valid::md5('dsdas'));
    }

    /**
     * @covers Valid::multiline
     */
    function testMultiline()
    {
        $this->assertTrue(Valid::multiline('sdasd\nsdasd\t'));
        $this->assertFalse(Valid::multiline('dsdas'));
    }

    /**
     * @covers Valid::pincode
     */
    function testPincode()
    {
        $this->assertTrue(Valid::pincode('8944', 'at'));
        $this->assertFalse(Valid::pincode('8944'));
    }

    /**
     * @covers Valid::time12
     */
    function testTime12()
    {
        $this->assertTrue(Valid::time12('08:00AM'));
        $this->assertTrue(Valid::time12('08:00PM'));
        $this->assertFalse(Valid::time12('08:00'));
    }

    /**
     * @covers Valid::time24
     */
    function testTime24()
    {
        $this->assertTrue(Valid::time24('12:15'));
        $this->assertTrue(Valid::time24('10:26:59'));
        $this->assertFalse(Valid::time24('08-00PM'));
    }

    /**
     * @covers Valid::timezone
     */
    function testTimezone()
    {
        $this->assertTrue(Valid::timezone('+00:00'));
        $this->assertTrue(Valid::timezone('-05:00'));
        $this->assertFalse(Valid::timezone('08-00'));
    }

    /**
     * @covers Valid::token
     */
    function testToken()
    {
        $this->assertTrue(Valid::token('sdfdsafdsfds@@#23fsadf'));
        $this->assertFalse(Valid::token('sdfsd sde'));
    }

    /**
     * @covers Valid::usssn
     */
    function testUsssn()
    {
        $this->assertTrue(Valid::usssn('987-65-4329'));
        $this->assertFalse(Valid::usssn('987-65-4320-2323'));
    }

    /**
     * @covers Valid::utf8
     */
    function testUTF8()
    {
        $this->assertTrue(Valid::utf8('\xe2\x82\xac'));
    }

    /**
     * @covers Valid::regexpr
     */
    function testRegexpr()
    {
        $this->assertTrue(Valid::regexpr('/ss\d+/is'));
        $this->assertFalse(Valid::regexpr('/ klkl \ d'));
    }

    /**
     * @covers Valid::date
     */
    function testDate()
    {
        $this->assertTrue(Valid::date('10/16/13 04:20 PM'));
        $this->assertFalse(Valid::date('dsfdsf'));
    }
}
