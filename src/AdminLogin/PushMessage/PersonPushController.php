<?php


namespace App\Controller\pushMessage;
header("Content-Type: text/html; charset=utf-8");
require_once('IGt.Push.php');
require_once('IGt.Batch.php');

class PersonPushController
{
    private $app_key;
    private $app_id;
    private $MasterSecret;
    private $host;

    public function __construct()
    {
        $this->app_key = config('pushMessage.AppKey');
        $this->app_id = config('pushMessage.AppID');
        $this->MasterSecret = config('pushMessage.MasterSecret');
        $this->host = config('pushMessage.Host');
    }

    public static function createObject()
    {
        $object = new PersonPushController();
        return $object;
    }

    //单推接口案例
    public function pushMessageToSingle($data)
    {
        putenv("gexin_pushSingleBatch_needAsync=false");
        $igt = new \IGeTui($this->host, $this->app_key, $this->MasterSecret);
        $igt->connect();
        //消息模版：
        // NotificationTemplate：通知模板
        //$template = $this->IGtNotificationTemplateDemo($data);
        //定义"SingleMessage"
        $template = $this->getTemplate($data);
        $message = new \IGtSingleMessage();
        $message->set_isOffline($data['isOffline']);//是否离线
        $message->set_offlineExpireTime($data['offlineExpireTime']);//离线时间
        $message->set_data($template);//设置推送消息类型
        //$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，2为4G/3G/2G，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId($this->app_id);
        $target->set_clientId($data['cid']);
//    $target->set_alias(Alias);
        try {
            $rep = $igt->pushMessageToSingle($message, $target);
            var_dump($rep);
            echo("<br><br>");
        } catch (\RequestException $e) {
            $requstId = $e->getRequestId();
            //失败时重发
            $rep = $igt->pushMessageToSingle($message, $target, $requstId);
            var_dump($rep);
            echo("<br><br>");
        }
        return $rep;
    }

    public function pushMessageToSingleBatch($data)
    {
        putenv("gexin_pushSingleBatch_needAsync=false");
        $igt = new \IGeTui($this->host,$this->app_key,$this->MasterSecret);
        $batch = new \IGtBatch($this->app_key, $igt);
        $batch->setApiUrl($this->host);
        $igt->connect();
        //消息模版：
        // 1.TransmissionTemplate:透传功能模板
        // 2.LinkTemplate:通知打开链接功能模板
        // 3.NotificationTemplate：通知透传功能模板
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板

        foreach ($data as $k=>$v){
            $template=$this->getTemplate($v);
            $messageLink = new \IGtSingleMessage();
            $messageLink->set_isOffline($v['isOffline']);//是否离线
            $messageLink->set_offlineExpireTime($v['offlineExpireTime']);//离线时间
            $messageLink->set_data($template);//设置推送消息类型
            //$messageLink->set_PushNetWorkType(1);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送，在wifi条件下能充分帮用户节省流量

            $targetLink = new \IGtTarget();
            $targetLink->set_appId($this->app_id);
            $targetLink->set_clientId($v);
            $batch->add($messageLink, $targetLink);
        }
        try {
            $rep = $batch->submit();
            var_dump($rep);
            echo("<br><br>");
        }catch(\Exception $e){
            $rep=$batch->retry();
            var_dump($rep);
            echo ("<br><br>");
        }
        return $rep;
    }
    public function pushMessageToList($data){
        putenv("gexin_pushSingleBatch_needAsync=true");
        $igt = new \IGeTui($this->host,$this->app_key,$this->MasterSecret);
        //$igt->connect();
        //$igt = new IGeTui('',APPKEY,MASTERSECRET);//此方式可通过获取服务端地址列表判断最快域名后进行消息推送，每10分钟检查一次最快域名
        //消息模版：
        // NotificationTemplate：通知功能模板
        $template = $this->IGtNotificationTemplateDemo($data);

        //定义"ListMessage"信息体
        $message = new \IGtListMessage();
        $message->set_isOffline($data['isOffline']);//是否离线
        $message->set_offlineExpireTime($data['offlineExpireTime']);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType($data['PushNetWorkType']);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送，在wifi条件下能帮用户充分节省流量
        $contentId = $igt->getContentId($message);
        //接收方1
        $targetList=[];
        foreach ($data['cid'] as $k=>$v){
            $target1 = new \IGtTarget();
            $target1->set_appId($this->app_id);
            $target1->set_clientId($v);
            array_push($targetList,$target1);
        }

        //$target1->set_alias(Alias1);
        //接收方2
//        $target2 = new IGtTarget();
//        $target2->set_appId(APPID);
//        $target2->set_clientId(CID2);
//        //$target2->set_alias(Alias2);
//
//
//        $targetList[0] = $target1;
//        $targetList[1] = $target2;

        $rep = $igt->pushMessageToList($contentId, $targetList);
        var_dump($rep);
        echo ("<br><br>");
        return $rep;
    }

    public function getTemplate($data)
    {
        switch ($data['type']) {
            case 0:
                $template = $this->IGtTransmissionTemplateDemo($data);
                break;//【透传模板】自定义消息
            case 1:
                $template = $this->IGtNotificationTemplateDemo($data);
                break;//【通知模板】 打开应用首页
            case 2:
                $template = $this->IGtLinkTemplateDemo($data);
                break;//【通知模板】 打开浏览器网页
            case 3:
                $template = $this->IGtStartActivityTemplateDemo($data);
                break;//【通知模板】 打开应用内页面
        }
        return $template;
    }

