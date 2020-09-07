<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//数据库配置
$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'aria_data';
$db['default']['password'] = 'DrS6B2Y3Xp2Gmba3';
$db['default']['database'] = 'aria_data';
$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

//redis配置
$redis_config['host'] = "127.0.0.1";
$redis_config['port'] = "6379";
$redis_config['password'] = "ahY2hX2emmAsyw5k";

//状态码
if(!defined("STATUS_CODE")) {
    define("STATUS_CODE", [
        'SUCCESS'=>['code'=>0, 'text'=>'Content-Type:application/json; charset=utf-8'], //成功
        'ERROR'=>['code'=>500, 'text'=>'Content-Type:application/json; charset=utf-8'],//通用错误
        'TOKEN_ERR'=>['code'=>401, 'text'=>'HTTP/1.1 401 Unauthorized'],//token错误
    ]);
}

if(!defined("ROOT_PATH")) {
    define("ROOT_PATH",str_replace('system/','',BASEPATH));
}

if(!defined("UPLOAD_PATH")) {
    define("UPLOAD_PATH",ROOT_PATH.'uploads/');
}