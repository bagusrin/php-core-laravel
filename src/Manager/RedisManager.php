<?php
namespace Bagusrin\CoreLaravel\Manager;

use Illuminate\Support\Facades\Redis;

class RedisManager {

    private $generalKey = [
      '_format', 'access_token', 'lang',
      'app_id', 'secret_key', 'api',
      'query_string', 'utm_campaign', 'utm_source',
      'utm_medium',
    ];

	private $defaultDB;
	private $redisDB;


	public function __construct(){

		$this->defaultDB = env('REDIS_DEFAULT_CACHE');
        
        $this->switchDB($this->defaultDB);
    }


    public function switchDB($db){

    	$this->redisDB = Redis::select($db);

    }

    public function get($key,$param = array()){

    	$this->redisDB;

        if (is_array($param)){
            $key .= $this->generateAdditionalKey($param);
        }

    	return json_decode(Redis::get($key), true);
    }

    public function set($key,$value,$expired){

    	$this->redisDB;
    	Redis::setex($key, $expired, json_encode($value));
    }

    public function del($key,$param = array()){

      $this->redisDB;

        if (is_array($param)){
            $key .= $this->generateAdditionalKey($param);
        }

        Redis::del($key);

        return true;

    }

    public function generateAdditionalKey(array $param = array()){
      $addKey = null;
      if (is_array($param)) {
          $addKey = '';
          foreach ($param as $keys => $value) {
              if (!in_array($keys, $this->generalKey)) {
                  $addKey .= '|' . $keys . '=' . $value;
              }
          }
      }

      return $addKey;
    }
	
}