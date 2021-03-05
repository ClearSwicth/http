<?php
/**
 *
 * User: daikai
 * Date: 2021/3/4
 */
//spl_autoload_register();
require_once 'src/Request.php';
$request=new \http\Request();
$request->send();
print_R($request);exit;
