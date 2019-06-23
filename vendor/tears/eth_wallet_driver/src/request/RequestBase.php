<?php

namespace eth\driver\request;

use eth\driver\exception\EthWalletDriverException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class RequestBase
 * @package eth\driver\request
 */
class RequestBase
{

    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var array
     */
    protected $params = [];
    /**
     * @var string
     */
    protected $request_method = 'GET';
    /**
     * @var string
     */
    protected $uri = '';

    /**
     * RequestBase constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $k
     * @param $v
     */
    public function setConfig($k, $v)
    {
        $this->config[$k] = $v;
    }

    /**
     * @param $k
     * @return mixed
     */
    public function getParams($k)
    {
        return array_key_exists($k, $this->params) ? $this->params[$k] : false;
    }

    /**
     * @param $k
     * @param $v
     */
    public function setParams($k, $v)
    {
        $this->params[$k] = $v;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->request_method;
    }

    /**
     * @param string $request_method
     */
    public function setRequestMethod($request_method)
    {
        $this->request_method = $request_method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }


    /**
     * @param string $uri
     * @param string $request_method
     * @param array $data
     * @param array $config
     * @return bool
     * @throws EthWalletDriverException
     */
    public function request($uri = '', $request_method = '', $data = [], $config = [])
    {
        $config = empty($config) ? $this->config : $config;
        $uri = empty($uri) ? $this->uri : $uri;
        $request_method = empty($request_method) ? $this->request_method : $request_method;
        $request_method = strtoupper($request_method) == 'POST' ? 'POST' : 'GET';
        $data = empty($data) ? $this->params : $data;
        $request_data = empty($data) ? ['json' => []] : ['json' => $data];
        $client = new Client(['base_uri' => $config['api_url']]);
        if (defined('ETH_WALLET_DRIVER_REQUEST_DEBUG')) {
            echo "request head:";
            var_dump($request_method . ': ' . $config['api_url'] . $uri);
            echo "request body:";
            var_dump(json_encode($request_data));
        }
        try {
            $response = $client->request($request_method, $uri, $request_data);
        } catch (GuzzleException $e) {
            throw new EthWalletDriverException("request error: " . $e->getMessage(), []);
        }
        $body = $response->getBody();
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            throw new EthWalletDriverException("response code ($statusCode) not eq 200 ", $response);
        }
        $content = $body->getContents();
        if (defined('ETH_WALLET_DRIVER_REQUEST_DEBUG')) {
            echo "response code:";
            var_dump($statusCode);
            echo "response content:";
            var_dump($content);
        }
        return json_decode($content, true);
    }
}