<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/7/17
 * Time: 下午4:47
 */

namespace eth\driver\request;



/**
 * 账户请求类
 * Class Account
 * @package eth\driver\request
 */
class Account extends RequestBase
{

    /**
     * 生成新钱包地址
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function newAddress()
    {
        return $this->request('/eth/account/new', 'POST');
    }

    /**
     * 验证是否是本地地址
     * @param $address
     * @return bool
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function isLocal($address)
    {
        return $this->request('/eth/account/check_address/' . $address);
    }

    /**
     * 导出私钥
     * @param $address
     * @return bool
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function export($address)
    {
        return $this->request('/eth/account/export', 'POST', ['address' => $address]);
    }

    /**
     * 导入私钥
     * @param $private_key
     * @return bool
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function import($private_key)
    {
        return $this->request('/eth/account/import', 'POST', ['private_key' => $private_key]);
    }

    /**
     * 查询ETH余额
     * @param $address
     * @return bool
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function balance($address)
    {
        return $this->request('/eth/balances/' . $address);
    }

    /**
     * 查询代币余额
     * @param $address
     * @param $token_address
     * @return bool
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function balance_token($address,$token_address)
    {
        return $this->request("/eth/token/$token_address/balance/$address");
    }
}