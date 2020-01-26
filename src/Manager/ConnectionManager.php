<?php

namespace Bagusrin\CoreLaravel\Manager;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;
use Bagusrin\CoreLaravel\Utils\JsonUtil;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

class ConnectionManager
{

    /* @var $baseUrl mixed */
    private $baseUrl;

    protected $allowMethod = ['GET', 'POST', 'PATCH', 'DELETE'];

    public function __construct($baseUrl = '')
    {
        $this->baseUrl = $baseUrl;
    }

    public function stream($url, $method = 'GET', array $_param = array(), array $header = array(), $toArray = false)
    {
        try {
            if (!in_array($method, $this->allowMethod))
                return false;

            $param = array();

            if (in_array($method, ['POST', 'PATCH'])) {
                $param = ['form_params' => $_param];
            } else {
                $param = ['query' => $_param];
            }

            if (count($header) > 0) {
                
                if(array_key_exists('content-type', $header)){
                    switch($header['content-type']){
                        case 'application/json':$param = ['json' => $_param];break;
                        case 'application/x-www-form-urlencoded':$param = ['form_params' => $_param];break;
                        case 'multipart/form-data':$param = ['multipart' => $_param ];unset($header['content-type']);break;
                        default:break;
                    }
                }
                
                if(array_key_exists('Content-Type', $header)){
                    switch($header['Content-Type']){
                        case 'application/json':$param = ['json' => $_param];break;
                        case 'application/x-www-form-urlencoded':$param = ['form_params' => $_param];break;
                        case 'multipart/form-data':$param = ['multipart' => $_param ];unset($header['Content-Type']);break;
                        default:break;
                    }
                }
                
                $param['headers'] = $header;

                // if(array_key_exists('Content-Type', $header)){
                //     if($header['Content-Type'] == "multipart/form-data"){

                //         $arrMultipart = [];
                //         foreach($header as $k => $v){
                //             if($k != 'Content-Type'){
                //                 $arrMultipart[$k] = $v; 
                //             }
                //         }

                //         $param['headers'] = $arrMultipart;   
                //     }
                // }

                // if(array_key_exists('content-type', $header)){
                //     if($header['content-type'] == "multipart/form-data"){

                //         $arrMultipart = [];
                //         foreach($header as $k => $v){
                //             if($k != 'content-type'){
                //                 $arrMultipart[$k] = $v; 
                //             }
                //         }

                //         $param['headers'] = $arrMultipart;   
                //     }
                // }
            }

            $param['query']['version'] = date('YmdGis').str_replace(' ', '', microtime());

            $client = new Client();
            $apiResponse = $client->request($method, $this->baseUrl . $url, $param);
            
            $content = $apiResponse->getBody();

            if ($apiResponse->getBody() instanceof Stream) {
                $content = $apiResponse->getBody()->getContents();
            }
            
            if ($toArray === true && JsonUtil::isJson($content)) {
                return array_merge(array("dt" => json_decode($content, true), "success" => true, "status" => 200));
            }

            $headers = [
                'Server' => 'Apache/2.4.9 (Unix) PHP/5.5.14 OpenSSL/0.9.8za',
                'X-Powered-By' => 'PHP/5.5.14',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Credentials' => true,
                'Cache-Control' => 'no-cache',
                'X-Debug-Token' => '959f63',
                'X-Debug-Token-Link' => '/_profiler/959f63',
                'Content-Type' => 'application/json',
            ];

            return response()
            		->json(json_decode($content, true), $apiResponse->getStatusCode())
            		->withHeaders($headers);

        
        } catch (BadResponseException $e) {

            $content = $e->getResponse()->getBody();
            if ($e->getResponse()->getBody() instanceof Stream) {
                $content = $e->getResponse()->getBody()->getContents();
            }
            if ($toArray === true && JsonUtil::isJson($content)) {
                return array_merge(array("dt" => json_decode($content, true), "success" => false, "status" => 400));
            }

            $headers = [
                'Server' => 'Apache/2.4.9 (Unix) PHP/5.5.14 OpenSSL/0.9.8za',
                'X-Powered-By' => 'PHP/5.5.14',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Credentials' => true,
                'Cache-Control' => 'no-cache',
                'X-Debug-Token' => '959f63',
                'X-Debug-Token-Link' => '/_profiler/959f63',
                'Content-Type' => 'application/json',
            ];

            return response()
                    ->json(json_decode($content, true), $apiResponse->getStatusCode())
                    ->withHeaders($headers);
        
        } catch (\Exception $e) {
            
            $response = false;

            if(env('APP_ENV') == 'local'){
                $response = ["success" => false, "errRequest" => Psr7\str($e->getRequest()), "errResponse" => Psr7\str($e->getResponse())];    
            }

            return $response;
        }
    }
}

?>