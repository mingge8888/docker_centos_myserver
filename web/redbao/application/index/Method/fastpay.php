<?php
/**
 * Class fastpay
 * 作者：岑明
 * 2019/6/21
 */

namespace app\index\Method;

class fastpay
{

    private static $fxgetway = "http://k.cross.echosite.cn/index/fast"; //fast免签
    private $data;

    public function __construct($paydata)
    {
        $data = array(
            'appkey'          => '',//你的appkey
            'secretkey'       => '',//密钥
            'total_fee'       => '',//你的金额
            'order_no'        => '',//你的订单号
            'pay_title'       => 'fastpay支付',//你的订单号
            'me_param'        => '',//其他参数,可返回回调里面
            'notify_url'      => '',//异步回调地址
            'me_back_url'     => '',//支付成功后返回
            'me_eshop_openid' => '',//付款用户openid
            'me_party'        => '',//根据其他支付插件,异步回调返回同样参数,比如填写codepay,码支付,我们异步回调的时候就按码支付的回调参数返回
            'sign'            => ''//签名
        );
        $this->data = array_merge($data, $paydata);

    }

    public function pay()
    {

        $data = $this->data;
        if (!is_array($data)) {
            return ["status" => 0, "msg" => "error", "data" => "参数错误"];
        }
        if (empty($data['appkey'])) {
            return ["status" => 0, "msg" => "error", "data" => "appkey没有填写"];

        }
        if (empty($data['total_fee'])) {
            return ["status" => 0, "msg" => "error", "data" => "金额不能为空"];

        }
        if (empty($data['uid'])) {
            return ["status" => 0, "msg" => "error", "data" => "付款用户id不能为空"];

        }
        if (empty($data['order_no'])) {
            return ["status" => 0, "msg" => "error", "data" => "订单号不能为空"];
        }
        if (!empty($data['$me_back_url'])) {
            $data['me_back_url'] = urlencode($data['me_back_url']);
        }
        if (!empty($data['notify_url'])) {
            $data['notify_url'] = urlencode($data['notify_url']);
        }
        $data['total_fee'] = bcadd($data['total_fee'], 0, 2);
        $secretkey = $data['secretkey'];
        unset($data['secretkey']);
        $data['sign'] = self::sign($data, $secretkey);
        return ["status" => 1, "msg" => "ok", "data" => self::$fxgetway . "?" . http_build_query($data)];

    }

    public static function notify($y, $post)
    {
        $api_key = config("fastpay_token")['api_key'];
        $sign = $post['sign_notify'];//签名
        $check_sign = self::sign($post, $api_key);//被检测
        $status = $post['status'];//状态 y成功
        $total_fee = $post['total_fee'];//支付金额
        $order_no = $post['order_no'];//订单号
        //$uid = $post['uid'];//支付用户
        //$pay_title = $post['pay_title'];//标题
        //$me_pri = $post['me_pri'];//我们网站生成的金额,参与签名的,跟实际金额有差异
        Handle::writeNotify($order_no, $y, $post);
        if ($status == 'y' && $sign == $check_sign) {
            return Handle::notifyCallback($order_no,0,$total_fee,['success', 'fail', 'fail']);
        }
        else {
            return ("fail");
        }
    }

    public static function sign($paydata, $secretkey, $isnotify = false)
    {
        $urlText = $isnotify ? "me_pri={$paydata['me_pri']}" : "total_fee={$paydata['total_fee']}";
        $str_sign = "appkey={$paydata['appkey']}&order_no={$paydata['order_no']}&secretkey=" . $secretkey . "&{$urlText}&uid={$paydata['uid']}&";
        return md5($str_sign);
    }
}