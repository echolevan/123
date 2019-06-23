<?php

namespace eth\driver\request;


/**
 * 查询接口请求类
 * Class Search
 * @package eth\driver\request
 */
class Search extends RequestBase
{

    /**
     * 获取系统运行状态
     * @return bool
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function info()
    {
        return $this->request('/eth/last_hash');
    }


    /**
     * 查询指定txid的交易情况
     * @param $txid
     * @return bool
     * @throws \eth\driver\exception\EthWalletDriverException
     */
    public function transactionReceipt($txid)
    {
        return $this->request('/eth/transaction_receipt/' . $txid);

    }


}