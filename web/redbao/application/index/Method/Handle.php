<?php
/**
 * Class index
 * 作者：岑明
 * 2019/6/13
 */

namespace app\index\Method;

use think\Db;
use think\Session;

class Handle
{

    public static function addGameOrder($config)
    {

        $checkhbtype = Db('hbtype')->where([
                                               'hb_type_id' => $config['hb_type_id'],
                                               'pay_amount' => ['<=', $config['pay_amount']]
                                           ])->value("hb_type_id");
        if ($checkhbtype) {
            $config['createtime'] = $config['updatetime'] = time();
            $config['status'] = 0;
            return Db('order')->insert($config);
        }
        return false;

    }

    public static function writeNotify($order, $paytype, $data)
    {
        if (!$order || $paytype || $data) {
            return;
        }
        if (!is_string($data)) {
            $data = @json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return Db('notify')->insert([
                                        'type'       => 3,
                                        'order_no'   => $order,
                                        'content'    => $data,
                                        'createtime' => time(),
                                        'paytype'    => $paytype
                                    ]);

    }

    public
    static function notifyCallback($orderId, $payno, $total_fee, $arrText)
    {

        $order = Db('order a')->join("hbtype b", 'a.hb_type_id = b.hb_type_id')->where([
                                                                                           'a.pay_amount' => ['>=',
                                                                                                              Db::raw("b.pay_amount")
                                                                                           ],
                                                                                           'a.order_no'   => $orderId
                                                                                       ])
                              ->field('a.user_id,a.pay_amount,b.hb_type_id,a.status')->find();

        if ($order && $order['status'] == 0) {
            try {
                Db::startTrans();
                $user_id = (int)$order['user_id'];//用户
                $pay_amount = (float)$order['pay_amount'];//订单的钱
                $hb_type_id = (int)$order['hb_type_id'];//红包id
                $total_fee = (float)$total_fee;
                if ($total_fee < $pay_amount) {
                    Db::rollBack();//事务回滚
                    return $arrText[1];
                }
                if (Db('order')->where([
                                           'order_no' => $orderId
                                       ])->update([
                                                      'status'      => 1,
                                                      'updatetime'  => time(),
                                                      'payorder_no' => $payno
                                                  ])) {
                    //处理佣金分成开始

                    //处理佣金分结束
                    Db::commit();  //提交事务
                    return $arrText[0];

                }

            } catch (Exception $e) {

            }
            Db::rollBack();//事务回滚
            return ($arrText[1]);

        }
        elseif (isset($order['status']) && ($order['status'] == 1 || $order['status'] == 2)) {
            return $arrText[0];
        }
        else {
            return ($arrText[2]); //服务器错误导致异常处理触发
        }
    }

    public
    static function pay($config)
    {
        $paytype = $config['paytype'];
        $user_id = $config['user_id'];
        $hb_type_id = $config['hb_type_id'];
        $fail = $config['fail'];

        list($app_id, $api_key) = array_values(config("livepay_token"));
        list($superpay_id, $superpay_key) = array_values(config("superpay_token"));
        list($cpay_id, $cpay_key) = array_values(config("cpay_token"));
        list($swiftpay_id, $swiftpay_key) = array_values(config("swiftpay_token"));
        list($fastpay_id, $fastpay_key) = array_values(config("fastpay_token"));
        list($lightnpay_id, $lightnpay_key) = array_values(config("lightnpay_token"));
        $hb_type_money = Db('hbtype')->where(['hb_type_id' => $hb_type_id])->value('pay_amount');
        if (!$hb_type_money) {
            return $fail('nobad', '红包id不存在');
        }
        $hb_type_money = (float)$hb_type_money;
        $hb_type_money = bcadd($hb_type_money, bcmul(mt_rand(0, 31), 0.01, 2), 2);
        $order = self::orderid();
        $endNum = count($paytype) - 1;
        $paytypes = $endNum ? mt_rand(0, $endNum) : 0;
        $paytypes = $paytype[$paytypes];
        $returnurl = config('domain') . "/index/index/open";
        $notify_url = config('domain') . "/index/api/paynotif?y=";
        try {
            Db::startTrans();
            if (!self::addGameOrder([
                                        'user_id'    => $user_id,
                                        'order_no'   => $order,
                                        'hb_type_id' => $hb_type_id,
                                        'pay_amount' => $hb_type_money,
                                        'get_amount' => 0,
                                        'paytype'    => $paytypes,
                                    ])) {
                Db::rollBack();//事务回滚
                return $fail('order_submit_fail', '提交订单失败');
            }

            if ($paytypes == 'love') {//爱支付

                $lp = new lovepay([//设置支付
                                   "fxid"        => $app_id, //商户号
                                   "fxkey"       => $api_key,//商户秘钥key 从商户后台获取
                                   "fxddh"       => $order, //商户订单号
                                   "fxdesc"      => "帐号冲值", //商品名
                                   "fxfee"       => $hb_type_money, //支付金额 单位元
                                   "fxattch"     => $user_id . "|充值|" . $hb_type_money, //附加信息
                                   "fxnotifyurl" => $notify_url . "lovenotify",
                                   //异步回调 , 支付结果以异步为准
                                   "fxbackurl"   => $returnurl,
                                   //同步回调 不作为最终支付结果为准，请以异步回调为准
                                   "fxpay"       => "wxgzh",//支付方式
                                   'fxbankcode'  => '',
                                   'fxfs'        => '',
                                  ]);
            }
            /* ---------------爱支付发起请求-------结束------------ */

            /* ---------------超极支付发起请求-------开始------------ */
            elseif ($paytypes == 'super') {//超极支付

                $lp = new superpay([
                                       'merchant_no'  => $superpay_id,//商户订单号
                                       'key'          => $superpay_key,
                                       'trade_type'   => "wx_pub",
                                       'money'        => $hb_type_money, //支付金额 单位元
                                       'notify_url'   => $notify_url . "supernotify",
                                       'back_url'     => $returnurl,
                                       'out_trade_no' => $order
                                   ]);

            }
            /* ---------------超极支付发起请求-------结束------------ */

            /* ---------------C+支付发起请求-------开始------------ */
            elseif ($paytypes == 'c') {//C+支付

                $lp = new cpay([
                                   'version'    => $data['version'] ?? '1.0',//支付平台版本号
                                   'customerid' => $cpay_id,//商户编号
                                   'userkey'    => $cpay_key,//接入密钥
                                   'sdorderno'  => $order,//订单号
                                   'total_fee'  => $hb_type_money,//支付金额
                                   'paytype'    => 'wx14',//'kjwxh5','wxlit',//'sqbwx','wx14',//支付编号
                                   'bankcode'   => 'ABC', //银行编号
                                   'notifyurl'  => $notify_url . "cnotify",//异步地址
                                   'returnurl'  => $returnurl,//同步地址
                                   'remark'     => $user_id . "|充值|" . $hb_type_money,//支付备注
                                   'get_code'   => '0',// 微信选项

                               ]);

            }
            /* ---------------C+支付发起请求-------结束------------ */
            /* ---------------闪电支付发起请求-------开始------------ */
            elseif ($paytypes == 'lightn') {//C+支付

                $lp = new lightnpay([
                                        'version'    => $data['version'] ?? '1.0',//支付平台版本号
                                        'customerid' => $lightnpay_id,//商户编号
                                        'userkey'    => $lightnpay_key,//接入密钥
                                        'sdorderno'  => $order,//订单号
                                        'total_fee'  => $hb_type_money,//支付金额
                                        'paytype'    => 'klwxh5',//'wxh5','alipay',//'wxsm','alism',//支付编号
                                        'bankcode'   => 'ABC', //银行编号
                                        'notifyurl'  => $notify_url . "lightnnotify",
                                        //异步地址
                                        'returnurl'  => $returnurl,//同步地址
                                        'remark'     => $user_id . "|充值|" . $hb_type_money,//支付备注
                                        'get_code'   => '0',// 微信选项

                                    ]);

            }
            /* ---------------闪电支付发起请求-------结束------------ */
            /* ---------------fastpay支付发起请求-------开始------------ */
            elseif ($paytypes == 'fast') {//fastpay支付

                $lp = new fastpay([
                                      'appkey'      => $fastpay_id,
                                      'secretkey'   => $fastpay_key,
                                      'uid'         => $user_id,
                                      'order_no'    => $order,
                                      'pay_title'   => '微信安全支付',
                                      'total_fee'   => $hb_type_money,
                                      'param'       => $user_id . "|充值|" . $hb_type_money,//备注,
                                      'me_back_url' => $notify_url . "fastnotify",
                                      'notify_url'  => $returnurl
                                  ]);

            }
            /* ---------------fastpay发起请求-------结束------------ */
            /* ---------------迅捷支付发起请求-------开始------------ */
            elseif ($paytypes == 'swift') {//迅捷支付

                $lp = new swiftpay([
                                       'app_id'         => $swiftpay_id,//商户订单号
                                       'key'            => $swiftpay_key,
                                       'third_order_id' => $order, //订单号
                                       'price'          => $hb_type_money, //支付金额 单位元
                                       'nonce_str'      => $user_id . "|充值|" . $hb_type_money,//备注
                                       'notify_url'     => $notify_url . "swiftnotify",
                                       'return_url'     => $returnurl,//同步地址,

                                   ]);

            }
            /* ---------------迅捷支付发起请求-------结束------------ */
            /* ---------------都不是-------开始------------ */
            else {
                Db::rollBack();//事务回滚
                return $fail('error', '致命错误');
            }
            /* ---------------都不是-------结束------------ */
            $retpay = $lp->pay();//取请求回调

            Db::commit();  //提交事务
            return $retpay;//输出结果

        } catch (Exception $e) {//异常处理
            Db::rollBack();//事务回滚

        }
        return $fail('error', '致命错误');
    }

    public
    static function orderid()
    {

        return "MH" . date("YmdHis") . rand(100000, 999999);
    }

    public
    static function ispay(int $hb_type_id, int $user_id)
    {
        $where = [

            'user_id'      => $user_id,
            'a.status'     => 1,
            'a.pay_amount' => ['>=', Db::raw("b.pay_amount")]
        ];

        if ($hb_type_id) {
            $where['a.hb_type_id'] = $hb_type_id;
        }
        return Db('order a')->join("hbtype b", 'a.hb_type_id = b.hb_type_id')->where($where)->order('createtime desc')
                            ->field('a.pay_amount,b.hb_type_id,a.status')
                            ->find();

    }

    public
    static function is_parent_user_id(int $parent_user_id)
    {
        return Db('user')->where(["id" => $parent_user_id])->value("id");
    }

    public
    static function jf($userinfo, $outTime)
    {

        $jfopenid = $userinfo['jfopenid'];
        $jfopenid_time = (int)$userinfo['jfopenid_time'];
        if ($jfopenid_time && $jfopenid) {
            $timedc = time() - $jfopenid_time;
            if ($timedc > -1 && $timedc < $outTime) {
                return true;
            }
        }
        $openid = input('openid');
        if ($openid && !isset($_SERVER['HTTP_REFERER'])) {
            $ret = Db('user')->where(["id" => $userinfo['id']])->update([
                                                                            'jfopenid'      => $openid,
                                                                            'jfopenid_time' => time()
                                                                        ]);

            return true;
        }
        return false;

    }

    public
    static function get_parent_user_id()
    {
        $parent_user_id = input('parent_user_id');
        $parent_user_id = $parent_user_id ? $parent_user_id : Session::get('parent_user_id');
        if ($parent_user_id) {
            if (Session::has('parent_user_id')) {
                Session::delete('parent_user_id');
            }
            $isgo = $parent_user_id == 'go';
            if ($isgo || self::is_parent_user_id($parent_user_id)) {
                Session::set('parent_user_id', (int)$parent_user_id);
                return $parent_user_id;
            }
        }
        return false;
    }

    public static function isweixin()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
        $mobile_browser = '0';
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($user_agent)))
            $mobile_browser++;
        if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== false))
            $mobile_browser++;
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
            $mobile_browser++;
        if (isset($_SERVER['HTTP_PROFILE']))
            $mobile_browser++;
        $mobile_ua = strtolower(substr($user_agent, 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda', 'xda-'
        );
        if (in_array($mobile_ua, $mobile_agents))
            $mobile_browser++;
        if (strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
            $mobile_browser++;
        // Pre-final check to reset everything if the user is on Windows
        if (strpos(strtolower($user_agent), 'windows') !== false)
            $mobile_browser = 0;
        // But WP7 is also Windows, with a slightly different characteristic
        if (strpos(strtolower($user_agent), 'windows phone') !== false)
            $mobile_browser++;
        if ($mobile_browser > 0)
            return strpos($user_agent, 'MicroMessenger') !== false;
        else
            return false;

    }

    public
    static function handleEntrance($host)
    {

        $host = str_replace(["\\", "/", "http:", "https:"], '', $host);
        return $host == $_SERVER['HTTP_HOST'];

    }
}