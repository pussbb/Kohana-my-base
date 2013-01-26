<?php
/**
 * This file just only to show how to use aditional functionality of model
 * nothing else. do not try to run it manualy
 * ALL VALUES WILL BE SANITIZED IN SQL QUERY
 */

 // relations
$model = Model_Blog_Post::find(array(
    'id' => 7,
    'with' => 'contents',
));
// or
$model = Model_Blog_Post::find(array(
    'id' => 7,
    'with' => array('contents', 'cotegory'),
));

 // relations and filter with relation field
$model = Model_Blog_Post::find(array(
    'id' => 7,
    'with' => array('contents', 'cotegory'),
    'contents.id' => 'uiuiu',
));

/**
 * comparision keys for fields:
 *  ! - NOT equals
 *  <> - NOT equals
 *  || - or where clause
 *  < - less than
 *  > - more than
 */
$model =  Model_Blog_Post_Content::find_all(array(
    '! id' => 7,
    '|| post_id' => 99,
));

// sub query
$model =  Model_Blog_Post_Content::find_all(array(
    '! id' => 7,
    'post_id' => Model_Blog_Post::select_query('id',array('author_id' => 1), 1),
));

// using expression in where clause
// generates YEAR(news.created_at) = 2010
$model = Model_News::find_all(array(
    'expression' => array(
        'YEAR(%s) = %s',
        'created_at' => 2010
    )
));

$year = $this->request->param('year');
$month = $this->request->param('month');
if ($month)
    $expression = array('YEAR(%s) = %s AND MONTH(%s) = %s', 'created_at' => array($year, intval($month)));
else
    $expression = array('YEAR(%s) = %s ', 'created_at' => $year,);



// get original Kohana_DB class but initialized with Based_Model logic
Model_Blog_Post::select_query();
Model_Blog_Post::update_query();
Model_Blog_Post::insert_query();
Model_Blog_Post::delete_query();
