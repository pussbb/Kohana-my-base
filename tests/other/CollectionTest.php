
<?php

Class CollectionTest extends Unittest_TestCase
{
    function providerCollection()
    {
        return array(
            array(
                array(
                    (object)array('id'=> 1, "name" => "user"),
                    (object)array('id'=> 2, "name" => "user2"),
                ),
                array(
                    1 => (object)array('id'=> 1, "name" => "user"),
                    2 => (object)array('id'=> 2, "name" => "user2"),
                ),
                array(
                    1 => 'user',
                    2 => 'user2'
                )
            ),
        );
    }

    function providerTree()
    {
        return array(
            array(
                array(
                    (object)array('id'=> 1, "parent_id" => NULL),
                    (object)array('id'=> 2, "parent_id" => 1),
                ),
                array(
                    1 => array(
                        'object' => (object)array('id'=> 1, "parent_id" => NULL),
                        'childs' => array(
                            2 => array(
                                'object' => (object)array('id'=> 2, "parent_id" => 1),
                                'childs' => array()
                            )
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider providerCollection
     */
    function testHash($collection, $hash)
    {
        $this->assertEquals(
            $hash,
            Collection::hash($collection, 'id')
        );
    }

    /**
     * @expectedException ErrorException
     */
    function testHashMalformedParametrs()
    {
        Collection::hash('sdsd', 'id');
        Collection::hash('sdsd');
    }

    /**
     * @dataProvider providerCollection
     */
    function testForSelect($collection, $hash, $select)
    {
        $this->assertEquals(
            $select,
            Collection::for_select($collection, 'name')
        );
    }

    /**
     * @expectedException ErrorException
     * @dataProvider providerCollection
     */
    function testForSelectMalformedParametrs($collection)
    {
        Collection::for_select('sdsd', 'id');
        Collection::for_select('sdsd');
        Collection::for_select($collection, 'mdmdmdm');
        var_dump(Collection::for_select($collection, 'mdmdmdm'));
    }

    /**
     * @dataProvider providerCollection
     */
    function testPluck($collection)
    {
        $this->assertEquals(
            array(1,2),
            Collection::pluck($collection, 'id')
        );
    }

    /**
     * @dataProvider providerTree
     */
    function testBuildTree($collection, $tree)
    {
        $this->assertEquals(
            $tree,
            Collection::build_tree($collection)
        );
    }

    /**
     * @dataProvider providerCollection
     */
    function testPropertyExists($collection, $hash)
    {
        $first = $collection[0];
        $this->assertTrue(Collection::property_exists($first, 'id'));
        $this->assertFalse(Collection::property_exists($first, 'sdsd'));
        $this->assertTrue(Collection::property_exists($hash, 1));
        $this->assertFalse(Collection::property_exists($hash, 2323));
    }

    /**
     * @dataProvider providerCollection
     */
    function testProperty($collection, $hash)
    {
        $first = $collection[0];
        $this->assertEquals(1, Collection::property($first, 'id'));
        $this->assertNull(Collection::property($first, 'sdsd'));
        $this->assertEquals($first, Collection::property($hash, 1));
        $this->assertNull(Collection::property($hash, 2323));
    }
}
