<?php


namespace app\controller;

use app\model\Config;
use app\model\Document;
use app\model\Member;
use app\model\Project;
use app\middleware\Authenticate;
use app\middleware\Install;
use think\facade\App;
use function GuzzleHttp\Psr7\_caseless_remove;


class Index extends Common
{
    const PageSize=20;

    /**文档列表
     * @return \think\response\View
     */
    public function index(){
        $keyword = input('keyword');
        $this->data['keyword'] = $keyword;
        $this->data['lists'] = Project::getProjectByMemberId($this->member,self::PageSize,$keyword);

        $this->data['count']=$this->data['lists']->total();
        return view('',$this->data);
    }

    /**显示文档
     * @param $id
     * @return \think\response\Redirect|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show($id)
    {
        if($id <= 0){
            abort(404);
        }
        $project = Project::getProjectFromCache($id);
        if(empty($project)){
            abort(404);
        }

        $member_id = null;
        if(empty($this->member) === false){
            $member_id = $this->member->member_id;

        }

        $permissions = Project::hasProjectShow($id,$member_id);

        //校验是否有权限访问文档
        if($permissions === 0){
            abort(404);
        }elseif($permissions === 2){
            $role = session_project_role($id);
            if(empty($role)){
                $this->data = $project;
                return view('home.password',$this->data);
            }
        }else if($permissions === 3 && empty($member_id)){
            return redirect(route("account.login"));
        }elseif($permissions === 3) {
            abort(403);
        }
        $member = Member::where('create_at',$project->create_at)->find();

        $this->data['author'] = '未知';
        $this->data['author_headimgurl'] = '/static/images/middle.gif';
        $this->data['modify_time'] = $project->modify_time?:$project->create_time;
        $this->data['title'] = $project->project_name;
        $this->data['project'] = $project;
        $this->data['tree'] = Project::getProjectHtmlTree($id);
        $this->data['body'] = $project->description;
        $this->data['first_document'] = Document::where('project_id','=',$id)->where('parent_id','=',0)->order('doc_sort ASC')->limit(1)->field('doc_id,doc_name,parent_id')->select();
        //查询作者信息
        if(empty($member) === false) {
            $this->data['author'] = $member->nickname?:$member->account;
            $this->data['author_headimgurl'] = $member->headimgurl ?: $member->headimgurl;
        }
        //查询最后修改时间
        $lastModifyDoc = Document::where('project_id','=',$id)->order('modify_time','DESC')->find();
        if($lastModifyDoc && $lastModifyDoc->modify_time) {
            $this->data['modify_time'] = $lastModifyDoc->modify_time;
        }
        return view('',$this->data);
    }

    /**显示文档
     * @param $id
     * @return \think\response\Redirect|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manual($id)
    {
        if($id <= 0){
            abort(404);
        }
        $project = Project::getProjectFromCache($id);
        if(empty($project)){
            abort(404);
        }

        $member_id = null;
        if(empty($this->member) === false){
            $member_id = $this->member->member_id;

        }

        $permissions = Project::hasProjectShow($id,$member_id);

        //校验是否有权限访问文档
        if($permissions === 0){
            abort(404);
        }elseif($permissions === 2){
            $role = session_project_role($id);
            if(empty($role)){
                $this->data = $project->toArray();
                return view('index/password',$this->data);
            }
        }else if($permissions === 3 && empty($member_id)){
            return redirect(url("account/login"));
        }elseif($permissions === 3) {
            abort(403);
        }
        $member = Member::where('member_id',$project->create_at)->find();

        $this->data['author'] = '未知';
        $this->data['author_headimgurl'] = '/static/images/middle.gif';
        $this->data['modify_time'] = $project->modify_time?:$project->create_time;
        $this->data['title'] = $project->project_name;
        $this->data['project'] = $project;
        $this->data['tree'] = Project::getProjectHtmlTree($id);
        $this->data['body'] = $project->description;
        $this->data['first_document'] = Document::where('project_id','=',$id)->where('parent_id','=',0)->order('doc_sort ASC')->limit(1)->field('doc_id,doc_name,parent_id')->select();
        //查询作者信息
        if(empty($member) === false) {
            $this->data['author'] = $member->nickname?:$member->account;
            $this->data['author_headimgurl'] = $member->headimgurl ?: $member->headimgurl;
        }

        //查询最后修改时间
        $lastModifyDoc = Document::where('project_id','=',$id)->order('modify_time','DESC')->find();
        if($lastModifyDoc && $lastModifyDoc->modify_time) {
            $this->data['modify_time'] = $lastModifyDoc->modify_time;
        }
        return view('',$this->data);
    }

    /**检查文档访问密码是否正确
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkDocumentAuth()
    {
        $id = intval(input('pid',0));
        $passwd = input('projectPwd');

        if($id <= 0){
            return show(40301,'id有误');
        }
        $project = Project::where('project_id',$id)->find();
        if(empty($project)){
            return show(40301,'文档不存在');
        }
        $member_id = empty($this->member) ? null : $this->member->member_id;
        if(Project::hasProjectShow($id,$member_id,$passwd) === 1){
            session_project_role($id,['project_id'=>$id,'project_password' => $passwd]);
            return show(0);
        }
        return show(40302,'密码错误');
    }
    
}