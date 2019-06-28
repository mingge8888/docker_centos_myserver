<?php

include 'zstop_cn.php';

$zstop_cn = new zstop_cn();


$url = $_SERVER['REQUEST_SCHEME'] . '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
 $tixianid = mt_rand(1000000, 99999999999);//提现ID
$result = $zstop_cn->commit('oaowzwwSQDXV_XdQ1UK_PLtjqnKY', 1.00, $tixianid, mt_rand(100, 999));
var_dump($result);