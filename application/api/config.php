<?php
//配置文件
return [
    //区块链配置
//    'coin_config'   => [
//        'api_url'            => \think\Env::get('dabi.api_url'),
//        'main_address'       => \think\Env::get('dabi.main_address'),
//        'msth_token_address' => \think\Env::get('dabi.msth_token_address'),
//        'mstm_token_address' => \think\Env::get('dabi.mstm_token_address')
//    ],

    //云信配置
//    'yx_config'     => [
//        'appkey'    => \think\Env::get('yunxin.appKey'),
//        'appsecret' => \think\Env::get('yunxin.appSecret')
//    ],

    //登录时生成钱包地址以及云信注册的启用或关闭 0启用 1关闭
    'status_config' => [
        'coin_status' => 0,
        'yx_status'   => 0
    ],

    //阿里云oss
    'aliyun_oss'    => [
        'KeyId'     => \think\Env::get('oss.KeyId'),
        'KeySecret' => \think\Env::get('oss.KeySecret'),
        'Endpoint'  => \think\Env::get('oss.Endpoint'),
        'Bucket'    => \think\Env::get('oss.Bucket')
    ],

    //保留小数几位
    'wei_shu'       => 4,

    //登录过期时间
    'login_time'    => 0,

    //客服好友
    'ke_fu_member'  => 'kf0001'
];