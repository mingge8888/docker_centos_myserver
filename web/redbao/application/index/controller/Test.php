<?php

namespace app\index\controller;

use think\controller;
use think\Db;

class Test extends controller
{

    public function index2()
    {
        //    $ret = Db::execute("lock  tables  wx_test2 WRITE");
        return json(Db('test')->where('id', 2)->update(['str' => mt_rand(1, 199)]));
    }

    public function index()
    {
        Db::startTrans();
        //$num = Db('test')->where('id', 1) ->where('num','>',97) ->lock(true)->select(); //并发锁
        //$ret = Db::execute("lock  tables  wx_test2 WRITE");

        Db::execute("lock tables wx_test READ");
        $num2 = Db('test')->count();

        $time = time();

        while (time() - $time < 20) {

        }
        // $num = Db('test')->count();
        if (!$num2) {
            $id = Db('test2')->insertGetId([
                                               'str' => mt_rand(1, 99),
                                               'com' => mt_rand(1, 99),
                                               'num' => mt_rand(1, 99)
                                           ]);

            Db::commit();
            Db::execute(" unlock tables");
            file_put_contents('test/test' . mt_rand(1, 666666) . '.txt', print_r($id, true));
            exit("生成完毕");
        }
        Db::execute(" unlock tables");
        Db::rollback();
        exit("回滚");
    }
}