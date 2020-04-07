<?php


namespace app\controller;

use \app\model\Project as ProjectModel;
use app\model\Relationship;
use think\facade\Log;
use \app\model\Member as MemberModel;
use function GuzzleHttp\Psr7\str;

class Project extends Member
{
    /**创建项目
     * @return mixed
     */
    public function create()
    {
        //如果非管理员用户并且非普通用户则禁止创建项目
        if($this->member->group_level != 0 && $this->member->group_level != 1){
            abort(403);
        }

        $projectName = trim($this->request->input('projectName'));
        $description = trim($this->request->input('description',null));
        $isPasswd = $this->request->input('projectPasswd','1');
        $passwd = trim($this->request->input('projectPasswdInput',null));

        $project = new Project();
        $project->project_name = $projectName;
        $project->description = $description;
        $project->project_open_state = $isPasswd;
        $project->project_password = $passwd;
        $project->create_at = $this->member_id;

        try{
            $project->addOrUpdate();

        }catch (\Exception $ex){
            if($ex->getCode() == 500){
                return show(40205,null,$ex->getMessage());
            }else{
                return show($ex->getCode());
            }
        }
        $this->data = $project->toArray();

        $this->data['doc_count'] = 0;

        $view = view('widget.project',$this->data);
        $this->data = array();

        $this->data['body'] = $view->render();

        return show(20002,$this->data);
    }

    /**删除项目
     * @param $id
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
        $this->data['member_projects'] = true;
        $project_id = intval($id);
        if ($project_id <= 0) {
            if(request()->isAjax()){
                return  show(50502,'参数有误');
            }
            abort(404);
        }
        $project = ProjectModel::where('project_id',$project_id)->find();
        if (empty($project)) {
            if(request()->isAjax()){
                return  show(40206,'项目不存在');
            }

            abort(404);
        }
        //如果不是项目的拥有者并且不是超级管理员
        if (!ProjectModel::isOwner($project_id,$this->member->member_id) && $this->member->group_level != 0) {
            if(request()->isAjax()){
                return  show(40305,'没有权限');
            }

            abort(403);
        }

        if(request()->isPost()) {
            $password = input('password');
            //如果密码错误
            if(password_verify($password,$this->member->member_passwd) === false){
                return  show(40606,'密码错误');
            }

            try{
                ProjectModel::deleteProjectByProjectId($project_id);
                return  show(0);
            }catch (\Exception $ex){
                if($ex->getCode() == 500){

                    Log::error($ex->getMessage(),['trace'=>$ex->getTrace(),'file'=>$ex->getFile(),'line'=>$ex->getLine()]);
                    return  show(500,'删除失败');
                }else{
                    return  show($ex->getCode(),$ex->getMessage());
                }
            }
        }
        $this->data['project'] = $project;

        return view('project.delete',$this->data);
    }

    /**编辑项目或创建
     * @param null $id
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($id = null)
    {
        $project_id = intval($id);
        //如果是访客则不能创建项目
        if($project_id <=0 && ProjectModel::isCanCreateProject($this->member) === false){
            abort(403);
        }
        $project = null;
        //如果项目不存在
        if($project_id > 0 && empty($project = ProjectModel::where("project_id",$id)->find()) ){
            if(request()->isPost()){
                return show(40206);
            }else{
                abort(404);
            }
        }

        //如果没有编辑权限
        if($project_id > 0 &&  $this->member->group_level != 0 && ProjectModel::hasProjectEdit($project_id,$this->member_id) === false){
            if(request()->isPost()){
                return show(40305);
            }else{
                abort(403);
            }
        }
        //如果不是项目的拥有者并且不是超级管理员
        if ($project_id > 0 && !ProjectModel::isOwner($project_id,$this->member->member_id) && $this->member->group_level != 0) {

            abort(403);
        }

        //如果是修改项目
        if(request()->isPost()){
            $name = trim(input('name'));
            $description = trim(input('description'));
            $open_state = input('state');
            $password =input('password');
            $version = input('version');
            if(empty($project)) {
                $project = new ProjectModel();
            }
            $project->project_name = $name;
            $project->description = $description;
            $project->project_open_state = $open_state;
            $project->project_password = $password;
            $project->version = $version;
            $project->create_at = $this->member_id;
            try{
                if($project->addOrUpdate()) {
                    $data['project_id'] = $project->project_id;
                    $data['url'] = url('project/edit',['id'=>$project->project_id]);

                    return show(0,'',$data);
                }else{
                    return show(500);
                }
            }catch (\Exception $ex){
                if($ex->getCode() == 500){
                    return show(40205,$ex->getMessage());
                }else{
                    return show((int)$ex->getCode(),$ex->getMessage());
                }
            }
        }
        $this->data['title'] = '编辑项目';
        if(empty($project)){
            $project = new ProjectModel();
            $project->project_open_state = 0;
            $this->data['title'] = '添加项目';
            $this->data['is_owner'] = false;
        }else{
            $this->data['is_owner'] = ProjectModel::isOwner($project_id,$this->member->member_id) ;
        }
        $this->data['project'] = $project;
        $this->data['member_projects'] = true;

        return view('',$this->data);
    }

    /**项目参与成员列表
     * @param $id
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function members($id)
    {
        $project_id = intval($id);

        if(empty($project_id)){
            abort(404);
        }

        $project = ProjectModel::where('project_id',$project_id)->find();
        if(empty($project)){
            abort(404);
        }

        //如果不是项目的拥有者并且不是超级管理员
        if (!ProjectModel::isOwner($project_id,$this->member->member_id) && $this->member->group_level != 0) {
            return  show(40305,'error');

        }

        $this->data['project'] = $project;
        $this->data['member'] = $this->member;
        $this->data['member_projects'] = true;
        $this->data['users'] = ProjectModel::getProjectMemberByProjectId($project_id);

        $a= view('',$this->data)->getContent();

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
        $type = trim(input('type'));
        $account = trim(input('account'));

        if (empty($project_id)) {
            return show(50502,'项目不存在');
        }
        $project = ProjectModel::where('project_id',$project_id)->find();
        if (empty($project)) {
            return show(40206,'项目不存在');
        }
        //如果不是项目的拥有者并且不是超级管理员
        if (!ProjectModel::isOwner($project_id,$this->member->member_id) && $this->member->group_level != 0) {
            return show(40305,'是项目的拥有者并且不是超级管理员');
        }
        $member = MemberModel::findNormalMemberOfFirst([['account', '=', $account]]);
        if (empty($member)) {
            return show(40506,'用户不存在');
        }

        if($member->state == 1){
            return show(40511,'用户已被冻结');
        }
        $data = null;
        $rel = Relationship::where('project_id', '=', $project_id)->where('member_id', '=', $member->member_id)->find();
        //如果是添加成员
        if (strcasecmp($type, 'add') === 0) {
            if (empty($rel) === false) {
                return show(40801,'用户已经在当前项目中');
            }
            $rel = new Relationship();
            $rel->project_id = $project_id;
            $rel->member_id = $member->member_id;
            $rel->role_type = 0;
            $result = $rel->save();

            if($result) {
                $item['role_type']  = $rel->role_type;
                $item['account']    = $member->account;
                $item['member_id']  = $member->member_id;
                $item['email']      = $member->email;
                $item['headimgurl'] = $member->headimgurl;
                $this->data['item'] = $item;
                $data['html']=view('widget/project_member',$this->data)->getContent();
            }
        } else {
            $result = empty($rel) === false ? $rel->delete() : false;
        }

        return $result ? show(0,'',$data) :show(500);
    }

    /**退出项目
     * @param $id
     * @return mixed
     */
    public function quit($id)
    {
        $project_id = intval($id);
        if (empty($project_id)) {
            return show(50502);
        }
        if($this->member->group_level === 2){
            return show(403);
        }

        $project = Project::find($project_id);
        if (empty($project)) {
            return show(40206);
        }

        $relationship = Relationship::where('project_id','=',$project_id)->where('member_id','=',$this->member_id)->first();


        //如果是项目参与者，则退出
        if(empty($relationship) === false && $relationship->role_type === 0){

            $result = $relationship->delete();
            return $result ? show(0) : show(500);
        }
        return show(500,null,'非参与者无法退出');
    }

