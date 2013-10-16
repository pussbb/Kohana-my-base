<?php

class prop {
    protected $some = 23;
    private $d = array();
}

class ObjectTest extends Unittest_TestCase
{

    /**
     * @covers Object::property
     */
    function testProperty()
    {
        $this->assertSame(
            1,
            Object::property((object)array('id'=> 1), 'id')
        );
    }

    /**
     * @covers Object::property_exists
     */
    function testPropertyExists()
    {
        $this->assertFalse(
            Object::property_exists((object)array('id'=> 1), 'ddid')
        );

    }

    /**
     * @expectedException Exception_NotObject
     * @covers Object::property_exists
     * @covers Object::property
     */
    function testObjecthMalformedParametrs()
    {
        Object::property('sdsd', '');
        Object::property(array(), '');
        Object::property_exists(array());
        Object::property_exists('sdsd');
    }

    /**
     * @covers Object::properties
     */
    function testPublicProperties()
    {
        $this->assertSame(array(), Object::properties(new prop));
    }

    /**
     * @covers Object::properties
     */
    function testAllProperties()
    {
        $this->assertSame(
            array('some'=>23, 'd'=> array()),
            Object::properties(new prop, TRUE)
        );
    }
}
