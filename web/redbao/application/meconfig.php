<?php

return [
    'jumphost'        => 'http://k.cross.echosite.cn', //跳转域名
    'domain'          => 'http://k.cross.echosite.cn',   //游戏域名
    'wx_config'       => [ //微信wx
                           'appid'     => 'wxc16cd343807bb7ba',
                           'appsecret' => '563c202e6ded6cb330d9d75ab9b001e3',
    ],
    'livepay_token'   => [ //爱支付
                           'app_id'  => '2019183',
                           'api_key' => 'IYjzZWlkiMZOWosmizrZbhfLOnRozMVu',
    ],
    'superpay_token'  => [ //超极付
                           'app_id'  => '10108',
                           'api_key' => '8225d42baf98af00958822852179a50f',
    ],
    'cpay_token'      => [ //c+支付
                           'app_id'  => '11008',
                           'api_key' => '2339d1c8d7e24e457aebc30339d12d22dbeb5814',
    ],
    'swiftpay_token'  => [ //迅捷支付
                           'app_id'  => '10392',
                           'api_key' => 'pnsyJBl3WjUsFmEOHabKK4C5Om1zUCQSAB2ce3nJpH5WMQaWbWNOjG7GhCubfBZL',
    ],
    'fastpay_token'   => [//fast个人免签
                          'app_id'  => '16267_ec5399aea3632a58bc20cc037fc54a7d',
                          'api_key' => 'b6cc7ef7e608ed0111f664f9308eb3d2',
    ],
    'lightnpay_token' => [ //闪电支付
                           'app_id'  => '10053',
                           'api_key' => 'a313902ea8288ecc463426dcb1b3b60f1da0a297',
    ],

    'paytype'        => ['fast', 'lightn'],
    //当前允许的支付,两个时会随机 lightn闪电 swift迅捷   c C+支付  super超极  love爱支付  fast fastpay免签
    'isTixianShenhe' => false,//是否所有提现审核
    'tixianRate'     => 0.03,//佣金提现费率
    'minTixian'      => 2, //提现最小多少钱
    'reward'         => [
        50  => ['money' => '50', 'max' => 1], //满50送 money50，奖多少max次，0无限奖
        100 => ['money' => 100, 'max' => 9]
    ]
];