    /**项目转让
     * @param $id
     * @return \think\response\Json
     */
    public function transfer($id)
    {
        $project_id = intval($id);
        $account = trim(input('account'));
        if (empty($project_id)) {
            return show(50502,'项目id不能为空');
        }
        $project = ProjectModel::where('project_id',$project_id)->find();
        if (empty($project)) {
            return show(40206,'项目不存在');
        }
        //如果不是项目的拥有者并且不是超级管理员
        if (!ProjectModel::isOwner($project_id,$this->member->member_id) && $this->member->group_level != 0) {
            return show(40305,'没有权限');
        }
        $member = MemberModel::findNormalMemberOfFirst([['account', '=', $account]]);
        if (empty($member)) {
            return show(40506,'转让的用户不存在');
        }
        //将拥有用户降级为参与者
        $rel = Relationship::where('project_id', '=', $project_id)->where('role_type', '=', 1)->find();
        $rel->role_type = 0;
        if(!$rel->save()){
            return show(40802,'转让失败');
        }
        //如果目标用户存在则升级为拥有者
        $newRel = Relationship::where('project_id', '=', $project_id)->where('member_id', '=', $member->member_id)->find();
        if(empty($newRel)){
            $newRel = new Relationship();
        }

        $newRel->project_id = $project_id;
        $newRel->member_id = $member->member_id;
        $newRel->role_type = 1;
        if(!$newRel->save()){
            return show(40802,'转让失败');
        }
        return show(0);

    }


}