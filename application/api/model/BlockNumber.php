<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/10
 * Time: 9:37 AM
 */

namespace app\api\model;


use think\Db;

class BlockNumber
{

    public static function app_update_block_num($now_block_number = 0, $now_block_hash = '')
    {
        Db::query("call app_update_block_num(?,?)", [$now_block_number, $now_block_hash]);
    }

}