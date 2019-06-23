<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/15
 * Time: 3:47 PM
 */

namespace app\ms_admin\model;


class HongbaoDataModel extends BaseModel
{
    protected $name = 'hongbao_data';

    public function getByWhere($where, $offset, $limit,$order='a.build_date desc')
    {
        return $this->field('a.*')->alias('a')->where($where)->limit($offset, $limit)->order($order)->select();
    }
}