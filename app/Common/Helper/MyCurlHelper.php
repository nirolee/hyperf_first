<?php
declare(strict_types=1);

namespace App\Common\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Utils\Arr;

class MyCurlHelper
{
    /**
     * @Inject()
     * @var HandlerStackFactory
     */
    private HandlerStackFactory $factory;

    /**
     * describe: 封装接口返回数据
     * author: derick
     * date: 2019/12/10
     * @param String $str
     * @return array
     */
    private function parseResponseJsonData(string $str): array
    {
        if (empty($str)) {
            return $str;
        }
        $data = @json_decode($str, true);
        if (json_last_error()) {
            return [];
        }
        return $data;
    }

    /**
     * describe: 获取curl客户端
     * author: 张旭之
     * date: 2020/7/23
     * @return Client
     */
    private function getCurlClient(): Client {
        $stack = $this->factory->create([
            'min_connections' => intval(env('MIN_CURL_CONNECTION_POOL', 1)),
            'max_connections' => intval(env('MAX_CURL_CONNECTION_POOL', 30))
        ]);
        return make(Client::class, [
            'config' => [
                'handler' => $stack,
            ],
        ]);
    }

    /**
     * describe: 实际请求发送方法
     * author: derick
     * date: 2019/12/10
     * @param String $method 请求方式
     * @param String $url 请求地址
     * @param array $requestData 请求参数
     * @param array $headers 请求连接信息
     * @return array|String
     */
    private function request(string $method, string $url, Array $requestData = [], Array $headers = [])
    {
        if (in_array(strtolower($method), ['get', 'delete']) && !empty($requestData)) {
            $url .= '?' . Arr::query($requestData);
        }
        return $this->_request($method, $url, $requestData, $headers);
    }

    /**
     * describe: 封装最后请求参数, 便于日志记录
     * author: derick
     * date: 2019/12/20
     * @param String $method 请求方式
     * @param String $url 请求地址
     * @param array $requestData 请求参数
     * @param array $headers 请求连接信息
     * @return array|string
     */
    private function _request(string $method, string $url, Array $requestData = [], Array $headers = [])
    {
        $client = $this->getCurlClient();
        try {
            $_requestData = [];
            $method = strtoupper($method);
            if ($requestData) {
                switch ($method) {
                    case 'POST':
                    case 'PUT':
                        $_requestData['form_params'] = $requestData;
                        $_requestData['json'] = $requestData;
                        break;
                    default:
                        $url .= Arr::query($requestData);
                        break;
                }
            }
            if ($headers) {
                $_requestData['headers'] = $headers;
            }
            $response = $client->request($method, $url, $_requestData);
            $content = $response->getBody()->getContents();
            $contentType = $response->getHeaderLine('Content-Type');
            if (strpos($contentType, 'text/html') !== false) {
                return $content;
            } elseif (strpos($contentType, 'application/json') !== false) {
                return $this->parseResponseJsonData($content);
            } else {
                return $content;
            }
        } catch (GuzzleException $e) {
            return $e->getMessage();
        }
    }

    /**
     * describe: 发送post请求
     * author: derick
     * date: 2019/12/10
     * @param String $url 请求地址
     * @param array $postData 请求post数据
     * @param array $headers 请求头信息
     * @return Array|string
     */
    public function post(string $url, Array $postData = [], Array $headers = [])
    {
        return $this->request('post', $url, $postData, $headers);
    }

    /**
     * describe: 发送get请求
     * author: derick
     * date: 2019/12/10
     * @param String $url 请求地址
     * @param array $queryData 请求get数据
     * @param array $headers 请求头信息
     * @return Array|string
     */
    public function get(string $url, Array $queryData = [], Array $headers = [])
    {
        return $this->request('get', $url, $queryData, $headers);
    }

    /**
     * describe: 发送put请求
     * author: derick
     * date: 2019/12/18
     * @param String $url 接口地址
     * @param array $queryData 发送请求
     * @return Array|string
     */
    public function put(string $url, Array $queryData = [])
    {
        return $this->request('put', $url, $queryData);
    }

    /**
     * describe: 发送delete请求
     * author: derick
     * date: 2020/1/3
     * @param string $url 请求地址
     * @param array $queryData 请求参数
     * @return Array|string
     */
    public function delete(string $url, Array $queryData = [])
    {
        return $this->request('delete', $url, $queryData);
    }
}