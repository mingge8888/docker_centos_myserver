<?php
/**
 * Class fast
 * 作者：岑明
 * 2019/6/21
 */

namespace app\index\controller;
use think\controller;
class fast extends controller
{

    public function index()
    {

        $req = $_REQUEST;
        $this->assign('back_url', $req['me_back_url']);
        $this->assign('me_from', urlencode(@$_SERVER["HTTP_REFERER"]));
        $this->assign('data', $req);
        $this->assign('is_weixin', strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false);
        return $this->fetch('/index/fast');
    }
}