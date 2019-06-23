<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/14
 * Time: 3:13 PM
 */

namespace app\ms_admin\model;


class TeamModel extends BaseModel
{
    protected $name = 'team';

    /**
     * 返回原有数据  不自动进行时间转换
     * @param $time
     * @return mixed
     */
    public function  formatDateTime($time, $format, $timestamp = false)
    {
        return $time;
    }

}