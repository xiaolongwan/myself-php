<?php


namespace AdminLogin\Pay;

class WeChatPayController extends BaseController
{
    private $appId;
    private $mchId;
    private $body;
    private $notify_url;
    private $out_trade_no;
    private $total_fee;
    private $attach;
    private $spbill_create_ip;
    private $nonce_str;
    private $trade_type;
    private $scene_url;
    private $openid;
    private $check_name='NO_CHECK';
    private $mchAppId;
    private $out_refund_no;
    private $refund_fee;
    private $desc;
    public function __construct($par)
    {
        $this->appId=config('pay.WeChatPay.WxPay.app_id');
        $this->mchId=config('pay.WeChatPay.WxPay.mch_id');
        $this->body=$par['body'];
        $this->notify_url=config('pay.WeChatPay.WxPay.notify_url')??$par['notify_url'];
        $this->out_trade_no=$par['out_trade_no'];
        $this->total_fee=($par['total_fee']??$par['amount'])*100;//支付参数 total_fee  打钱参数amount
        $this->attach=$par['attach']?json_encode($par['attach']):'';
        $this->spbill_create_ip=$this->createIp();
        $this->nonce_str=md5(time() . mt_rand(0, 1000));
        $this->trade_type=$par['trade_type'];//微信支付类型  MWEB，APP，JSAPI
        $this->scene_url=$par['scene_url'];//微信H5支付 场景地址
        $this->mchAppId=config('pay.WeChatPay.WxPay.app_id');//商户appid
//        $this->SSLCERT_PATH=env('SSLCERT_PATH');//配置证书
//        $this->SSLKEY_PATH=env('SSLKEY_PATH');//配置key
        $this->openid=$par['openid'];
        $this->out_refund_no=$par['out_refund_no'];//退款订单号
        $this->refund_fee=$par['refund_fee']*100;//退款金额
        $this->desc=$par['desc']??'企业付款';//打钱描述
    }

    public function WxPay(){
        $postUrl='https://api.mch.weixin.qq.com/pay/unifiedorder';
        $tmpArr = array(
            'appid' => $this->appId,//不要填成了 公众号原始id
            'mch_id' => $this->mchId,//商户号
            'body' => $this->body,
            'nonce_str' => $this->nonce_str,//随机字符串
            'notify_url' => $this->notify_url,//回调地址
            'out_trade_no' => $this->out_trade_no,
            'spbill_create_ip' => $this->spbill_create_ip        ,
            'total_fee' => $this->total_fee,
            'trade_type' => $this->trade_type,
            'attach'=>$this->attach//回调需要的参数
        );
        if($this->trade_type=='MWEB'){
            $tmpArr['scene_info']="{'h5_info': {'type'':'Wap','wap_url': '". $this->scene_url."','wap_name': 'h5pay'}}";
        }
        if($this->trade_type='JSAPI'){
            $tmpArr['openid']=$this->openid;
        }
        if($this->trade_type='APP'){
            $tmpArr['appid']=config('pay.WeChatPay.AppPay.app_id');
        }
        return $this->resultRequest($postUrl,$tmpArr);
    }
    //企业打钱
    public function WxPayTransfer(){
        $postUrl='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $tmpArr = array(
            'mch_appid' => $this->mchAppId,//商户appid
            'mchid' => $this->mchId,//商户号
            'nonce_str' => $this->nonce_str,//随机字符串
            'partner_trade_no' => $this->out_trade_no,//商户订单号
            'openid' => $this->openid,
            'check_name' => $this->check_name,//是否验证名字
            'spbill_create_ip' => $this->spbill_create_ip        ,
            'amount' => $this->total_fee,
            'desc'=>$this->desc//打钱备注
        );
        return $this->resultRequest($postUrl,$tmpArr,true);
    }
    //退款
    public function WxPayRefund(){
        $postUrl='https://api.mch.weixin.qq.com/secapi/pay/refund';
        $tmpArr = array(
            'appid' => $this->appId,//不要填成了 公众号原始id
            'mch_id' => $this->mchId,//商户号
            'nonce_str' => $this->nonce_str,//随机字符串
            'notify_url' => $this->notify_url,//回调地址
            'out_trade_no' => $this->out_trade_no,//要退款的商家订单
            'out_refund_no' => $this->out_refund_no,//退款订单号
            'total_fee' => $this->total_fee,
            'refund_fee' => $this->refund_fee,
        );
        return $this->resultRequest($postUrl,$tmpArr,true);
    }
    /**
    * 格式化参数格式化成url参数  生成签名sign
    * $data为微信返回数据
    */
    public function checkSign($data){
        $appwxpay_key = env('WxKey');
        //签名步骤一：按字典序排序参数
        ksort($data);
        $String = $this->callbackToUrlParams($data);
        //签名步骤二：在string后加入KEY
        if($appwxpay_key){
            $String = $String."&key=".$appwxpay_key;
        }
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }
    /**
     * 格式化参数格式化成url参数
     */
    public function callbackToUrlParams($Parameters){
        $buff = "";
        foreach ($Parameters as $k => $v){
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}