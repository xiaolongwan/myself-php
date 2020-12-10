<?php


namespace AdminLogin\Pay;


class BaseController
{
    public  $SSLCERT_PATH;
    public  $SSLKEY_PATH;
    public function __construct()
    {
        $this->SSLCERT_PATH=config('pay.WeChatPay.WxPay.cert_path');
        $this->SSLKEY_PATH=config('pay.WeChatPay.WxPay.key_path');
    }

    public function getSign($tmpArr){
        ksort($tmpArr);
        $buff = "";
        foreach ($tmpArr as $k => $v) {
            if($v){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        $stringSignTemp = $buff . "&key=".config('pay.WeChatPay.WxPay.key');
        $sign = strtoupper(md5($stringSignTemp)); //签名
        return $sign;
    }
    public function getXml($tmpArr){
        $xml='<xml>';
        foreach ($tmpArr as $k=>$v){
            if($v){
                $xml.='<'.$k.'>'.$v.'</'.$k.'>';
            }
        }
        $xml.='</xml>';
        return $xml;
    }
    public function postRequest($posturl,$xml,$cert=false){
        $ch = curl_init($posturl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($cert==true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            //默认格式为PEM，可以注释
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, config('pay.WeChatPay.WxPay.cert_path'));
            //默认格式为PEM，可以注释
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY,config('pay.WeChatPay.WxPay.key_path'));
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        var_dump($response);
        curl_close($ch);
        return json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
    public function createIp(){
        $ip = $_SERVER;
        if(empty($ip['SSH_CLIENT'])){
            $spbill_create_ip = '116.62.168.232';
        }else{
            $spbill_create_ip = strstr($ip['SSH_CLIENT'], ' ', true);//获取ip
        }
        return $spbill_create_ip;
    }

    public function resultRequest($postUrl,$tmpArr,$cert=false){
        $tmpArr['sign']=$this->getSign($tmpArr);// 签名逻辑官网有说明，签名步骤就不解释了
        $xml=$this->getXml($tmpArr);
        $result =$this->postRequest($postUrl,$xml,$cert);
        if($tmpArr['trade_type']=='APP'&&!isset($result['err_code'])){//app支付重新组装数据交给前端调起支付
            $result=[
                'appid'=>$result['appid'],
                'partnerid'=>$result['mch_id'],
                'prepayid'=>$result['prepay_id'],
                'package'=>'Sign=WXPay',
                'noncestr'=>md5(time() . mt_rand(0, 1000)),
                'timestamp'=>time()
            ];
            $result['sign']=$this->getSign($result);
        }
        if($tmpArr['trade_type']=='JSAPI'&&!isset($result['err_code'])){//app支付重新组装数据交给前端调起支付
            $result=[
                'appId'=>$result['appid'],
                'signType'=>'MD5',
                'package'=>'prepay_id='.$result['prepay_id'],
                'nonceStr'=>md5(time() . mt_rand(0, 1000)),
                'timeStamp'=>time()
            ];
            $result['paySign']=$this->getSign($result);
        }
        return $result;
    }
}