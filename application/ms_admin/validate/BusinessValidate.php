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

class BusinessValidate extends Validate
{
    protected $rule = [
        'pageSize|页码'               => 'require|number|gt:0',
        'pageNumber|页数'             => 'require|number|gt:0',
        'searchText|搜索名称'           => 'chsAlphaNum|verStrFun:',
        'sh_business_id|商家ID'       => 'require|number|gt:0|checkShBusiness:',
        'dj_business_id|商家ID'       => 'require|number|gt:0|checkDjBusiness:',
        'dj_memo|冻结原因'              => 'require|chsAlphaNum|verStrFun:',
        'member_id|商家会员编号'          => 'length:6,10|alphaNum|checkPassword:|verStrFun:',
        'name|商家类型名称'               => 'require|chs|verStrFun:|checkTypeName:',
        'type_id|商家类型ID'            => 'require|number|gt:0|checkBusinessType:',
        'type_name|商家类型名称'          => 'require|chs|verStrFun:|checkBusinessTypeName:',
        'order_no|商家订单编号'           => 'alphaDash|length:36|verStrFun:',
        'pay_member_id|付款会员编号'      => 'length:6,10|alphaNum|checkPassword:|verStrFun:',
        'business_member_id|商家会员编号' => 'length:6,10|alphaNum|checkPassword:|verStrFun:',
        'order_status|商家订单状态'       => 'in:未支付,已支付|chs|verStrFun:',
        'pay_status|商家订单支付状态'       => 'in:-1,0,1|number|verStrFun:',
        'start|开始时间'                => 'dateFormat:Y-m-d',
        'end|结束时间'                  => 'dateFormat:Y-m-d',
        'account_type|账户类型'         => 'in:冻结账户,可用账户|chs|verStrFun:',
        'sort|类型排序'                 => 'require|number|gt:0|checkNum:'
    ];

    protected $message = [

    ];

    protected $scene = [
        'index'       => ['pageSize', 'pageNumber', 'searchText', 'member_id', 'order_no', 'pay_member_id', 'business_member_id', 'order_status', 'pay_status', 'start', 'end', 'account_type'],
        'toExamine'   => ['sh_business_id'],
        'frozen'      => ['dj_business_id', 'dj_memo'],
        'typeAdd'     => ['name', 'sort'],
        'typeEdit'    => ['type_id', 'type_name', 'sort'],
        'typeStart'   => ['type_id'],
        'typeStop'    => ['type_id'],
        'orderExport' => ['order_no', 'pay_member_id', 'business_member_id', 'order_status', 'pay_status', 'start', 'end'],
        'logExport'   => ['member_id', 'start', 'end', 'account_type']
    ];

    /**
     * 验证审核商家信息
     * @throws \Exception
     */
    protected function checkShBusiness($sh_business_id)
    {
        $business = Db::name('business')->field('id,memberid')->where('id', 'eq', $sh_business_id)->where('status', 'neq', '已审')->find();
        if (empty($business)) {
            return '您要审核的商家信息不存在或状态不正确';
        }
        $member = Db::name('user')->field('id')->where('memberid', 'eq', $business['memberid'])->where('status', 'eq', '已审')->where('is_business', 'neq', '是')->find();
        if (empty($member)) {
            return '您要审核的商家会员信息不存在或状态不正确';
        }

        return true;
    }

    /**
     * 验证冻结商家信息
     * @throws \Exception
     */
    protected function checkDjBusiness($dj_business_id)
    {
        $business = Db::name('business')->field('id,memberid')->where('id', 'eq', $dj_business_id)->where('status', 'eq', '已审')->find();
        if (empty($business)) {
            return '您要冻结的商家信息不存在或状态不正确';
        }
        $member = Db::name('user')->field('id')->where('memberid', 'eq', $business['memberid'])->where('status', 'eq', '已审')->where('is_business', 'eq', '是')->find();
        if (empty($member)) {
            return '您要冻结的商家会员信息不存在或状态不正确';
        }

        return true;
    }