    //【透传模板】自定义消息
    public function IGtTransmissionTemplateDemo($data)
    {
        $template = new \IGtTransmissionTemplate();
        //应用appid
        $template->set_appId($this->app_id);
        //应用appkey
        $template->set_appkey($this->app_key);
        //透传消息类型
        $template->set_transmissionType(1);//	收到消息是否立即启动应用，1为立即启动（不推荐使用，影响客户体验），2则广播等待客户端自启动
        //透传内容
        $template->set_transmissionContent("测试离线");
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //这是老方法，新方法参见iOS模板说明(PHP)*/
        //$template->set_pushInfo("actionLocKey","badge","message",
        //"sound","payload","locKey","locArgs","launchImage");
        return $template;
    }

    //【通知模板】 打开应用首页
    public function IGtNotificationTemplateDemo($data)
    {
        $template = new \IGtNotificationTemplate();
        $template->set_appId($this->app_id);                      //应用appid
        $template->set_appkey($this->app_key);                    //应用appkey
        $template->set_transmissionType($data['transmissionType']);               //透传消息类型
        $template->set_transmissionContent($data['transmissionContent']);   //透传内容
        $template->set_title($data['title']);                     //通知栏标题
        $template->set_text($data['text']);        //通知栏内容
        $template->set_logo($data['logo']);                  //通知栏logo
        $template->set_logoURL($data['logoURL']); //通知栏logo链接
        $template->set_isRing($data['isRing']);                      //是否响铃
        $template->set_isVibrate($data['isVibrate']);                   //是否震动
        $template->set_isClearable($data['isClearable']);
//        $template->set_channel("set_channel");
//        $template->set_channelName("set_channelName");
        $template->set_channelLevel(3);
        //$template->set_notifyId(12345678);
        return $template;
    }

    //【通知模板】 打开浏览器网页
    public function IGtLinkTemplateDemo($data)
    {
        $template = new \IGtLinkTemplate();
        $template->set_appId($this->app_id);                  //应用appid
        $template->set_appkey($this->app_key);                //应用appkey
        $template->set_title($data['title']);       //通知栏标题
        $template->set_text($data['text']);        //通知栏内容
        $template->set_logo($data['logo']);                       //通知栏logo
        $template->set_logoURL("");                    //通知栏logo链接
        $template->set_isRing($data['isRing']);                      //是否响铃
        $template->set_isVibrate($data['isVibrate']);                   //是否震动
        $template->set_isClearable($data['isClearable']);//通知栏是否可清除
        $template->set_url("http://www.igetui.com/"); //打开连接地址
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
//        $template->set_channel("set_channel");//通知渠道id，唯一标识，用户自定义
//        $template->set_channelName("set_channelName");//通知渠道名称，用户自定义
        //该字段代表通知渠道重要性，具体值有0、1、2、3、4；
        //设置之后不能修改；具体展示形式如下：
        //0：无声音，无震动，不显示。(不推荐)
        //1：无声音，无震动，锁屏不显示，通知栏中被折叠显示，导航栏无logo。
        //2：无声音，无震动，锁屏和通知栏中都显示，通知不唤醒屏幕。
        //3：有声音，有震动，锁屏和通知栏中都显示，通知唤醒屏幕。（推荐）
        //4：有声音，有震动，亮屏下通知悬浮展示，锁屏通知以默认形式展示且唤醒屏幕。（推荐）
        $template->set_channelLevel(3);
        //	在消息推送的时候设置notifyid。如果需要覆盖此条消息，则下次使用相同的notifyid发一条新的消息。客户端sdk会根据notifyid进行覆盖。详见消息覆盖
        $template->set_notifyId(12345678);
        return $template;
    }

    //【通知模板】 打开应用内页面
    public function IGtStartActivityTemplateDemo($data)
    {
        $template = new \IGtStartActivityTemplate();

        $template->set_appId($this->app_id);//应用appid
        $template->set_appkey($this->app_key);//应用appkey
        $template->set_intent("");//【Android】长度小于1000字节，通知带intent传递参数（以intent:开头，;end结尾）示例：intent:#Intent;component=你的包名/你要打开的 activity 全路径;S.parm1=value1;S.parm2=value2;end
        $template->set_title("个推");//通知栏标题
        $template->set_text("个推最新版点击下载");//通知栏内容
        $template->set_logo("");//通知栏logo
        $template->set_logoURL("http://*");
        $template->set_isRing($data['isRing']);                      //是否响铃
        $template->set_isVibrate($data['isVibrate']);                   //是否震动
        $template->set_isClearable($data['isClearable']);//通知栏是否可清除
        //$template->set_duration("XXXX-XX-XX XX:XX:XX","XXXX-XX-XX XX:XX:XX");
        //$smsMessage = new SmsMessage();//设置短信通知
        //$smsMessage->setPayload("1234");
        //$smsMessage->setUrl("http://www/getui");
        //$smsMessage->setSmsTemplateId("123456789");
        //$smsMessage->setOfflineSendtime(1000);
        //$smsMessage->setIsApplink(true);
        //$template->setSmsInfo($smsMessage);
        $template->set_notifyId(123456543);
        return $template;
    }

    //【通知模板】通知消息撤回
    public function getRevokeTemplateDemo()
    {
        $revoke = new \IGtRevokeTemplate();
        $revoke->set_appId("appid");
        $revoke->set_appkey("appkey");
        $revoke->set_oldTaskId("taskId");//	指定需要撤回消息对应的taskId
        $revoke->set_force(false);//默认false， 【Android】客户端没有找到对应的taskid，是否把对应appid下所有的通知都撤回
        return $revoke;
    }
}