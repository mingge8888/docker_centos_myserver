<?php
/**
 * Class newpay
 * 作者：岑明
 * 2019/4/23
 */

class superpay
{

    private static $fxgetway = "http://www.fo53p.cn/Payment/Pay.html"; //新支付
    private $data;

    public function __construct($data)
    {
        $data['sign'] = $this->makeSign([
                                            'merchant_no'  => $data['merchant_no'],
                                            'money'        => $data['money'],
                                            'trade_type'   => $data['trade_type'],
                                            'notify_url'   => $data['notify_url'],
                                            'back_url'     => $data['back_url'],
                                            'out_trade_no' => $data['out_trade_no'],

                                        ], $data['key']);
        unset($data["key"]);
        $this->data = $data;

    }

    public function pay()
    {

        $ret = json_decode($this->curl(self::$fxgetway, "POST", $this->data), true);
        if (empty($ret)) {
            return ["error" => 1, "msg" => "支付网关取不到数据"];
        }

        if ($ret["code"] == 1) {
            return ["ok" => 1, "msg" => $ret["pay_url"]];
        }

        return ["error" => 1, "msg" => $ret['msg']];

    }

    private function makeSign($post, $wx_key)
    {
        if ($post) {
            ksort($post);
            unset($post['sign']);
            $str1 = '';
            foreach ($post as $k => $v) {
                $str1 .= $k . '=' . $v . '&';
            }
            // 拼接key
            $str2 = $str1 . 'key=' . $wx_key;
            // md5编码并转成大写
            $sign2 = strtoupper(md5($str2));
            return $sign2;
        }
        return false;
    }

    private function curl($url, $method = 'GET', $postData = array())
    {
        $data = '';
        $user_agent = $_SERVER ['HTTP_USER_AGENT'];
        $header = array(
            "User-Agent: $user_agent"
        );
        if (!empty($url)) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30); //30秒超时
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
                if (strstr($url, 'https://')) {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                }

                if (strtoupper($method) == 'POST') {
                    $curlPost = is_array($postData) ? http_build_query($postData) : $postData;
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                }
                $data = curl_exec($ch);
                curl_close($ch);
            } catch (Exception $e) {
                $data = '';
            }
        }
        return $data;
    }
}