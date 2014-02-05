<?php

class ModelTest extends Unittest_TestCase {

    /**
     * @expectedException Base_Db_Exception_RecordNotFound
     * @covers Base_Model::find
     */
    function testFind()
    {
        Model_User::find(-546456);
        Model_User::find('546456');
    }

    /**
     * @covers Base_Model::find_all
     */
    function testFindAll()
    {
        $m = Model_User::find_all();
        $this->assertGreaterThan(0, $m->total_count);
        foreach($m as $_m) {
            $this->assertInstanceOf('Base_Model', $_m);
            foreach ($_m as $key => $item) {
                $this->assertInternalType('string', $key);
            }
        }
    }

    /**
     * @covers Base_Model::with
     * @depends testFindAll
     */
    function testWith(){
        $all = Model_User::find_all(array('with' => 'access_rules'));
        foreach($all as $item)
        {
            $this->assertArrayHasKey('access_rules', $item->as_array());
        }
        $model = Model_User::find_all(array(
            'with' => array('access_rules'),
            'access_rules.role_id' => 0,
        ));
        $accepted  ="SELECT `user`.*, `access_rule`.`id` AS `access_rule:id`, `access_rule`.`role_id` AS `access_rule:role_id`, `access_rule`.`directory` AS `access_rule:directory`, `access_rule`.`controller` AS `access_rule:controller`, `access_rule`.`action` AS `access_rule:action` FROM `users` AS `user` LEFT JOIN `access_rules` AS `access_rule` ON (`user`.`role_id` = `access_rule`.`role_id`) WHERE (`access_rule`.`role_id` = 0) ORDER BY `email` DESC";
        $this->assertEquals($accepted, (string)$model);
        $model = Model_User::find_all(array(
            'with' => array('access_rules'),
            ' ! access_rules.role_id  ' => NULL,
            'limit' => 10
        ));
        $accepted = "SELECT `user`.*, `access_rule`.`id` AS `access_rule:id`, `access_rule`.`role_id` AS `access_rule:role_id`, `access_rule`.`directory` AS `access_rule:directory`, `access_rule`.`controller` AS `access_rule:controller`, `access_rule`.`action` AS `access_rule:action` FROM (SELECT `user`.* FROM `users` AS `user` ORDER BY `email` DESC LIMIT 10) AS `user` LEFT JOIN `access_rules` AS `access_rule` ON (`user`.`role_id` = `access_rule`.`role_id`) WHERE (`access_rule`.`role_id` IS NOT  NULL)";
        $this->assertEquals($accepted, (string)$model);

        foreach(Model_User::find_all() as $u) {
            $i = clone $u;
            $this->assertInternalType('array', $i->access_rules);
            $this->assertInternalType('array', $u->access_rules(array('role_id' => 0)));
        }

    }

    /**
     * @expectedException Exception_Collection_PropertyNotExists
     * @depends testFindAll
     */
    function testPropertyNotExists(){
        $all = Model_User::find_all();
        foreach($all as $item)
        {
            $item->dfdsfds;
        }
    }

    /**
     *@covers Base_Model::delete_query
     */
    function testDeleteQuery(){
        $d = Model_User::delete_query(array('id'=> 'sdsd'));
        $this->assertInstanceOf('Base_Db_Query_Builder_Delete', $d);
        $accepted  = "DELETE `user` FROM `users` AS `user` WHERE (`user`.`id` = 0)";
        $this->assertEquals($accepted, (string)$d);
    }

    /**
     *@covers Base_Model::select_query
     */
    function testSelectQuery(){
        $d = Model_User::select_query('*', array('id'=> 'sdsd'));
        $this->assertInstanceOf('Database_Query_Builder_Select', $d);
        $accepted = "SELECT `user`.* FROM `users` AS `user` WHERE (`user`.`id` = 0)";
        $this->assertEquals($accepted, (string)$d);
    }
}
