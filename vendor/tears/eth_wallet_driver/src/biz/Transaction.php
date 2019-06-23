<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/7/18
 * Time: 上午9:16
 */

namespace eth\driver\biz;


use eth\driver\EthDriverResponse;

/**
 * 交易业务类
 * Class Transaction
 * @package eth\driver\biz
 */
class Transaction extends BizBase
{
    /**
     * 转出代币到指定地址
     * @param $to_address
     * @param $amount
     * @param string $from_address
     * @param string $token_address
     * @return bool
     */
    public function transfer_token($to_address, $amount, $from_address = '', $token_address = '')
    {
        $from_address = empty($from_address) ? empty($this->option['main_address']) ? '' : $this->option['main_address'] : $from_address;
        $token_address = empty($token_address) ? empty($this->option['token_address']) ? '' : $this->option['token_address'] : $token_address;
        $result = $this->getRequest(__CLASS__, __FUNCTION__, [
            'to_address' => $to_address,
            'amount' => (string)$amount,
            'from_address' => $from_address,
            'token_address' => $token_address
        ]);
        $resp = EthDriverResponse::buildResponse($result);
        if ($resp->getError() || !isset($resp->getData()['tx_hash'])) {
            return false;
        } else {
            return $resp->getData()['tx_hash'];
        }
    }

    /**
     * 转出ETH到指定地址
     * @param $to_address
     * @param $amount
     * @param string $from_address
     * @return bool
     */
    public function transfer($to_address, $amount, $from_address = '')
    {
        $from_address = empty($from_address) ? empty($this->option['main_address']) ? '' : $this->option['main_address'] : $from_address;
        $result = $this->getRequest(__CLASS__, __FUNCTION__, [
            'to_address' => $to_address,
            'amount' => (string)$amount,
            'from_address' => $from_address,
        ]);
        $resp = EthDriverResponse::buildResponse($result);
        if ($resp->getError() || !isset($resp->getData()['tx_hash'])) {
            return false;
        } else {
            return $resp->getData()['tx_hash'];
        }
    }

    /**
     * 获取转账预估手续费
     * @param $to_address
     * @return bool
     */
    public function suggest_gas($to_address)
    {
        $result = $this->getRequest(__CLASS__, __FUNCTION__, [
            'to_address' => $to_address
        ]);
        $resp = EthDriverResponse::buildResponse($result);
        if ($resp->getError() || !isset($resp->getData()['suggest_gas'])) {
            return false;
        } else {
            return $resp->getData()['suggest_gas'];
        }
    }
}