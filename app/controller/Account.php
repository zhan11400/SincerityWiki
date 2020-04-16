<?php
namespace app\controller;
use app\lib\SendEmail;
use app\model\Member;
use app\model\Passwords;
use app\validate\FindPassword;
use app\validate\LoginValidate;
use app\validate\ModifyPassword;
use app\validate\RegisterValidate;
use think\facade\Session;

class Account extends Common
{
    /**登陆
     * @return \think\response\Json|\think\response\Redirect|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(){
        if(request()->isPost()) {
            $post=input();
            if ($this->config['ENABLED_CAPTCHA'] && empty($post['code'])){
                return show(40101,'验证码不能为空');
            }
            $validate=new LoginValidate();
            if(!$validate->check($post)){
                return show(40601,$validate->getError());
            }
            try {
                $member = Member::login($post['account'], $post['password'], request()->ip(),request()->header('User-Agent'));
                $is_remember = input('is_remember');
                session_member($member,$is_remember);
                return show(20001);
            }catch (\Exception $ex){
                return show($ex->getCode(),$ex->getMessage());
            }catch (\Exception $ex){
                return show('90'.$ex->getCode(),$ex->getMessage());
            }
        }
        $cookie =cookie('login_token');
        if(empty($cookie) === false or empty(session('member')) === false){
            $cookie=json_decode($cookie,true);
            $member = Member::where('member_id',$cookie['member_id'])->find();
            session_member($member);
            if($this->request->isGet()) {
                return redirect('/');
            }
        }
        return view('');
    }

    /** 退出登录
     * @return \think\response\Redirect
     */
    public function logout()
    {
        session('member',null);
        cookie('login_token',null);
        $loginUrl = url('account/login');
        return redirect($loginUrl,302);
    }

    /**找回密码
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function findPassword()
    {
        if($this->request->isPost()){
            //如果没有启用邮件
            if(!$this->config['ENABLED_REGISTER']){
                return show(40609,'没有启用邮件');
            }
            $post=input();
            $validate=new FindPassword();
            if(!$validate->check($post)){
                return show(40601,$validate->getError());
            }
            $member = Member::where('email','=',$post['email'])->find();
            if(empty($member)){
                return show(40506,'会员不存在');
            }
            $totalCount = Passwords::where('create_time','>=', date('Y-m-d H:i:s',time() - (int)$this->config['MAIL_TOKEN_TIME']))->count();
            if($totalCount > 5){
                return show(40607,'操作太过频繁');
            }
            $key = md5(uniqid('find_password'));
            $passwords = new Passwords();
            $passwords->email = $post['email'];
            $passwords->token = $key;
            $passwords->is_valid = 0;
            $passwords->user_address = $this->request->ip();
            $passwords->create_time = date('Y-m-d H:i:s');
            if(!$passwords->save()){
                return show(40608,'生成发送记录失败');
            }

            $domain=request()->domain();
            $url =$domain. url('account/modify_password',['key' => $key ]);
            $email= $post['email'];
            //
            $html=view('account/email',compact('url','email'))->getContent();
            $result=(new SendEmail())->send($this->config,$email,$email,'找回密码',$html);
            if($result) {
                $passwords->send_time = date('Y-m-d H:i:s');
                $passwords->save();
            }
            session('process',json_encode([
                'message' => "<p>密码重置链接已经发到您邮箱</p><p><a>{$email}</a> </p><p>请登录您的邮箱并点击密码重置链接进行密码更改</p><p><b>还没收到确认邮件?</b> 尝试到广告邮件、垃圾邮件目录里找找看</p>",
                'title' => '邮件发送成功'
            ]));
            return show(0,'',['url' => url('account/process_result')]);
        }
        return view('');
    }
    /**
     * 显示处理结果
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function processResult()
    {
        $data = json_decode(session('process'),true);
        if(empty($data)){
            return redirect(url('/'));
        }
        Session::delete('process');

        return view('',$data);
    }

    /**修改密码
     * @param $key
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException、
     */
    public function modify_password($key)
    {
        $this->data['token'] = $key;

        if(empty($key)){
            abort(404);
        }

        $passwords = Passwords::where('token','=',$key)->where('is_valid','=','0')->where('create_time','>', date('Y-m-d H:i:s',time() - (int)$this->config['MAIL_TOKEN_TIME']))->find();

        if($this->request->isPost()){
            if(empty($passwords)){
                return show(50001,'链接已失效');
            }
            $post=input();
            $validate=new ModifyPassword();
            if(!$validate->check($post)){
                return show(40601,$validate->getError());
            }
            $member = Member::where('email','=',$passwords->email)->find();
            if(empty($member)){
                return show(40506,'该邮箱没有注册');
            }
            $member->member_passwd =  password_hash($post['password'],PASSWORD_DEFAULT);
            if(!$member->save()){
                return show(500);
            }
            $passwords->is_valid = 1;
            $passwords->valid_time = date('Y-m-d H:i:s');
            $passwords->save();

            return show(0,'success',['url' => (string)url('account/login')]);
        }
        if(empty($passwords)){

            $this->data['title'] = '链接已失效';
            $this->data['message'] ='链接已失效，请重新发送邮件';
        }

        return view('',$this->data);
    }


    /**用户注册
     * @return \think\response\Json|\think\response\View
     */
    public function register()
    {
        //如果启用了注册
        if($this->config["ENABLED_REGISTER"]!=1){
            abort(404,'没有开启注册功能');
        }
        if($this->request->isPost()){
            $post=input();
            if ($this->config['ENABLED_CAPTCHA'] && empty($post['code'])){
                return show(40101,'验证码必填');
            }
            $validate=new RegisterValidate();
            if(!$validate->check($post)){
                return show(40601,$validate->getError());
            }
            $member = new Member();

            $member->account = $post['account'];
            $member->member_passwd = password_hash($post['password'],PASSWORD_DEFAULT);
            $member->nickname = $post['account'];
            $member->email = $post['email'];
            $member->headimgurl = '/static/images/middle.gif';
            $group_level = intval($this->config["DEFAULT_GROUP_LEVEL"]);
            if(in_array($group_level,[0,1,2]) === false){
                $group_level = 2;
            }
            $member->group_level = $group_level;
            $member->create_at = 0;
            try{
                $result = Member::addOrUpdateMember($member);
                if($result == false){
                    return show(500);
                }
                $member = Member::login($post['account'],$post['password']);
                session_member($member);
                return show(0);

            }catch (\Exception $ex){
                return show($ex->getCode(),$ex->getMessage());
            }
        }

        return view("");
    }

}

