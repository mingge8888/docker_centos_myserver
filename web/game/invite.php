<?php
define('ONGHEAD','Content-type:application/json;charset=UTF-8');  //强制输出头部
define('ONGPHP', dirname(__FILE__).'/ong/');
define('ONGTEMP', 'temp');   //缓存存放目录
define('ONGCON', '');        //系统配置
define('ONGDB', '');         //数据库配置
define('ONGNAME', 'invite');  //调用model里面控制器
define('ONGQHTPL', '');      //强行后台模版
define('ONGQQTPL', '');      //强行前台模版
require ONGPHP.'zongderukou.php';