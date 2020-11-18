<?php


namespace AdminLogin\Login;

require_once 'aop/AopCertClient.php';
require_once 'aop/request/AlipayTradeAppPayRequest.php';
require_once 'aop/request/AlipayTradeWapPayRequest.php';
require_once 'aop/request/AlipayFundTransUniTransferRequest.php';
require_once 'aop/request/AlipayTradeRefundRequest.php';
class AliPayController extends BaseController
{
    private $appCertPath;
    private $alipayCertPath;
    private $rootCertPath;
    private $appId;
    private $rsaPrivateKey;
    private $body;
    private $subject;
    private $total_amount;
    private $out_trade_no;
    private $format='json';
    private $charset='UTF-8';
    private $signType='RSA2';
    private $notify_url;
    private $order_title;
    private $identity;
    private $name;
    private $passback_params;
    private $quit_url;
    private $ali_order_no;
    private $remark;
    public function __construct($par)
    {
        //$this->appCertPath=dirname(__FILE__).'/../../public/Cert/appCertPublicKey.crt';//原证书名称appCertPublicKey_2021001192692558.crt
        $this->appCertPath=config('pay.AliPay.common.appCertPath');//原证书名称appCertPublicKey_2021001192692558.crt
        //$this->alipayCertPath=dirname(__FILE__).'/../../public/Cert/alipayCertPublicKey_RSA2.crt';//
        $this->alipayCertPath=config('pay.AliPay.common.alipayCertPath');//
        //$this->rootCertPath=dirname(__FILE__).'/../../public/Cert/alipayRootCert.crt';//
        $this->rootCertPath=config('pay.AliPay.common.rootCertPath');//
        $this->appId=config('pay.AliPay.common.app_id');
        $this->rsaPrivateKey=config('pay.AliPay.common.rsaPrivateKey');//无空格，无换行，一行字符串
        $this->subject=$par['subject'];//标题
        $this->body=$par['body']??'商品付款';//描述
        $this->total_amount=$par['total_amount'];//金额
        $this->out_trade_no=$par['out_trade_no'];//订单号
        $this->notify_url=$par['notify_url'];//回调地址参数
        $this->order_title=$par['order_title'];//转账标题 单笔转账参数
        $this->identity=$par['identity'];//支付宝账号  单笔转账参数
        $this->name=$par['name'];//真实姓名  单笔转账参数
        $this->passback_params=$par['passback_params'];//额外参数 支付宝H5支付参数
        $this->quit_url=$par['quit_url']??'';//用户付款中途退出返回商户网站的地址 支付宝H5支付参数
        //$this->ali_order_no=$par['ali_order_no'];//用户下单 支付宝返回的订单号 退款参数
        $this->remark=$par['remark'];//备注
    }
    //app支付
    public function alipayTradeAppPay(){
        $aop=$this->commonParameter();
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"{$this->body}\","
            . "\"subject\": \"{$this->subject}\","
            . "\"out_trade_no\": \"{$this->out_trade_no}\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"{$this->total_amount}\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        //$request->setNotifyUrl("商户外网可以访问的异步地址");
        $request->setBizContent($bizcontent);
        $response = $aop->sdkExecute($request);//这里和普通的接口调用不同，使用的是sdkExecute
        return $response ;//就是orderString 可以直接给客户端请求，无需再做处理。
    }
    //单笔转账（swoole4.5以上）
    public function alipayFundTransUniTransfer(){
        $aop=$this->commonParameter();
        $request = new \AlipayFundTransUniTransferRequest;
        $request->setBizContent("{" .
            "\"out_biz_no\":\"{$this->out_trade_no}\"," .
            "\"trans_amount\":{$this->total_amount}," .
            "\"product_code\":\"TRANS_ACCOUNT_NO_PWD\"," .
            "\"biz_scene\":\"DIRECT_TRANSFER\"," .
            "\"order_title\":\"{$this->order_title}\"," .
            "\"payee_info\":{" .
            "\"identity\":\"{$this->identity}\"," .
            "\"identity_type\":\"ALIPAY_LOGON_ID\"," .
            "\"name\":\"{$this->name}\"" .
            "},".
            "\"remark\":\"{$this->remark}\"".
            "}");
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        return $resultCode;//code=10000为成功
    }
    //H5支付
    public function alipayTradeWapPay(){
        $aop=$this->commonParameter();
        $request = new \AlipayTradeWapPayRequest ();
        $content['body'] = $this->body;//对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body
        $content['subject'] = $this->subject;//商品的标题/交易标题/订单标题/订单关键字等
        $content['out_trade_no'] = $this->out_trade_no;//商户网站唯一订单号
        $content['timeout_express'] = '90m';//该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。注：若为空，则默认为15d。
        $content['total_amount'] = $this->total_amount;//订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]
        $content['quit_url'] = $this->quit_url;//用户付款中途退出返回商户网站的地址
        $content['passback_params'] =$this->passback_params?urlencode($this->passback_params):'';//额外参数，回调原样返回
        $content['product_code'] = 'QUICK_WAP_WAY';//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        $con = json_encode($content);
        $request->setBizContent($con);
        $request->setNotifyUrl($this->notify_url);
        $result = $aop->pageExecute ($request);
        return $result;
    }
    //退款
    public function alipayTradeRefund(){
        $aop=$this->commonParameter();
        $request = new \AlipayTradeRefundRequest ();
        $request->setBizContent("{" .
            "\"out_trade_no\":\"{$this->out_trade_no}\"," .
            "\"refund_amount\":{$this->total_amount}," .
            "\"refund_reason\":\"{$this->remark}\"" .
            "  }");
        $result = $aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        return  $resultCode;//code=10000为成功
    }

    public function commonParameter(){
        $aop = new \AopCertClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey=$this->rsaPrivateKey;//应用私钥
        $aop->format = $this->format;
        $aop->charset = $this->charset;
        $aop->signType = $this->signType;
        $aop->notify_url = $this->notify_url;
        $aop->alipayrsaPublicKey=$aop->getPublicKey($this->alipayCertPath);
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $aop->isCheckAlipayPublicCert = true;//是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
        $aop->appCertSN = $aop->getCertSN($this->appCertPath);//调用getCertSN获取证书序列号
        $aop->alipayRootCertSN = $aop->getRootCertSN($this->rootCertPath);//调用getRootCertSN获取支付宝根证书序列号
        return $aop;
    }

    //支付宝回调验签
    public function checkSign($request,$resp){
        $aop=new \AopCertClient();
        $r = iconv($this->charset, $this->charset . "//IGNORE", $resp);
        $signData = null;

        if ("json" == $this->format) {
            $respObject = json_decode($r);
            if (null !== $respObject) {
                $respWellFormed = true;
                $signData = $aop->parserJSONSignData($request, $resp, $respObject);
            }
        }
        $aop->checkResponseSign($request, $signData, $resp, $respObject);
    }
}