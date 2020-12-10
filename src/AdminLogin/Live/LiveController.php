<?php


namespace AdminLogin\Live;

require_once 'LiveService.php';
class LiveController
{
    private $push_domain;
    private $play_domain;
    private $domain_prefix;
    private $push_key;
    private $video_id;
    private $type;

    public function __construct($data)
    {
        $this->push_domain = config('live.push_domain');
        $this->play_domain = config('live.play_domain');
        $this->domain_prefix = config('live.domain_prefix');
        $this->push_key = config('live.push_key');
        $this->video_id = $data['video_id'];//直播间id  自己数据库的主键 惟一值
        $this->type = $data['type']??'rtmp';//播流类型
    }
    //获取推流,播流链接
    public function getLiveUrl(){
        $time   = date('Y-m-d H:i:s',strtotime('+1 day'));
        $ennumber=substr(md5($this->video_id),0,8);
        $streamName =  $this->domain_prefix.'_'.$ennumber;
        $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
        //txSecret = MD5( KEY + streamName + txTime )
        $txSecret = md5( $this->push_key . $streamName . $txTime);
        $ext_str = "?" . http_build_query(array(
                "txSecret" => $txSecret,
                "txTime" => $txTime,
                'video_id'=>$this->video_id
            ));
        $push_url= "rtmp://" .  $this->push_domain . "/live/" . $streamName . (isset($ext_str) ? $ext_str : "");

        $type = strtolower($this->type);
        switch ($type) {
            case 'rtmp':
                $playUrl = "rtmp://" . $this->play_domain . "/live/" . $streamName;
                break;

            case 'flv':
                $playUrl = "http://" . $this->play_domain . "/live/" . $streamName . '.flv';
                break;
            case  'hls':
                $playUrl = "http://" . $this->play_domain . "/live/" . $streamName . '.m3u8';
                break;
            case 'udp':
                $playUrl = "http://" . $this->play_domain . "/live/" . $streamName;
                break;
            default:
                $playUrl = '';
        }
        return ['push_url'=>$push_url,'play_url'=>$playUrl];
    }
    public function createGroup(){
        $object=new \LiveService();
        /*$object->CreatGroup($group_type,);*/
    }
}