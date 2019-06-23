<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/7/17
 * Time: 下午1:12
 */

namespace eth\driver\exception;


/**
 * 异常处理
 * Class EthWalletDriverException
 * @package eth\driver\exception
 */
class EthWalletDriverException extends \Exception
{
    /**
     * @var
     */
    protected $data;

    /**
     * EthWalletDriverException constructor.
     * @param $msg
     * @param $data
     */
    public function __construct($msg, $data)
    {
        $this->data = $data;
        parent::__construct($msg);
    }

}