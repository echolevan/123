<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/13
 * Time: 4:07 PM
 */

namespace app\ms_admin\model;


class YejiLogModel extends BaseModel
{
    protected $name = 'log_eth_yeji';

    public function getByWhere($where, $offset, $limit, $table = '')
    {
        $db = $this;
        if (!empty($table)) {
            $db = $this->name($table);
        }
        return $db->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

}