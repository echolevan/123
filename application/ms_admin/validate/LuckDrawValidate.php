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

class LuckDrawValidate extends Validate
{
    protected $rule = [
        ['start_time', 'require', '开始投注时间不能为空'],
        ['start_time', "dateFormat:" . DATE_TIME_FORMAT, '开始投注时间格式不正确'],
        ['start_time', 'after:' . DATE_TIME_NOW, '开始投注时间需要大于当前时间'],
        ['end_time', 'require', '停止投注时间不能为空'],
        ['end_time', "dateFormat:" . DATE_TIME_FORMAT, '停止投注时间格式不正确'],
        ['end_time', 'after:' . DATE_TIME_NOW, '停止投注时间需要大于当前时间'],
        ['stop_block_number', 'require', '停止投注区块号不能为空'],
        ['stop_block_number', 'number', '停止投注区块号只能是数字'],
        ['open_block_number', 'require', '开奖区块号不能为空'],
        ['open_block_number', 'number', '开奖区块号只能是数字'],
        ['account_type', 'require', '账户类型不能为空'],
        ['account_type', 'in:eth,wuc', '账户类型不正确'],

    ];

    protected $scene=['edit'=>['start_time','end_time','stop_block_number','open_block_number']];

}