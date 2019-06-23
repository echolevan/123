<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/7/17
 * Time: 下午5:16
 */

namespace eth\driver\biz;


/**
 * Class BizBase
 * @package eth\driver\biz
 */
class BizBase
{
    /**
     * @var
     */
    protected $option;
    /**
     * @var
     */
    protected $class;

    /**
     * BizBase constructor.
     * @param $option
     */
    public function __construct($option)
    {
        /*
         * $option's keys
         * api_url
         * main_address
         * token_address
         */
        if (!array_key_exists('api_url', $option)) {
            throw new \InvalidArgumentException('api_url not exists');
        }
        $this->option = $option;
    }

    /**
     * @param $c
     * @param $f
     * @param array $p
     * @return mixed
     */
    public function getRequest($c, $f, $p = [])
    {
        $c = str_replace('biz', 'request', $c);
        $req = new $c($this->option);
        return call_user_func_array([$req, $f], $p);
    }


}