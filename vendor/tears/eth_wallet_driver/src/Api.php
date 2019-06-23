<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/7/17
 * Time: 下午5:11
 */

namespace eth\driver;


use eth\driver\biz\Account;
use eth\driver\biz\Search;
use eth\driver\biz\Transaction;

/**
 * 基类
 * Class Api
 * @package eth\driver
 */
class Api
{
    /**
     * 初始化查询类操作
     * @param array $option 配置参数
     * @return Search
     */
    public static function getSearch($option)
    {
        return new Search($option);
    }

    /**
     * 初始化账户类操作
     * @param array $option 配置参数
     * @return Account
     */
    public static function getAccount($option)
    {
        return new Account($option);
    }

    /**
     * 初始化交易类操作
     * @param array $option 配置参数
     * @return Transaction
     */
    public static function getTransaction($option)
    {
        return new Transaction($option);
    }
}