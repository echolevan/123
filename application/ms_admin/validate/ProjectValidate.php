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

use think\Validate;

class ProjectValidate extends Validate
{
    protected $rule = [
        ['save_days', 'require', '存储天数不能为空'],
        ['save_days', 'number', '存储天数只能是数字'],
        ['rixi_bili', 'require', '日息比例不能为空'],
        ['rixi_bili', 'number', '日息比例只能是数字'],
        ['yuexi_bili', 'require', '月息比例不能为空'],
        ['yuexi_bili', 'number', '月息比例只能是数字'],
        ['min_num', 'require', '最小存储数量不能为空'],
        ['min_num', 'number', '最小存储数量只能是数字'],
        ['max_num', 'require', '最大存储数量不能为空'],
        ['max_num', 'number', '最大存储数量只能是数字'],
        ['zhiquweiyuejin', 'require', '提前支取违约金比例不能为空'],
        ['zhiquweiyuejin', 'number', '提前支取违约金比例只能是数字'],
        ['fenhong', 'require', '分红配置不能为空'],
//        ['account_type', 'require', '账户类型不能为空'],
//        ['account_type', 'in:eth,wuc', '账户类型不正确'],
        ['status', 'require', '状态不能为空'],
        ['status', 'in:0,-1,1', '状态不正确'],
        ['tiqianzhiqu', 'require', '提前支取不能为空'],
        ['tiqianzhiqu', 'in:0,-1,1', '提前支取不正确'],

    ];

    protected $scene=['edit'=>['save_days','rixi_bili','yuexi_bili','min_num','max_num','zhiquweiyuejin','fenhong','status','tiqianzhiqu']];

}