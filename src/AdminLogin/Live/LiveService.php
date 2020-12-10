<?php
/**
 *
 * 直播类/即时通讯
 */
class LiveService
{
    private $api;
    private $sdkappid = '1400439450';//腾讯云直播appid
    private $identifier = 'administrator';//腾讯云即时通讯群组管理员账号
    private $key = '59bc65c0d84462dd7901ec2a3056dafe9712ac448e99937f5b1f08e0a70cdfa2';//密钥
    /**
     * 构造函数
     */
    function __construct()
    {
        require_once('tim/TimApi.php');
        $this->api = createTimAPI($this->sdkappid,$this->identifier,$this->key);
    }
    /**
     *在创建直播间时创建群组
     * $group_type默认类型
     * $user_id 创建直播间用户_直播间id [id_$user_id1]
     * $user_id1 创建用户id
     */
    public function CreatGroup($group_type = 'AVChatRoom', $user_id, $user_id1){
        $CreatGroup = $this->api->group_create_group($group_type, (string)$user_id, (string)$user_id1);
        return $CreatGroup;
    }

    /**
     * 进入直播间时加入到群组
     * $group_id 群id 就是直播间group_id
     * $member_id 进入群的用户id
     * $silence 是否静默加人（选填）
     */
    public function AddGroupMember($group_id, $member_id, $silence = 1){
        $AddGroupMember = $this->api->group_add_group_member($group_id, (string)$member_id, $silence);
        return $AddGroupMember;
    }

    /**
     * 退出直播间时从群组销毁
     *  $group_id 群id 就是直播间group_id
     * $member_id 进入群的用户id
     * $silence 是否静默加人（选填）
     */
    public function DeleteGroupMember($group_id, $member_id, $silence = 1){
        $DeleteGroupMember = $this->api->group_delete_group_member($group_id, (string)$member_id, $silence);
        return $DeleteGroupMember;
    }

    /**
     * 发群消息【礼物，普通消息 弹幕消息 主播退出 禁言 观众进入房间 观众退出房间】
     * $account_id  发送者用户id
     * $group_id  群id就是直播间group_id
     * $text_content 消息数据
     */
    public function GroupSend($account_id, $group_id, $text_content){
        #构造高级接口所需参数
        $msg_content = array();
        //创建array 所需元素
        $msg_content_elem = array(
            'MsgType' => 'TIMCustomElem',       //文本类型TIMTextElem
            'MsgContent' => array(
                'Data' => json_encode($text_content),                //hello 为文本信息
            )
        );
        array_push($msg_content, $msg_content_elem);
        // dump($msg_content);die();
        $GroupSend = $this->api->group_send_group_msg2((string)$account_id, $group_id, $msg_content);
        return $GroupSend;
    }

    /**
     * 关闭直播间时销毁群组
     * $group_id  群id  就是直播间group_id
     */
    public function DestroyGroup($group_id){
        $DestroyGroup = $this->api->group_destroy_group((string)$group_id);
        return $DestroyGroup;
    }

    /**
     * 查询账号
     * $arrUserid 用户id二维数组
     */
    public function AccountGroup($arrUserid){
        $AccountGroup = $this->api->AccountGroup($arrUserid);
        return $AccountGroup;
    }
    /**
     * 查询账号状态
     * $arrUserid 用户id一维数组
     */
    public function AccountStateGroup($arrUserid){
        $AccountStateGroup = $this->api->AccountStateGroup($arrUserid);
        return $AccountStateGroup;
    }
     /** 导入账户
     * $identifier  用户账户 就是
     * $nick   用户名称
     * $face_url 头像地址
     */
    public function accountImport($identifier, $nick, $face_url){
        $accountImport = $this->api->account_import($identifier, $nick, $face_url);
        return $accountImport;
    }
    /** 批量导入账户
     * $ist userId列表
     */
    public function multiaccountImport($list){
        $accountImport = $this->api->multiaccount_import($list);
        return $accountImport;
    }
    /** 获取群成员信息
     * $group_id  群组id
     * $limit    一次最多获取多少个成员的资料，不得超过10000。如果不填，则获取群内全部成员的信息
     * $offset   从第几个成员开始获取，如果不填则默认为0，表示从第一个成员开始获取
     * $role   成员角色
     */

    public function groupGetGroupMemberInfo($group_id, $limit, $offset,$role)
    {
         $res = $this->api->group_get_group_member_info($group_id,$limit,$offset,$role);

         return $res;
    }
    /**   获取直播群在线人数
     * $group_id  群组id
     */
    public function getOnlineMemberNum($group_id)
    {
        $res = $this->api->get_online_member_num($group_id);

        return $res;
    }

}
