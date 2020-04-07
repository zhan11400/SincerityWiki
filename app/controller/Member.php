<?php


namespace app\controller;



use app\model\Config;
use app\model\Project;
use app\Request;
use  app\model\Member as MemberModel;
use app\validate\ResetPassword;
use app\validate\UpdateProfile;
use think\db\Where;
use think\helper\Str;

class Member extends Common
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->data['action'] = request()->action();
        $member = session('member');


        if(!$member){

        }
    }

    /**个人资料
     * @param Request $request
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(Request $request)
    {
        if ($request->isPost()) {
            $post=input();
            $validate=new UpdateProfile();
            if(!$validate->check($post)){
                return show(40601,$validate->getError());
            }
            $member = MemberModel::where('member_id',$this->member_id)->find();
            if (empty($member)) {
                return show(40601,'会员不存在');
            }
            $member->nickname = $post['userNickname'];
            $member->email =  $post['userEmail'];
            $member->phone = $post['userPhone'];
            $member->description = $post['description'];
            try {
                $result = MemberModel::addOrUpdateMember($member);
                if ($result == false) {
                    return show(500,'系统错误');
                }
                session_member($member);
                return show(0);

            } catch (\Exception $ex) {
                return show($ex->getCode(),$ex->getMessage());
            }
        }

        return view('', $this->data);
    }

    /**项目列表
     * @return \think\response\View
     */
    public function projects()
    {
        $this->data['lists'] = Project::getParticipationProjectList($this->member,10);
        $this->data['is_can_create_project']=Project::isCanCreateProject($this->member);
        return view('',$this->data);
    }

    /**用户列表
     * @return \think\response\View
     * @throws \think\db\exception\DbException
     */
    public function users()
    {
        $members = MemberModel::order('member_id DESC')->paginate(20);

        $this->data['lists'] = $members;

        return view('',$this->data);
    }

    /**编辑或添加用户
     * @param null $id
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editUser($id = null)
    {
        if(\request()->isPost()){
            $member_id =input('member_id');
            $account = trim(input('userAccount'));
            $nickname = trim(input('userNickname',''));
            $email =input('userEmail');
            $phone = input('userPhone');
            $des =input('description');
            $password = input('userPasswd');
            $group_level = intval(input('group_level',1));
            if(in_array($group_level,[0,1,2]) == false){
                $group_level = 1;
            }

            if($member_id<= 0 and empty($password)){
                return  show(40103,'密码不能为空');
            }

            if($member_id > 0) {
                $member = MemberModel::where('member_id',$member_id)->find();
                if (empty($member)) {
                    return  show(40103,'会员不存在');
                }
                if($member->member_id == 1 && $group_level != 0){
                    return  show(40512,'系统超级管理员不能修改权限');
                }
            }else{
                $member = new MemberModel();
                $member->account = $account;
                $member->create_at = $this->member_id;
            }
            if(empty($password) === false) {
                $member->member_passwd = password_hash($password, PASSWORD_DEFAULT);
            }
            $member->nickname = $nickname;
            $member->email = $email;
            $member->phone =$phone;
            $member->description = $des;

            if(empty($member->headimgurl) ){
                $member->headimgurl = '/static/images/middle.gif';
            }

            $member->group_level = $group_level;

            try{
                $result = MemberModel::addOrUpdateMember($member);
                if($result == false){
                    return  show(500,'系统有误');
                }
                return  show(0);

            }catch (\Exception $ex){
                return  show($ex->getCode(),$ex->getMessage());
            }

        }
        $member_id = intval($id);
        if($member_id > 0){
            $member = MemberModel::where('member_id',$member_id)->find();

            if(empty($member) ){
                abort(404);
            }
            $this->data['member_info'] =$member;
        }
        return view('',$this->data);
    }

    /**添加或删除项目组用户
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addMember($id)
    {
        $project_id = intval($id);
        $type = trim($this->request->input('type'));
        $account = trim($this->request->input('account'));
        if (empty($project_id)) {
            return show(50502,'项目不存在');
        }
        $project = Project::where('project',$project_id)->find();
        if (empty($project)) {
            return show(40206,'项目不存在');
        }
        //如果不是项目的拥有者并且不是超级管理员
        if (!Project::isOwner($project_id,$this->member->member_id) && $this->member->group_level != 0) {
            return $this->jsonResult(40305);
        }
        $member = Member::findNormalMemberOfFirst([['account', '=', $account]]);
        if (empty($member)) {
            return $this->jsonResult(40506);
        }

        if($member->state == 1){
            return $this->jsonResult(40511);
        }
        $data = null;
        $rel = Relationship::where('project_id', '=', $project_id)->where('member_id', '=', $member->member_id)->first();
        //如果是添加成员
        if (strcasecmp($type, 'add') === 0) {
            if (empty($rel) === false) {
                return $this->jsonResult(40801);
            }
            $rel = new Relationship();
            $rel->project_id = $project_id;
            $rel->member_id = $member->member_id;
            $rel->role_type = 0;
            $result = $rel->save();

            if($result) {
                $item = new \stdClass();

                $item->role_type  = $rel->role_type;
                $item->account    = $member->account;
                $item->member_id  = $member->member_id;
                $item->email      = $member->email;
                $item->headimgurl = $member->headimgurl;
                $this->data['item'] = $item;

                $data = view('widget.project_member',$this->data)->render();
            }
        } else {
            $result = empty($rel) === false ? $rel->delete() : false;
        }

        return $result ? $this->jsonResult(0,$data) : $this->jsonResult(500);
    }
    /**密码修改
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function account()
    {
        $this->data['member_account'] = true;

        if(\request()->isPost()){
            $post=input();
            $validate=new ResetPassword();
            $result = $validate->check($post);
            if(!$result){
                return show(40601,$validate->getError());
            }
            if(password_verify($post['oldPasswd'],$this->member->member_passwd) === false){
                return show(40605,'密码错误');
            }
            $member = MemberModel::where('member_id',$this->member_id)->find();
            $member->member_passwd = password_hash($post['password'],PASSWORD_DEFAULT);
            if(! $member->save()){
                return show(500,'系统错误');
            }
            session_member($member);
            return show(0);
        }
        return view('',$this->data);
    }

    /**开发设置
     * @return \think\response\View
     * @throws \think\db\exception\DbException
     */
    public function setting()
    {
        $this->data['lists'] = Config::order('id DESC')->paginate(20);
        return view('',$this->data);
    }

    /**添加或更新网站设置
     * @param null $id
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setting_edit($id = null)
    {
        if($this->request->isPost()){
            $config_id = intval(input('config_id'));
            $name = trim(input('name'));
            $value = trim(input('value'));
            $key = trim(input('key'));
            $remark  = trim(input('remark'));
            if($config_id <= 0 && (empty($name) or mb_strlen($name) <3 or mb_strlen($name) > 20)){
                return  show(40701,'名称在3-20字之间');
            }
            $matches = [];

            if($config_id <= 0 && (empty($key) or !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{5,19}$/',$key,$matches))){
                return  show(40702,'键值字母开头,6位数以上');
            }
            $result = Config::where('id','<>',$config_id)
              //  ->fetchSql(true)
                ->where(function ($query) use($name, $key) {
                    $query->where('name', $name);
                    $query->whereOr('key', $key);
                })
                ->find();
            if(empty($result) === false){
                return  show(40704,'名称或者键名已存在');
            }

            if(mb_strlen($remark) > 1000){
                $remark = mb_substr($remark,0,1000);
            }

            if($config_id > 0){
                $config = Config::where('id',$config_id)->find();
                if(empty($config)){
                    return  show(40703,'记录不存在');
                }
            }else{
                $config = new Config();
                $config->config_type = 'user';
            }
            //只能用户变量可以修改键名和键值
            if($config->config_type == 'user') {
                $config->key = $key;
                $config->name = $name;
            }
            $config->value = $value;
            $config->remark = $remark;

            if($config->save() == false){
                return  show(500,'系统繁忙');
            }
            Config::getConfigFromCache(true);
            return  show(0,'成功',['id'=>$config->id]);
        }
        $config_id = intval($id);

        if($config_id > 0){
            $config = Config::where('id',$config_id)->find();
            if(empty($config)){
                abort(404);
            }
            $this->data['conf']=$config;
        }
        return view('',$this->data);
    }

    /**删除网站设置
     * @param null $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteSetting($id = null)
    {
        $config_id = intval($id);

        if($config_id <= 0){
            return  show(40703,'id有误');
        }
        $config = Config::where('id',$config_id)->find();
        if(empty($config)){
            return  show(40704,'记录不存在');
        }
        if($config->config_type == 'system'){
            return  show(40705,'系统常量，不能删除');
        }
        if($config->delete() == false){
            return  show(500,'删除失败');
        }
        return  show(0,'删除成功');
    }

    /**站点设置
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function site()
    {
        if($this->request->isPost()){
            $form = input();
            if(empty($form) === false){
                foreach ($form as $key=>$value){
                    Config::where('key','=',$key)->update(['value'=>$value]);
                }
            }
            Config::getConfigFromCache(true);
            return  show(0);
        }
        $result =  Config::select()->toArray();


        if(empty($result) === false && count($result) > 0){
            $this->data = array_merge($this->data,array_column($result,'value','key'));
        }
        return view('',$this->data);
    }
}