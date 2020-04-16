<?php


namespace app\model;


use app\lib\exception\ApiException;
use think\Exception;
use think\Model;

class Member extends Model
{
    /**会员登录
     * @param $account
     * @param $password
     * @param null $ip
     * @param null $userAgent
     * @return array|Model|null
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function login($account,$password,$ip = null, $userAgent = null)
    {
        $member = Member::where('account','=',$account)->where('state','=',0)->find();

        if(empty($member) or password_verify($password,$member->member_passwd) === false){

            throw new ApiException('账号或密码错误',40401);
        }
        $original_data = json_encode($member,JSON_UNESCAPED_UNICODE);

        $member->last_login_time = date('Y-m-d H:i:s');
        $member->last_login_ip = $ip;
        $member->user_agent = $userAgent;
        $member->save();

        $logs = "用户 {$account} 在 {$member->last_login_time} 登录成功.IP：{$ip}，User-Agent：{$userAgent}。";
        $present_data = json_encode($member,JSON_UNESCAPED_UNICODE);

        Logs::addLogs($logs,$member->member_id,$original_data,$present_data);

        return $member;
    }
    /**
     * 判断用户是否是超级管理员
     * @param int $memberId
     * @return bool
     */
    public static function isSuperMember($memberId)
    {
        $memberId = intval($memberId);
        if($memberId <= 0){
            return false;
        }
        $member = Member::where('member_id',$memberId)->find();
        //如果是管理员，则不限制
        return (empty($member) === false && $member->group_level === 0);
    }

    /**
     * 添加或更新用户信息
     * @param Member $member
     * @return bool
     * @throws \Exception
     */
    public static function addOrUpdateMember(Member &$member)
    {
        if($member->member_id <= 0 and empty($member->account)){
            throw new Exception('账号不能为空',40507);
        }
        $matches = array();
        if($member->member_id<=0 and !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,19}$/',$member->account,$matches)){
            throw new Exception('账号必须以英文字母开头并且大于5个字符小于20个字符',40508);
        }

        if(empty($member->nickname) === false and mb_strlen($member->nickname) > 20){
            throw new Exception('用户昵称最少3个字符，最多20字符',40501);
        }
        if(empty($member->email)){
            throw new Exception('用户邮箱不能为空',40502);
        }
        if(!filter_var ($member->email, FILTER_VALIDATE_EMAIL )){
            throw new Exception('用户邮箱不合法',40503);
        }
        if(empty($phone) === false and strlen($member->phone) > 20){
            throw new Exception('手机号码不合法',40504);
        }
        if(empty($member->description) === false and mb_strlen($member->description) > 500){
            throw new Exception('描述最多500字',40505);
        }



        if($member->member_id > 0) {

            if(empty(Member::where('email','=',$member->email)->where('member_id','<>',$member->member_id)->find()) === false){
                throw new Exception('邮箱已存在',40509);
            }

            $user = Member::where('member_id',$member->member_id)->find();
            if(empty($user)){
                throw new Exception('用户不存在',40506);
            }


        }else{
            if(Member::where('account','=',$member->account)->find()){
                throw new Exception('账号已存在',40513);
            }
            if(Member::where('email','=',$member->email)->find()){
                throw new Exception('邮箱已存在',40509);
            }
        }
        return $member->save();
    }
    /**
     * 获取状态为正常的用户信息
     * @param array $columns
     * @param array $where
     * @return Member|null
     */
    public static function findNormalMemberOfFirst($where = array(), $columns = ['*'])
    {
        $query = static::where('state','=',0);

        if(empty($where) === false){
            foreach ($where as $item){
                $query = call_user_func_array([$query, 'where'], $item);
            }
        }
        return $query->field($columns)->find();
    }
}