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

class AccountValidate extends Validate
{
    protected $rule = [
        ['account_type', 'require', '账户类型不能为空'],
        ['account_type', 'in:eth,wuc', '账户类型不正确'],
        ['memberid', 'require', 'memberid不能为空'],
        ['number', 'require', '数量不能为空'],
        ['number', 'number', '数量只能是数字'],
    ];

}