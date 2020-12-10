<?php

    require_once(BASE_PATH . '/app/Sdk/tim/TimRestApi.php');

    function usersignLoad($param,$sdkappid,$key1)
	{
		$id = trim($param['id']);
		$key = "usersign:".$sdkappid.":".$id;

//		$redis = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Redis::class);
//		$usersign = $redis->get($key);
		$redis = new \App\Sdk\RedisPackage();
		//$redis->rm($key);
		$usersign = $redis->get($key);
		if (!$usersign) {
			$root = array();
			if ($id == ''){
				$root['error'] = "参数id不能为空";
				$root['status'] = 0;
			}else{
//				$api = createRestAPI();
//				$api->init($sdkappid, $identifier);
//
//				$signature = get_signature();
				$expiry_after = 86400 * 30;//30天有效期
				$getuser = new \App\Sdk\TLSSigAPIv2($sdkappid,$key1);
				$ret = $getuser->genUserSig((string)$id,$expiry_after);
				//$ret = $api->generate_user_sig((string)$id, $expiry_after, $private_pem_path, $signature);
				if($ret == null){
					$root['error'] = $sdkappid.":获取usrsig失败";
					$root['status'] = 0;
				}else{
					$root['usersign'] = $ret;
					$root['status'] = 1;

					//$GLOBALS['redis']->set($key,$root,$expiry_after - 60);
					$usersign = serialize($root);
					$redis->set($key,$usersign,$expiry_after - 60);
					//$redis->expire($key, $expiry_after - 60);

					//$expiry_after = NOW_TIME + 86400;
					//$GLOBALS['db']->query("update ".DB_PREFIX."user set usersig = '".$ret[0]."',expiry_after=".$expiry_after." where id = '".$id."'");
				}
			}
		}
		return unserialize($usersign);
	}
	/**
	 * sdkappid 是app的sdkappid
	 * identifier 是用户帐号
	 * private_pem_path 为私钥在本地位置
	 * server_name 是服务类型
	 * command 是具体命令
	 */
	 
	function createTimAPI($sdkappid,$identifier,$key1){

		$ret = usersignLoad(array("id"=>$identifier),$sdkappid,$key1);

        //dump($identifier);exit;
		if ($ret['status'] == 1){
//			$private_pem_path = BASE_PATH ."/app/Sdk/tim/private_key";
//			if (!file_exists($private_pem_path)&&function_exists('log_err_file')) {
//				log_err_file(array(__FILE__,__LINE__,__METHOD__,'app/Sdk/tim/private_key,不存在'));
//			}
			$api = createRestAPI();
			$api->init($sdkappid, $identifier);
			$api->set_user_sig($ret['usersign']);

			return $api;
		}else{
			return $ret;
		}
	}
	
	/*
	* signature为获取私钥脚本，详情请见 账号登录集成 http://avc.qcloud.com/wiki2.0/im/
	*/
	function get_signature(){
		if(is_64bit()){
			if(PATH_SEPARATOR==':'){
				$signature = "signature/linux-signature64";
			}else{
				$signature = "signature\\windows-signature64.exe";
			}
		}else{
			if(PATH_SEPARATOR==':')
			{
				$signature = "signature/linux-signature32";
			}else{
				$signature = "signature\\windows-signature32.exe";
			}
		}
		return BASE_PATH ."/app/Sdk/tim/".$signature;
	}

?>
