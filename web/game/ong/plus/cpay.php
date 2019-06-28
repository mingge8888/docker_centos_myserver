<?php
/**
 * Class newpay
 * 作者：岑明
 * 2019/4/23
 */

class cpay
{

    private static $fxgetway = "http://www.fu-nice.com/apisubmit"; //新支付
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
         return ["ok" => 2,"way"=>self::$fxgetway,"data"=>$this->data];

    }

    private function makeSign($data)
    {
        return md5('version=' . $data["version"] . '&customerid=' . $data["customerid"] . '&total_fee=' . $data["total_fee"] . '&sdorderno=' . $data["sdorderno"] . '&notifyurl=' . $data["notifyurl"] . '&returnurl=' . $data["returnurl"] . '&' . $data["userkey"]);
    }


}