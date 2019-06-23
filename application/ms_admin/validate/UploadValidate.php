<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\ms_admin\validate;

use think\Db;
use think\Validate;

class UploadValidate extends Validate
{
    protected $rule = [
        'action|上传图片参数' => 'require|alpha|in:config,uploadimage,uploadscrawl,uploadvideo,uploadfile,listimage,listfile,catchimage'
    ];

    protected $message = [

    ];

    protected $scene = [
        'conUpload' => ['action']
    ];

}