    /**
     * 验证新增商家类型名称
     */
    protected function checkTypeName($name)
    {
        try {
            $type = Db::name('business_type')->field('id')->where('name', 'eq', $name)->find();
            if (!empty($type)) {
                return '商家类型名称已存在，请更换后再次操作';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 验证编辑商家类型
     */
    protected function checkBusinessType($type_id)
    {
        try {
            $type = Db::name('business_type')->field('id')->where('id', 'eq', $type_id)->find();
            if (empty($type)) {
                return '编辑商家类型不存在';
            }
            $business = Db::name('business')->field('id')->where('type_id', 'eq', $type_id)->find();
            if (!empty($business)) {
                return '此商家类型已绑定商家无法进行编辑';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 验证编辑商家类型名称
     */
    protected function checkBusinessTypeName($type_name, $rule, $data)
    {
        try {
            $type = Db::name('business_type')->field('id')->where('id', 'neq', $data['type_id'])->where('name', 'eq', $type_name)->find();
            if (!empty($type)) {
                return '商家类型名称已存在，请更换后再次操作';
            }
            $business = Db::name('business')->field('id')->where('type_id', 'eq', $data['type_id'])->find();
            if (!empty($business)) {
                return '此商家类型已绑定商家无法进行编辑';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 验证字符是否存在特殊字符
     * @param $str
     * @return bool
     */
    protected function verStrFun($str)
    {
        $str = strtolower($str);
        //正则匹配
        $ver_regular = true;
        $_arr_dangerRegs = [
            "/<(script|frame|iframe|bgsound|link|object|applet|embed|blink|style|layer|ilayer|base|meta)\s+\S*>/i",
            "/on(afterprint|beforeprint|beforeunload|error|haschange|load|message|offline|online|pagehide|pageshow|popstate|redo|resize|storage|undo|unload|blur|change|contextmenu|focus|formchange|forminput|input|invalid|reset|select|submit|keydown|keypress|keyup|click|dblclick|drag|dragend|dragenter|dragleave|dragover|dragstart|drop|mousedown|mousemove|mouseout|mouseover|mouseup|mousewheel|scroll|abort|canplay|canplaythrough|durationchange|emptied|ended|error|loadeddata|loadedmetadata|loadstart|pause|play|playing|progress|ratechange|readystatechange|seeked|seeking|stalled|suspend|timeupdate|volumechange|waiting)\s*=\s*(\"|')?\S*(\"|')?/i",
            "/\w+\s*=\s*(\"|')?(java|vb)script:\S*(\"|')?/i",
            "/(document|location)\s*\.\s*\S*/i",
            "/(eval|alert|prompt|msgbox)\s*\(.*\)/i",
            "/expression\s*:\s*\S*/i",
            "/show\s+(databases|tables|index|columns)/i",
            "/create\s+(database|table|(unique\s+)?index|view|procedure|proc)/i",
            "/alter\s+(database|table)/i",
            "/drop\s+(database|table|index|view|column)/i",
            "/backup\s+(database|log)/i",
            "/truncate\s+table/i",
            "/replace\s+view/i",
            "/(add|change)\s+column/i",
            "/(select|update|delete)\s+\S*\s+from/i",
            "/insert\s+into/i",
            "/load_file\s*\(.*\)/i",
            "/(outfile|infile)\s+(\"|')?\S*(\"|')/i",
        ];
        foreach ($_arr_dangerRegs as $item) {
            if (preg_match($item, $str)) {
                $ver_regular = false;
                break;
            }
        }
        //字符串验证
        $ver_string = true;
        $str_ver_arr = ["%20", "%27", "%2527", "*", '"', ";", "<", ">", "{", "}", "and", "execute", "update", "count", "chr", "mid", "master", "truncate", "char", "declare", "select", "create", "delete", "insert", " ", "'", "or", "=", "\'", "\/\*", "\.\.\/", "\.\/", "union", "and", "order", "or", "into", "load_file", "outfile"];
        foreach ($str_ver_arr as $value) {
            if (stripos($str, $value) !== false) {
                $ver_string = false;
                break;
            }
        }
        if (!$ver_regular || !$ver_string) {
            return '存在特殊字符，请修改后再次操作！';
        }

        return true;
    }

    /**
     * 验证英文加数字
     * @param $password
     * @return bool
     */
    protected function checkPassword($password)
    {
        $arr = str_split($password);
        $num = $alpha = false;
        foreach ($arr as $item) {
            if (is_numeric($item)) {
                $num = true;
                break;
            }
        }
        foreach ($arr as $item) {
            if (ctype_alpha($item)) {
                $alpha = true;
                break;
            }
        }
        if ($num && $alpha) {
            return true;
        }

        return false;
    }

    /**
     * 验证正整数
     */
    protected function checkNum($num)
    {
        if (!preg_match('/^[1-9][0-9]*$/', $num)) {
            return '数量必须为正整数！';
        }

        return true;
    }

}