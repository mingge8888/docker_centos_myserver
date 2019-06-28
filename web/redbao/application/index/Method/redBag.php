<?php
/**
 * Class redBag
 * 作者：岑明
 * 2019/6/18
 */

namespace app\index\Method;

use think\Db;

class redBag
{

    public static function getBag($where, $field = null, $ord = null)//订单与红包的数据
    {

        if (isset($where['h.hb_type_id']) && !$where['h.hb_type_id']) {
            unset($where['h.hb_type_id']);
        }
        if (isset($where['o.hb_type_id']) && !$where['o.hb_type_id']) {
            unset($where['o.hb_type_id']);
        }
        $where['o.pay_amount'] = ['>=', Db::raw("h.pay_amount")];
        $ret = Db('order o')->join("hbtype h", 'o.hb_type_id = h.hb_type_id')->where($where);
        if ($field) {
            $ret = $ret->field($field);
        }
        if ($ord) {
            $ret = $ret->order($ord);
        }
        return $ret->find();
    }

    public static function numeric_filter($a)
    {
        if (is_array($a) && $a) {
            $a = array_filter($a, function ($val) {
                return is_numeric(trim($val));
            });
            return array_map(function ($num) {
                return abs((int)trim($num));
            }, array_values($a));
        }
        return [];
    }

    private static function riskHandle($riskArray)
    {
        $i = 0;
        $risk = [];
        foreach ($riskArray as $item) {
            $itemArray = explode("-", $item);
            $itemArray = self::numeric_filter($itemArray);
            $itemArrayCount = count($itemArray);
            if ($itemArrayCount == 2 || $itemArrayCount == 3) {
                $percentage = (int)end($itemArray);
                if ($percentage < 1 || $percentage > 100) {
                    return false;
                }
                $percentage = (int)bcmul($percentage, 1000, 1);
                if ($itemArrayCount == 2) {//2
                    $num = (int)$itemArray[0];
                }
                else { //3
                    $itemArray[0] = (int)$itemArray[0];
                    $itemArray[1] = (int)$itemArray[1];
                    $min = min($itemArray[0], $itemArray[1]);
                    $max = max($itemArray[0], $itemArray[1]);
                    $num = mt_rand($min, $max);
                }
                $lt = $i + $percentage;
                array_push($risk, ['gt' => $i, 'lt' => $lt + 1, 'money' => $num]);
                $i = $lt;
            }
            else {
                return false; //管理员设置错误
            }

        }
        return ['scope' => $i, 'data' => $risk];
    }

    public static function openAmount($data, $is_list_rist = false) //打开红包，生成金额
    {

        $risk = $is_list_rist ? $data['list_risk'] : $data['risk'];
        $riskArray = explode("|", $risk);
        if (count($riskArray) < 2) {
            return false;//管理员设置错误
        }

        $data = self::riskHandle($riskArray);

        if (!is_array($data) || $data['scope'] < 2) {
            return false;
        }

        $decision = mt_rand(1, $data['scope']); //决定随机的数值

        $randData = $data['data'];
        foreach ($randData as $item) {
            if ($item['gt'] < $decision && $item['lt'] > $decision) {
                return $item['money'];
            }
        }
        return false;

    }

    public static function amountList($data, $n) //打开红包，生成金额
    {

        $list = [];
        for ($i = 0; $i < $n; $i++) {
            array_push($list, bcadd(self::openAmount($data, true), bcmul(mt_rand(0, 61), 0.01, 2), 2));
        }
        return $list;
    }

    public static function updataOrder($where, $update)
    {
        $update['updatetime'] = time();
        return Db('order')->where($where)->update($update);
    }

    public static function withdrawlog($update)
    {
        $update['createtime'] = $update['updatetime'] = time();
        return Db('withdraw')->insertGetId($update);

    }

    public static function withdrawlogUpdate($where, $update)
    {
        return Db('withdraw')->where($where)->update($update);
    }

    public static function withdrawSum(int $userid, $type = 0)
    {
        $where = ['user_id' => $userid];
        $ret = Db('withdraw')->where($where);
        if ($type) {
            if (is_array($type)) {
                $ret = $ret->where('type', 'in', $type);
            }
            else {
                $ret = $ret->where('type', (int)$type);
            }
        }
        return $ret->sum('amount');
    }
}