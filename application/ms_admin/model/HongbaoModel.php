<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/15
 * Time: 3:47 PM
 */

namespace app\ms_admin\model;


class HongbaoModel extends BaseModel
{
    protected $name = 'hongbao';

    public function getByWhere($where, $offset, $limit,$order="a.op_time desc")
    {
        return $this->field('a.*,b.memberid')->alias('a')->join('user b',
            'a.userid =b.id')->where($where)->limit($offset, $limit)->order($order)->select();
    }
}