<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\index\Method\Handle;
use app\index\Method\LowerLevel;
use app\index\Method\redBag;
use think\Db;

use app\extra\zstop_cn;

class Api extends Frontend
{

    protected $noNeedLogin = ['paynotif'];
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

    }

    public function paynotif()
    {
        $y = input("get.y");
        $configpaytype = config("paytype");
        $notifyName = "notify";
        $payname = substr_replace($y, '', -strlen($notifyName));
        if ($y && in_array($payname, $configpaytype, true)) { //限制回调安全，防止不良支付恶意回调偷钱
            $spaceName = 'app\\index\\Method';
            $className = $spaceName . '\\' . $payname . "pay";
            if (!class_exists($className) || !method_exists($className, $notifyName)) {
                return ('fail');
            }
            return call_user_func_array([$className, 'notify'], [$y, $this->request->post()]);

        }
        else {
            return ('儿子非法操作');
        }

    }

    public
    function pay() //支付
    {

        $hb_type_id = (int)input("post.hb_type_id");
        $userinfo = $this->auth->getUserinfo();
        if (!$hb_type_id) {
            return $this->fail("not_hb_type_id", "参数错误");
        }
        if (Handle::ispay($hb_type_id, $userinfo['id'])) {
            return $this->ok(["open" => 1, "hb_type_id" => $hb_type_id]);
        }
        else {

            $pay_ret = Handle::pay([
                                       'paytype'    => config("paytype"),
                                       'user_id'    => $userinfo['id'],
                                       'hb_type_id' => $hb_type_id,
                                       'fail'       => function ($enstr, $str) {
                                           return $this->fail($enstr, $str);
                                       }
                                   ]);
            return $pay_ret;
            //跳入支付进行支付
        }

    }

    function openbag()  //开包
    {
        $hb_type_id = (int)input("post.hb_type_id");
        $userinfo = $this->auth->getUserinfo();
        if (!$userinfo['id']) {
            return $this->fail("bag_fail", "拆包发生错误");
        }
        if (!$userinfo['jfopenid']) {
            return $this->fail("tixin_fail", "提现id有误");
        }

        $bag = redBag::getBag([
                                  'h.hb_type_id' => $hb_type_id,
                                  'o.status'     => 1,
                                  'o.user_id'    => $userinfo['id']
                              ], "h.*,o.pay_amount as order_amount,o.order_no", "o.createtime desc");

        if ($bag) {

            $amount = redBag::openAmount($bag); //生成开包金额
            $list = redBag::amountList($bag, 14);//生成假的红包列表
            if (!$amount) {
                return $this->fail("bag_fail", "拆包发生错误");
            }
            if (!$list) {
                return $this->fail("list_fail", "拆包发生错误");
            }
            $amount = bcadd($amount, bcmul(mt_rand(0, 61), 0.01, 2), 2);
            Db::startTrans();
            //更新订单开始
            $orderRet = redBag::updataOrder([
                                                'order_no' => $bag['order_no'],
                                                'user_id'  => $userinfo['id'],
                                                'status'   => 1
                                            ], [
                                                'status'     => 2,
                                                'get_amount' => $amount
                                            ]);

            if ($orderRet) {
                //俊飞给钱他
                $tixianid = mt_rand(10000000, 99999999999);//提现ID
                //写入提现日记
                if ($amount > 0) {
                    $withdrawData = [
                        'user_id'   => $userinfo['id'],
                        'amount'    => $amount,
                        'type'      => 1,//红包
                        'status'    => 1,
                        'tixian_id' => $tixianid,
                        'order_no'  => $bag['order_no'],
                        'result'    => '拆红包钱'
                    ];
                    $withdrawId = redBag::withdrawlog($withdrawData);//写提现日记
                    if (!$withdrawId) {
                        Db::rollBack();
                        return $this->fail("withdraw_fail", "拆包出错了");
                    }

                    $zstop_cn = new zstop_cn();
                    $result = $zstop_cn->commit($userinfo['jfopenid'], $amount, $tixianid, $userinfo["id"] . "|奖励", false, config("isTixianShenhe"));
                    if ($result) {
                        $isresshenhe = @$result["o"] == 'shenhe';
                        $isresyes = @$result["o"] == 'yes' || @$result['payment_no'];
                    }
                    else {
                        $isresyes = $isresshenhe = false; //提现失败
                    }
                    if ($isresyes || $isresshenhe) { //提现成功
                        Db::commit();
                        //更新提现日记
                        $withdrawData = [];
                        if (isset($result["ismyshenhe"])) {
                            $withdrawData['status'] = 0;//状态 0申请提现 1处理成功 2处理错误
                            $withdrawData['result'] = '等待拆包审核';
                        }
                        else {
                            $withdrawData['status'] = 1;
                            $withdrawData['pay_order_no'] = $result['payment_no'] ?? '';
                        }
                        if (redBag::withdrawlogUpdate(['withdraw_id' => $withdrawId], $withdrawData)) { //更新提现日记
                            LowerLevel::commission($userinfo['id'], $bag['pay_amount']);//佣金分成
                        }
                        return $this->ok(['shenhe' => $isresshenhe, 'list' => $list, 'amount' => $amount]);

                    }
                    else {
                        Db::rollBack();
                        return $this->fail("jf_fail", $result['msg'] ?? "暂时无法提现");
                    }
                }else{
                    Db::commit();
                    return $this->ok(['shenhe' => false, 'list' => $list, 'amount' => 0]);
                }
            }
            else {
                Db::rollBack();
                return $this->fail("fail_order", "订单有误");

            }

        }
        else {
            return $this->fail("not_pay", "没红包可拆");
        }

    }

    public function withdraw() //佣金打钱
    {
        $userinfo = $this->auth->getUserinfo();
        $isTixianShenhe = config("isTixianShenhe");//是否开启审核
        $tixianRate = (float)config("tixianRate"); //费率
        $minTixian = (float)config("minTixian"); //最小提现金额
        $balance = (float)$userinfo['balance'];//你的钱
        if (!$userinfo['jfopenid']) {
            return $this->fail("tixin_fail", "提现id有误");
        }
        if ($balance < 1) {
            return $this->fail("balance_fail", "余额不足");
        }
        if ($balance > 800) {
            $balance = 800; //一次最多提800
        }

        if ($balance < $minTixian) {
            return $this->fail("balance_fail", "提现金额最小" . $minTixian . "元");
        }
        if ($tixianRate <= 0 || $tixianRate > 1) {
            return $this->fail("tixianRate_fail", "没正确设置费率错误");
        }
        if (!$isTixianShenhe) {
            $commissionSum = LowerLevel::commissionSum($userinfo['id']); //佣金总和 1:佣金 2：工资 3:系统赠送
            $withdrawSum = redBag::withdrawSum($userinfo['id'], [2, 3, 4]);//佣金提现总和 1:红包 2:佣金 3：工资 4:系统赠送
            $withdrawSum += $balance;
            if ($withdrawSum > $commissionSum) {
                $isTixianShenhe = true;
            }
        }
        //进入提现环节
        //俊飞给钱他
        $tixianid = mt_rand(10000000, 99999999999);//提现ID

        $money = bcmul($balance, bcsub(1, $tixianRate, 2), 2);
        if ($money < 1) {
            return $this->fail("money_fail", "提现不能小于1元");
        }
        Db::startTrans();
        $isgive = Db('user')->where(['id' => $userinfo["id"]])->setDec('balance', $balance);
        if (!$isgive) {
            Db::rollBack();
            return $this->fail("money_fail", "用户错误");
        }
        //写入提现日记
        $withdrawData = [
            'user_id'   => $userinfo['id'],
            'amount'    => $balance,
            'type'      => 2,//佣金
            'tixian_id' => $tixianid,
            'status'    => 1,
            'result'    => '佣金-工资'
        ];
        $withdrawId = redBag::withdrawlog($withdrawData);//写提现日记
        if (!$withdrawId) {
            Db::rollBack();
            return $this->fail("withdraw_fail", "提现错误");
        }

        $zstop_cn = new zstop_cn();
        $result = $zstop_cn->commit($userinfo['jfopenid'], $money, $tixianid, $userinfo["id"] . "|佣金", false, $isTixianShenhe);
        if ($result) {
            $isresshenhe = @$result["o"] == 'shenhe';
            $isresyes = @$result["o"] == 'yes' || @$result['payment_no'];
        }
        else {
            $isresyes = $isresshenhe = false; //提现失败
        }
        if ($isresyes || $isresshenhe) { //提现成功
            Db::commit();
            //更新提现日记
            $withdrawData = [];
            if (isset($result["ismyshenhe"])) {
                $withdrawData['status'] = 0;//状态 0申请提现 1处理成功 2处理错误
                $withdrawData['result'] = '等待佣金审核';
            }
            else {
                $withdrawData['status'] = 1;
                $withdrawData['pay_order_no'] = $result['payment_no'] ?? '';
            }
            redBag::withdrawlogUpdate(['withdraw_id' => $withdrawId], $withdrawData);//更新提现日记
            return $this->ok(['shenhe' => $isresshenhe, 'amount' => $balance]);
        }
        else {
            Db::rollBack();
            return $this->fail("jf_fail", $result['msg'] ?? "暂时无法提现");
        }

    }

    public function reward($isPrintReward = false)
    {

        $userinfo = $this->auth->getUserinfo();
        $type2Data = LowerLevel::commissionSum($userinfo['id'], 2, 'today', true);
        $type2Data = $type2Data->value("commission_id");
        if ($type2Data && !$isPrintReward) {
            return $this->fail("now_reward", "你今日的超级奖励已领取了");
        }
        $commissionSum = LowerLevel::commissionSum($userinfo['id'], 1, 'today');
        $howReward = LowerLevel::reward($commissionSum);
        if ($isPrintReward) {
            return [$type2Data ? 0 : $howReward, $commissionSum];
        }
        if ($howReward === false) {
            return $this->fail("adminset_fail", "管理员设置错误");
        }
        if ($howReward <= 0) {
            return $this->fail("not_reward", "你的佣金还不足奖励标准");
        }
        Db::startTrans();
        $hasWrite = LowerLevel::writeCommission([
                                                    'user_id'        => $userinfo['user_id'],
                                                    'origin_user_id' => $userinfo['origin_user_id'],
                                                    'from_user_id'   => 0,
                                                    'amount'         => $howReward,
                                                    'type'           => 2,
                                                    'from_reward'    => $commissionSum
                                                ]);
        if ($hasWrite) {
            Db::commit();
            return $this->ok(['amount' => $howReward, 'from_reward' => $commissionSum]);
        }
        else {
            Db::rollBack();
            return $this->fail("writeCommission_fail", "系统出错");
        }

    }
}