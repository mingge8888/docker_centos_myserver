<?php

namespace app\index\Method;

use think\Db;

class LowerLevel
{

    public static function commisMoney(float $money, $level, $rate)
    {
        switch ($level) {
            case 1:
                $r = (float)$rate['first'];
                break;
            case 2:
                $r = (float)$rate['second'];
                break;
            case 3:
                $r = (float)$rate['third'];
                break;
            case 4:
                $r = (float)$rate['four'];
                break;
            case 5:
                $r = (float)$rate['five'];
                break;
            default:
                $r = 0;
        }
        return $r > 0 ? bcmul($money, $r, 2) : false;
    }

    public static function writeCommission($data)
    {

        $data['createtime'] = $data['updatetime'] = time();
        $res = Db('commission')->insert($data);
        if ($res) {
            return Db('user')->where(['id' => $data['user_id']])->setInc('balance', $data['amount']);

        }
        return false;
    }

    public static function commisUpdata($data, $rate, $from_user_id, $money)
    {

        $money = self::commisMoney($money, $data['level'], $rate);
        if ($money) {
            return self::writeCommission([
                                             'user_id'        => $data['parent_user_id'],
                                             'origin_user_id' => $data['origin_user_id'],
                                             'from_user_id'   => $from_user_id,
                                             'amount'         => $money,
                                             'type'           => 1
                                         ]);

        }
        return false;
    }

    public static function commission(int $userid, float $money)
    {
        $levelArr = $parentArr = [];

        if ($money <= 0 || !$userid) {
            return false;
        }
        $relations = Db('relation')->where('user_id', $userid)->field("parent_user_id,origin_user_id,level")
                                   ->order("level asc")
                                   ->limit(7)->select();
        $rate = Db('rate')->find();

        if ($relations && $rate) {
            Db::startTrans();
            foreach ($relations as $val) {
                $level = $val['level'];
                $parent_user_id = $val['parent_user_id'];
                if (!$parent_user_id || !$level || isset($levelArr[$level]) || isset($parentArr[$parent_user_id])) {
                    continue;
                }
                if (!self::commisUpdata($val, $rate, $userid, $money)) {
                    Db::rollback();
                    continue;
                }
                Db::commit();
                $parentArr[$parent_user_id] = $levelArr[$level] = 1;
            }
            return true;

        }
        return false;
    }

    public static function commissionSum(int $userid, $type = 0, $date = 0,$isReturnDb =false)
    {
        $where = ['user_id' => $userid];
        $ret = Db('commission')->where($where);
        if ($type) {
            if (is_array($type)) {
                $ret = $ret->where('type', 'in', $type);
            }
            else {
                $ret = $ret->where('type', (int)$type);
            }
        }
        if ($date) {
            $ret = $ret->whereTime('createtime', $date);
        }
        if($isReturnDb){
            return $ret;
        }
        return $ret->sum('amount');
    }

    private static function checkRewardConfig($rewardConfig)
    {
        $arr = [];
        foreach ($rewardConfig as $key => $item) {
            $key = trim($key);
            if (is_numeric($key)) {
                $item['money'] = trim(@$item['money']);
                $item['max'] = trim(@$item['max']);
                if (!is_numeric($item['money']) || !is_numeric($item['max'])) {
                    return false;
                }
                if ($item['money'] < 1 || $item['max'] < 0) {
                    return false;
                }
                $key = (float)$key;
                $item['money'] = (float)$item['money'];
                $item['max'] = (int)$item['max'];
                $arr[$key] = $item;
            }
            else {
                return false;
            }
        }
        return $arr;
    }

    public static function reward(float $money)
    {

        $rewardConfig = config("reward");
        if (!is_array($rewardConfig)) {
            return false;
        }
        $rewardConfig = self::checkRewardConfig($rewardConfig);
        if (!$rewardConfig) {
            return false;
        }
        $rewardConfigKey = array_keys($rewardConfig);
        rsort($rewardConfigKey);

        foreach ($rewardConfigKey as $key) {
            $item = $rewardConfig[$key];
            $m = (int)bcdiv($money, $key, 0);
            if ($m > 0) {
                if ($item['max'] > 0 && $m > $item['max']) {
                    $m = $item['max'];
                }
                return bcmul($m, $item['money'], 2);
            }

        }
        return 0;
    }

}