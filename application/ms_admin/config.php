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

    // 模板参数替换
    'view_replace_str'       => array(
        '__CSS__'    => '/static/admin/css',
        '__JS__'     => '/static/admin/js',
        '__IMG__' => '/static/admin/images',
    ),

    // 管理员状态
    'user_status' => [
        '1' => '正常',
        '2' => '禁用'
    ],
    // 角色状态
    'role_status' => [
        '1' => '启用',
        '2' => '禁用'
    ],

    // 多库测试
    'local' => [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => '127.0.0.1',
        // 数据库名
        'database'    => 'test',
        // 数据库用户名
        'username'    => 'root',
        // 数据库密码
        'password'    => 'root',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],

    //编辑器上传图片配置
    'uploader' => [
        "imageActionName" => "uploadimage",
        "imageFieldName" => "upfile",
        "imageMaxSize" => 2048000,
        "imageAllowFiles" => [".png", ".jpg", ".jpeg", ".gif", ".bmp"],
        "imageCompressEnable" => true,
        "imageCompressBorder" => 1600,
        "imageInsertAlign" => "none",
        "imageUrlPrefix" => "",
        "imagePathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}",
        "scrawlActionName" => "uploadscrawl", /* 执行上传涂鸦的action名称 */
        "scrawlFieldName" => "upfile", /* 提交的图片表单名称 */
        "scrawlPathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "scrawlMaxSize" => 2048000, /* 上传大小限制，单位B */
        "scrawlUrlPrefix" => "", /* 图片访问路径前缀 */
        "scrawlInsertAlign" => "none",
        "snapscreenActionName" => "uploadimage", /* 执行上传截图的action名称 */
        "snapscreenPathFormat" => "/static/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "snapscreenUrlPrefix" => "", /* 图片访问路径前缀 */
        "snapscreenInsertAlign" => "none", /* 插入的图片浮动方式 */
        "catcherLocalDomain" => ["127.0.0.1", "localhost", "img.baidu.com"],
        "catcherActionName" => "catchimage", /* 执行抓取远程图片的action名称 */
        "catcherFieldName" => "source", /* 提交的图片列表表单名称 */
        "catcherPathFormat" => "/static/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "catcherUrlPrefix" => "", /* 图片访问路径前缀 */
        "catcherMaxSize" => 2048000, /* 上传大小限制，单位B */
        "catcherAllowFiles" => [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */
        "videoActionName" => "uploadvideo", /* 执行上传视频的action名称 */
        "videoFieldName" => "upfile", /* 提交的视频表单名称 */
        "videoPathFormat" => "/upload/video/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "videoUrlPrefix" => "", /* 视频访问路径前缀 */
        "videoMaxSize" => 102400000, /* 上传大小限制，单位B，默认100MB */
        "videoAllowFiles" => [
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], /* 上传视频格式显示 */
        "fileActionName" => "uploadfile", /* controller里,执行上传视频的action名称 */
        "fileFieldName" => "upfile", /* 提交的文件表单名称 */
        "filePathFormat" => "/upload/file/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "fileUrlPrefix" => "", /* 文件访问路径前缀 */
        "fileMaxSize" => 51200000, /* 上传大小限制，单位B，默认50MB */
        "fileAllowFiles" => [
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ], /* 上传文件格式显示 */
        "imageManagerActionName" => "listimage", /* 执行图片管理的action名称 */
        "imageManagerListPath" => "/upload/image/", /* 指定要列出图片的目录 */
        "imageManagerListSize" => 20, /* 每次列出文件数量 */
        "imageManagerUrlPrefix" => "", /* 图片访问路径前缀 */
        "imageManagerInsertAlign" => "none", /* 插入的图片浮动方式 */
        "imageManagerAllowFiles" => [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */
        "fileManagerActionName" => "listfile", /* 执行文件管理的action名称 */
        "fileManagerListPath" => "/upload/file/", /* 指定要列出文件的目录 */
        "fileManagerUrlPrefix" => "", /* 文件访问路径前缀 */
        "fileManagerListSize" => 20, /* 每次列出文件数量 */
        "fileManagerAllowFiles" => [
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ] /* 列出的文件类型 */
    ]
];
