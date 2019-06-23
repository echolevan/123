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

class NewsValidate extends Validate
{
    protected $rule = [
        ['title', 'require', '文章标题不能为空'],
        ['account_type', 'require', '账户类型不能为空'],
        ['account_type', 'in:eth,wuc', '账户类型不正确'],
        ['total_amount', 'egt:0', '总赏金需要大于等于0'],
        ['surplus_amount', 'egt:0', '剩余赏金需要大于等于0'],
        ['every_read_amount', 'egt:0', '每次阅读赏金需要大于等于0'],
        ['content', 'require', '文章内容不能为空'],
    ];

}