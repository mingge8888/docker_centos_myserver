<?php
/**
 * Class remitmoney
 * 作者：岑明
 * 2019/4/19
 */

//付钱
class  zstop_cn
{

    protected static $apiUrl = 'http://127.0.0.1/zstop_cn/jieru.php';//'http://www.zstop.cn/jieru.php';
    protected static $apiUid = '2824';
    protected static $secretKey = '76fdae18f813f3669c9e750fc1154c58af6be4d3';
    protected $isTest = false;

    private function getTest()
    {
        return ["o"                => "yes",
                "orderid"          => "201904231316443" . mt_rand(1000, 9999),
                "payment_no"       => "1507019761201904260526216022",
                "partner_trade_no" => "2019042313164430" . mt_rand(1000, 9999),
                "payment_time"     => date('Y-m-d H:i:s'),
                "tixianid"         => mt_rand(100000, 999999)
        ];
    }

    public function urlToopenId($url)
    {
        $_SESSION['isopenid'] = 1;
        $url = "http://jfcms12.com/openid.php?mid=" . self::$apiUid . "&url=" . $url;
        header("Location:" . $url);
        exit;
    }

    public function commit($openid, $money, $tixianid, $remark, $hasTest = false, $isshenhe = false)
    {
        if ($isshenhe) {//开始本站审核，给个假数据出来，后台审
            return ["o"                => "shenhe",
                    "ismyshenhe"             => true,
                    "orderid"          => "0",
                    "payment_no"       => "0",
                    "partner_trade_no" => "0",
                    "payment_time"     => date('Y-m-d H:i:s'),
                    "tixianid"         => $tixianid
            ];
        }
        if ($this->isTest === true || $hasTest === true) {
            return $this->getTest();

        }

        $post_data = array(
            'mid'      => self::$apiUid,
            'jine'     => $money,
            'openid'   => $openid,
            'tixianid' => $tixianid,
            'lailu'    => $remark . "||" . $money,
        );

        $post_data['lx'] = 999;
        $post_data['mkey'] = md5($post_data['mid'] . $post_data['jine'] . $post_data['openid'] . self::$secretKey);

        $ret = $this->curlData([
                                   'url'     => self::$apiUrl,
                                   'method'  => 'POST',
                                   'headers' => [],
                                   'data'    => $post_data
                               ]);

        $ret = $ret ? json_decode($ret, true) : ["error" => 1, "msg" => "网络错误"];

        return $ret;
    }

    protected function curlData($arg = [])
    {
        return '{"o":"yes","orderid":"20190506015300431529","payment_no":"1507019761201905061910520303","partner_trade_no":"20190506015300431529","payment_time":"2019-05-06 01:53:01","tixianid":"47566233567"}';
        $arg = array_merge(
            [
                'url'     => 'http://www.baidu.com',
                'method'  => 'POST',
                'headers' => [],
                'data'    => []
            ],
            $arg
        );

        $method = strtoupper($arg['method']);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 30000);
        if ($method == 'POST') {
            $url = $arg['url'];
            curl_setopt($curl, CURLOPT_POSTFIELDS, $arg['data']);
        }
        else {
            $url = $arg['url'] . "?" . http_build_query($arg['data']);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $arg['headers']);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$" . $arg['url'], "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $ret = curl_exec($curl);
        $ret = str_replace('﻿', '', trim($ret));//JSON里面有不知名字符，重要
        curl_close($curl);
        return $ret;
    }
}