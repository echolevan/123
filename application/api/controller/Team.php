<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/14
 * Time: 3:00 PM
 */

namespace app\api\controller;


use think\Db;

class Team extends Common
{
    public function create()
    {
        $data['id'] = Db::raw("uuid()");
        $data['name'] = input('tname');
        $data['owner'] = input('owner');
        $data['tid'] = input('tid');
        if (empty($data['tid']) || empty($data['owner']) || empty($data['name'])) {
            return $this->quickResponse(null, -1, '参数错误');
        }
        try {
            Db::name('team')->insert($data);
        } catch (\Exception $e) {
            return $this->quickResponse(null, -1);
        }
        return $this->quickResponse();
    }

    public function remove()
    {
        $data['tid'] = input('tid');
        if (empty($data['tid'])) {
            return $this->quickResponse(null, -1, '参数错误');
        }
        try {
            Db::name('team')->where($data)->delete();
        } catch (\Exception $e) {
            return $this->quickResponse(null, -1);
        }

        //        }

        return $this->quickResponse();
    }

    public function changeOwner()
    {
        $data['tid'] = input('tid');
        $new_data['owner'] = input('newowner');
        if (empty($data['tid']) || empty($new_data['owner'])) {
            return $this->quickResponse(null, -1, '参数错误');
        }
        try {
            Db::name('team')->where($data)->update($new_data);
        } catch (\Exception $e) {
            return $this->quickResponse(null, -1);
        }
        return $this->quickResponse();
    }

    public function update()
    {
        $data['tid'] = input('tid');
        $new_data['name'] = input('tname');
        if (empty($data['tid']) || empty($new_data['name'])) {
            return $this->quickResponse(null, -1, '参数错误');
        }
        try {
            Db::name('team')->where($data)->update($new_data);
        } catch (\Exception $e) {
            return $this->quickResponse(null, -1);
        }

        //    }

        return $this->quickResponse();
    }

    private function quickResponse($array = [], $int = 1, $string = '操作成功!')
    {
        return json(arr_msg($int, $array, $string));
    }
}