<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return [
    // 是否开启路由
    'url_route_on' => true,
    'trace' => [
        'type' => 'html', // 支持 socket trace file
    ],
    "exception_tmpl" => \think\Env::get('exception_tmpl', THINK_PATH . 'tpl' . DS . 'think_exception.tpl'),
    //各模块公用配置
    'extra_config_list' => ['database', 'route', 'validate'],
    //临时关闭日志写入
    'log' => [
        // 日志记录方式，支持 file socket
        'type' => 'File',
        //日志保存目录
        'path' => LOG_PATH,
        //单个日志文件的大小限制，超过后会自动记录到第二个文件
        'file_size' => 2097152,
        //日志的时间格式，默认是` c `
        'time_format' => 'c',
        // error和sql日志单独记录
        'apart_level' => ['info', 'sql', 'log'],
        'max_files' => 1200,
    ],
    // 应用调试模式
    'app_debug' => \think\Env::get('app_debug', false),
    // 应用Trace
    'app_trace' => \think\Env::get('app_trace', false),

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache' => [
        // 驱动方式
        'type' => 'redis',
        // 缓存保存目录
        'path' => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    //加密串
    'salt' => 'Wg%yZubkw$H0VFgB',
    //备份数据地址
    'back_path' => APP_PATH . '../back/',

    //数据过滤安全
    'default_filter' => 'stripslashes,htmlentities,htmlspecialchars,strip_tags,trim,addslashes',

    // 禁止访问模块
    'deny_module_list' => ['common'],

    //云信配置
    'yx_config' => [
        'appkey' => \think\Env::get('yunxin.appKey'),
        'appsecret' => \think\Env::get('yunxin.appSecret')
    ],

    //收打币配置
    'coin_config'   => [
        'api_url'            => \think\Env::get('dabi.api_url'),
        'main_address'       => \think\Env::get('dabi.main_address'),
        'msth_token_address' => \think\Env::get('dabi.msth_token_address'),
        'mstm_token_address' => \think\Env::get('dabi.mstm_token_address')
    ],
];
