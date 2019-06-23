<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/7/17
 * Time: 下午4:17
 */

namespace eth\driver;


/**
 * Class EthDriverResponse
 * @package eth\driver
 */
class EthDriverResponse
{
    /**
     * @var
     */
    /**
     * @var
     */
    /**
     * @var
     */
    protected $data, $msg, $code;

    /**
     * EthDriverResponse constructor.
     * @param $data
     * @param $msg
     * @param $code
     */
    public function __construct($data, $msg, $code)
    {
        $this->data = $data;
        $this->msg = $msg;
        $this->code = $code;
    }


    /**
     * @param $result
     * @return EthDriverResponse
     */
    public static function buildResponse($result)
    {
        $code = -999;
        $msg = '返回参数错误!';
        $data = [];
        if (isset($result['code']) && isset($result['msg'])) {
            $data = isset($result['data']) ? $result['data'] : [];
            $code = $result['code'];
            $msg = $result['msg'];
        }
        return new self($data, $msg, $code);
    }

    /**
     * @return array|bool
     */
    public function getError()
    {
        if ($this->code == 0) {
            return false;
        } else {
            return ['code' => $this->code, 'msg' => $this->msg];
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

}