<?php
/**
 * Created by PhpStorm.
 * User: pussbb
 * Date: 2/15/14
 * Time: 10:22 PM
 */

if ($e instanceof Exception_Tools) {
    include Kohana::find_file('views', 'errors/tools');
} else {
    include Kohana::find_file('views', 'errors/error');
}
