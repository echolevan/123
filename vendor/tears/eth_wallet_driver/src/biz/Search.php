<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/7/17
 * Time: 下午5:57
 */

namespace eth\driver\biz;


use eth\driver\EthDriverResponse;

/**
 * 查询业务类
 * Class Search
 * @package eth\driver\biz
 */
class Search extends BizBase
{
    /**
     * 获取系统运行状态
     * @return mixed
     */
    public function info()
    {
        $result = $this->getRequest(__CLASS__, __FUNCTION__);
        $resp = EthDriverResponse::buildResponse($result);
        if ($resp->getError() || empty($resp->getData())) {
            return false;
        } else {
            return $resp->getData();
        }
    }

    /**
     * 查询指定txid的交易情况
     * @param $txid
     * @return mixed
     */
    public function transactionReceipt($txid)
    {
        $result = $this->getRequest(__CLASS__, __FUNCTION__, ['txid' => $txid]);
        $resp = EthDriverResponse::buildResponse($result);
        if ($resp->getError() || empty($resp->getData())) {
            return false;
        } else {
            return $resp->getData();
        }

    }
}