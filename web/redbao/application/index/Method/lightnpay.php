<?php
/**
 * Class newpay
 * 作者：岑明
 * 2019/4/23
 */

namespace app\index\Method;

class lightnpay
{

    private static $fxgetway = "http://www.ldxjjr.cn/apisubmit"; //新支付
    private $data;

    public function __construct($data)
    {
        $data['sign'] = $this->makeSign([
                                            'version'    => $data['version'] ?? '1.0',//支付平台版本号
                                            'customerid' => $data['customerid'],//商户编号
                                            'userkey'    => $data['userkey'],//接入密钥
                                            'sdorderno'  => $data['sdorderno'],//订单号
                                            'total_fee'  => number_format($data['total_fee'], 2, '.', ''),
                                            'paytype'    => $data['paytype'],//支付编号
                                            'bankcode'   => $data['bankcode'], //银行编号
                                            'notifyurl'  => $data['notifyurl'],//异步地址
                                            'returnurl'  => $data['returnurl'],//同步地址
                                            'remark'     => $data['remark'],//支付备注
                                            'get_code'   => $data['get_code'],// 微信选项

                                        ]);
        unset($data["userkey"]);
        $this->data = $data;

    }

    public function pay()
    {

        return ["status" => 2, 'msg' => 'ok', "way" => self::$fxgetway, "data" => $this->data];

    }

    public static function notify($y, $post)
    {
        $api_key = config("lightnpay_token")['api_key'];
        $status = input('post.status', null);
        $customerid = input('post.customerid', null);
        $sdorderno = input('post.sdorderno', null);
        $total_fee = input('post.total_fee', null);
        $paytype = input('post.paytype', null);
        $sdpayno = input('post.sdpayno', null);
        $remark = input('post.remark', null);
        $sign = input('post.sign', null);
        Handle::writeNotify($sdorderno, $y, $post);
        if ($status == '1') {
            $mysign = md5('customerid=' . $customerid . '&status=' . $status . '&sdpayno=' . $sdpayno . '&sdorderno=' . $sdorderno . '&total_fee=' . $total_fee . '&paytype=' . $paytype . '&' . $api_key);
            if ($sign == $mysign) {
                return Handle::notifyCallback($sdorderno, $sdpayno, $total_fee, ['success', 'fail', 'fail']);
            }
            else {
                return ("signerr");
            }
        }
        else {
            return ("fail");
        }
    }

    private function makeSign($data)
    {
        return md5('version=' . $data["version"] . '&customerid=' . $data["customerid"] . '&total_fee=' . $data["total_fee"] . '&sdorderno=' . $data["sdorderno"] . '&notifyurl=' . $data["notifyurl"] . '&returnurl=' . $data["returnurl"] . '&' . $data["userkey"]);

    }

}