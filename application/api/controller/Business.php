<?php

namespace app\api\controller;

use Endroid\QrCode\QrCode;
use function GuzzleHttp\Psr7\str;
use think\console\Input;
use think\Controller;
use think\Db;
use think\Log;
use think\Request;

class Business extends Common
{

    /**
     * 商家限制
     */
    public function _initialize()
    {
        parent::_initialize();
        if (!empty($this->sys[1008007])) {
            $ver_arr = explode('|', $this->sys[1008007]);
            if (!empty($this->sys[1008007]) && !empty($ver_arr) && !in_array($this->app_member['memberid'], $ver_arr)) {
                show_ajax(arr_msg(-1, '', '惠民商家功能关闭，请稍后再试！'));
            }
        }
    }


    /**
     * 商家列表
     * @return \think\response\Json
     */
    public function businessList()
    {
        try {
            $business = Db::name('business')->field('id,longitude,latitude,memberid,business_name,content,address,address_info,mobile,type_name')->where('status', 'eq', '已审')->select();
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['business' => $business], 'success'));
    }

    /**
     * 商家信息
     * @return \think\response\Json
     */
    public function businessDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.businessDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $business = Db::name('business')->field('id,longitude,latitude,memberid,business_name,content,address,address_info,mobile,type_name')->where('status', 'eq', '已审')->where('id', 'eq', intval($param['business_id']))->select();
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['business' => $business], 'success'));
    }

    /**
     * 查询是否是商家
     * @return \think\response\Json
     * @throws \Exception
     */
    public function isBusiness()
    {
        if ($this->sys[1008003] != '开启') {
            return json(arr_msg(-1, '', '惠民商家功能关闭，请稍后再试！'));
        }
        $business = Db::name('business')->where('memberid', 'eq', $this->app_member['memberid'])->find();
        if ($business['status'] == '未审') {
            return json(arr_msg(-1, '', '您已经审请商家了，请耐心等待后台审核！'));
        }
        if ($this->app_member['is_business'] == '是' && $business['status'] == '已审') {
            return json(arr_msg(-2, '', '您已经成为商家'));
        }
        if ($this->app_member['is_business'] == '冻结' && $business['status'] == '冻结') {
            return json(arr_msg(-3, '', '您的商家信息已经被冻结了'));
        }

        return json(arr_msg(1, '', '您还未成为商家'));
    }

    /**
     * 申请商家初始化
     * @return \think\response\Json
     */
    public function becomeBusinessInit()
    {
        try {
            $member_rec = Db::name('user')->where('memberid', 'eq', $this->app_member['memberid'])->where('is_business', 'eq', '否')->find();
            $business = Db::name('business')->where('memberid', 'eq', $this->app_member['memberid'])->find();
            if (empty($member_rec) || !empty($business)) {
                return json(arr_msg(-1, '', '您已存在商家信息，请不要重复申请'));
            }
            $business_type = Db::name('business_type')->field('id,name')->where('status', 'eq', '启用')->order('sort asc')->select()->each(function ($model) {
                $model['text'] = $model['name'];

                return $model;
            });
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['business_type' => $business_type], 'success'));
    }

    /**
     * 申请商家提交
     * @return \think\response\Json
     */
    public function becomeBusiness()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.becomeBusiness');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $type_name = Db::name('business_type')->where('id', 'eq', $param['business_type_id'])->where('status', 'eq', '启用')->value('name');
        $data = [
            'memberid'      => $this->app_member['memberid'],
            'membername'    => $this->app_member['membername'],
            'mobile'        => $param['business_mobile'],
            'business_name' => $param['business_name'],
            'content'       => $param['business_content'],
            'type_id'       => $param['business_type_id'],
            'type_name'     => $type_name,
            'address'       => $param['business_address'],
            'address_info'  => $param['business_address_info'],
            'longitude'     => $param['business_long'],
            'latitude'      => $param['business_lat'],
            'memo'          => $param['business_memo'],
            'status'        => '未审',
        ];
        $rec = Db::name('business')->insert($data);
        if (!$rec) {
            return json(arr_msg(-1, '', '申请商家失败'));
        }

        return json(arr_msg(1, '', '申请成功，请等待审核！'));
    }

    /**
     * 商家信息编辑初始化
     * @return \think\response\Json
     */
    public function upBusinessInit()
    {
        if ($this->sys['1008003'] != '开启') {
            return json(arr_msg(-1, '', '此功能暂未开启，请稍后再试'));
        }
        if ($this->app_member['is_business'] != '是') {
            return json(arr_msg(-1, '', '您还未成为商家或您的商家被冻结'));
        }
        try {
            $business = Db::name('business')->field('id,memberid,mobile,business_name,content,type_id,type_name,address,address_info,longitude,latitude,memo')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已审')->find();
            if (empty($business)) {
                return json(arr_msg(-1, '', '您的商家信息被冻结或不存在'));
            }
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['business' => $business], 'success'));
    }

    /**
     * 商家信息编辑提交
     * @return \think\response\Json
     */
    public function upBusiness()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.upBusiness');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $data = [
            'mobile'        => $param['up_mobile'],
            'business_name' => $param['up_name'],
            'content'       => $param['up_content']
        ];
        try {
            $rec = Db::name('business')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已审')->update($data);
            if (!$rec) {
                return json(arr_msg(-1, '', '编辑商家信息失败'));
            }
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, '', '编辑商家信息成功'));
    }

    /**
     * 【商家】商家收款二维码
     * @return \think\response\Json
     * @throws \Exception
     */
    public function businessQrcode()
    {
        $ver_business = $this->verBusiness();
        if ($ver_business['code'] != 1) {
            return json(arr_msg(-1, '', $ver_business['msg']));
        }
        $width = input("width", 300, 'int');
        $text = urldecode($this->app_member['memberid']);
        if (!$text) {
            return json(arr_msg(-1, '', '未获取到商家信息！'));
        }
        $root_path = ROOT_PATH . 'public';
        $path = '/static/business/qrcode';
        if (!file_exists(($root_path . $path))) {
            mkdir(($root_path . $path));
        }
        $request = Request::instance();
        $file_name = md5($this->app_member['memberid'] . 'business') . '.png';
        $file_path = $request->domain() . $path . '/' . $file_name;
        if (!file_exists($file_path)) {
            $qr_code = new QrCode($text);
            $qr_code->setSize($width);
            $qr_code->writeFile($root_path . $path . '/' . $file_name);
        }
        $business = Db::name('business')->field('id,memberid,mobile,business_name,content,type_id,type_name,address,address_info,longitude,latitude,memo')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已审')->find();

        return json(arr_msg(1, ['qr_code' => $file_path, 'business_name' => $business['business_name']], 'success'));
    }

    /**
     * 付款商家初始化
     * @return \think\response\Json
     */
    public function payBusinessInit()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.payBusinessInit');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $business = Db::name('business')->where('memberid', 'eq', $param['business_member_id'])->where('status', 'eq', '已审')->find();

            return json(arr_msg(1, ['business_member_id' => $param['business_member_id'], 'business_name' => $business['business_name'], 'msth_tiqu' => format_num($this->app_member['lian_num_keyong'])], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 付款提交验证接口
     * @return \think\response\Json
     */
    public function checkBusinessSub()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.checkBusinessSub');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        return json(arr_msg(1, '', 'success'));
    }

    /**
     * 付款商家提交
     * @return \think\response\Json
     */
    public function payBusinessSub()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.payBusinessSub');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $data = [
            'business_memberid' => $param['business_member_id'],
            'pay_memberid'      => $this->app_member['memberid'],
            'pay_type'          => $param['pay_type'],
            'pay_num'           => $param['pay_num'],
            'pay_memo'          => $param['pay_memo']
        ];
        $procedure_name = 'p_business_pay';
        $result = db_query($procedure_name, $data);
        if (1 != $result['code']) {
            Log::info('时间：' . date('Y-m-d H:i:s') . '-会员账号：' . $this->app_member['memberid'] . '-请求参数：' . json_encode($data) . '-p_business_pay存储过程返回:' . $result['msg']);
            return json($result);
        }

        return json(arr_msg(1, '', '支付成功'));
    }

    /**
     * 我的店铺
     */
    public function myShop()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.myShop');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $ver_business = $this->verBusiness();
        if ($ver_business['code'] != 1) {
            return json(arr_msg(-1, '', $ver_business['msg']));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::name('business_order')->where('business_memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已支付')->where('pay_status', 'eq', 1)->count('id');
            $order = Db::name('business_order')->field('id,business_yuqi_num,lrdate')->where('business_memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已支付')->where('pay_status', 'eq', 1)->order('id desc')->limit($offset, $limit)->select()->each(function ($model) {
                $model['lrdate'] = date('Y-m-d', strtotime($model['lrdate']));
                $model['business_yuqi_num'] = $model['business_yuqi_num'] >= 0 ? '+' . format_num($model['business_yuqi_num']) : format_num($model['business_yuqi_num']);

                return $model;
            });
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['list' => $order, 'pages' => ceil($count / $limit), 'no_jie_suan' => format_num($this->app_member['business_yuqi_shouyi_dongjie']), 'ke_yong_ye_ji' => format_num($this->app_member['business_yuqi_shouyi_keyong'])], 'success'));
    }

    /**
     * 明细详情
     */
    public function shopDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.shopDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $order = Db::name('business_order')->field('id,num,ping_tai_yeji_num,business_sfdate_begin,memberid,order_no,business_yuqi_num,lrdate')->where('business_memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已支付')->where('pay_status', 'eq', 1)->where('id', 'eq', $param['shop_order_id'])->find();
            $order['num'] = format_num($order['num']);
            $order['ping_tai_yeji_num'] = format_num($order['ping_tai_yeji_num']);
            $order['business_yuqi_num'] = format_num($order['business_yuqi_num']);
            $order['business_sfdate_begin'] = date('Y-m-d', strtotime($order['business_sfdate_begin']));
            $order['leixing'] = '商家收款';
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['list' => $order], 'success'));
    }

    /**
     * 验证商家[私有]
     */
    private function verBusiness()
    {
        if ($this->sys['1008003'] != '开启') {
            return arr_msg(-1, '', '此功能暂未开启，请稍后再试');
        }
        if ($this->app_member['is_business'] != '是') {
            return arr_msg(-1, '', '您还未成为商家或您的商家被冻结');
        }
        try {
            $business = Db::name('business')->field('id,memberid,mobile,business_name,content,type_id,type_name,address,address_info,longitude,latitude,memo')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已审')->find();
            if (empty($business)) {
                return arr_msg(-1, '', '您的商家信息被冻结或不存在');
            }
        } catch (\Exception $e) {
            return arr_msg(-1, '', $e->getMessage());
        }

        return arr_msg(1, '', 'success');
    }
}
