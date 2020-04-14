<?php


namespace app\model;


use Carbon\Carbon;
use mysql_xdevapi\Exception;
use think\db\Where;
use think\facade\Cache;
use think\facade\Db;
use think\Model;

class Project extends Model
{
    /**
     * 查询可查看的项目列表
     * @param int $pageIndex
     * @param int $pageSize
     * @param null $member_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getProjectByMemberId($member, $pageSize = 20, $keyword = null)
    {
        $where=[];
        if(empty($member) === false){
        //Member::isSuperMember($member->member_id);
            //超级管理员
            if(empty($member) === false && $member->group_level === 0){

                $Result=  Project::where($where)
                    ->order("project_id desc")
                    ->paginate($pageSize)
                    ->appends([
                        'keyword' => $keyword
                    ]);

                return $Result;
            }

            $Result=  Project::where($where)
                ->order("project_id desc")
                ->where('project_open_state','<>',0)
                ->whereLike('project_name|description','%'.$keyword.'%')
                ->paginate($pageSize)
                ->appends([
                    'keyword' => $keyword
                ]);
            return $Result;

        }else{
            $Result=  Project::where($where)
                ->where('project_open_state','<>',0)
                ->whereLike('project_name|description','%'.$keyword.'%')
                ->order("project_id desc")
                ->fetchsql(true)
                ->paginate($pageSize)
                ->appends([
                    'keyword' => $keyword
                ]);
            return $Result;
        }
    }
    /**
     * 从缓存中获取项目信息
     * @param $project_id
     * @param bool $update
     * @return bool|Project|null
     */
    public static function getProjectFromCache($project_id,$update = false)
    {
        if(empty($project_id)){
            return false;
        }
        $key = 'project.id.' . $project_id;

            $project = Cache::get($key);

        if(empty($project) || $update) {
            $project = Project::where('project_id','=',$project_id)->find();
            if(empty($project)){
                return false;
            }
            $expiresAt = Carbon::now()->addHour(12);
            Cache::set($key, $project, $expiresAt);
        }
        return $project;
    }
    /**
     * 判断用户是否有查看指定文档权限
     * @param $project_id
     * @param int|null $member_id
     * @param string|null $passwd
     * @return int 0 项目不存在；1 有权限； 2 需要密码； 3 没有权限
     */
    public static function hasProjectShow($project_id,$member_id = null,$passwd = null)
    {
        $project = Project::getProjectFromCache($project_id);

        if(empty($project)){
            return 0;
        }
        if($project->project_open_state == 1){
            return 1;
        }
        if(empty($member_id) === false){
            //超级管理员不限制权限
            if(Member::isSuperMember($member_id)){
                return 1;
            }
        }

        if ($project->project_open_state == 2) {

            if(empty($passwd)) {
                return 2;
            }elseif (strcasecmp($passwd,$project->project_password) === 0){
                return 1;
            }
        }
        if(empty($member_id) === false) {
            $rel = Relationship::where('project_id', '=', $project_id)
                ->where('member_id', '=', $member_id)
                ->find();
            return empty($rel) ? 3 : 1;
        }
        return 3;
    }


    /**
     * 获取项目的文档树Html结构
     * @param int $project_id
     * @param int $selected_id
     * @return string
     */
    public static function getProjectHtmlTree($project_id,$selected_id = 0,$keywords=null)
    {
        if(empty($project_id)){
            return '';
        }
        $tree = Document::where('project_id','=',$project_id)
            ->field('doc_id,doc_name,parent_id')
            ->where(function ($query) use ($keywords){
                if($keywords){
                    $query->whereLike('doc_name','%'.$keywords.'%');
                }
            })
            ->order('doc_sort ASC')
            ->select()
            ->toArray();
        if(empty($tree) === false){
            $parent_id = self::getSelecdNode($tree,$selected_id);
           return self::createTree(0,$tree,$selected_id,$parent_id,$keywords);
        }
        return '';
    }
    protected static function getSelecdNode($array,$parent_id)
    {
        foreach ($array as $item){
            if($item['doc_id'] == $parent_id and $item['parent_id'] == 0){
                return $item['doc_id'];
            }elseif ($item['doc_id'] == $parent_id and $item['parent_id'] != 0){
                return self::getSelecdNode($array,$item['parent_id']);
            }
        }
        return 0;
    }
    protected static function createTree($parent_id,array $array,$selected_id = 0,$selected_parent_id = 0,$keywords=null)
    {
        global $menu;
        $menu .= '<ul>';
        foreach ($array as $item){
            if($keywords){
                $selected = $item['doc_id'] == $selected_id ? ' class="jstree-clicked"' : '';
                $selected_li = $item['doc_id'] == $selected_parent_id ? ' class="jstree-open"' : '';
                $menu .= '<li id="' . $item['doc_id'] . '"' . $selected_li . '><a href="' . url('document/show', ['doc_id' => $item['doc_id']]) . '" title="' . htmlspecialchars($item['doc_name']) . '"' . $selected . '>' . $item['doc_name'] . '</a>';
                $menu .= '</li>';
            }else {
                if ($item['parent_id'] == $parent_id) {
                    $selected = $item['doc_id'] == $selected_id ? ' class="jstree-clicked"' : '';
                    $selected_li = $item['doc_id'] == $selected_parent_id ? ' class="jstree-open"' : '';

                    $menu .= '<li id="' . $item['doc_id'] . '"' . $selected_li . '><a href="' . url('document/show', ['doc_id' => $item['doc_id']]) . '" title="' . htmlspecialchars($item['doc_name']) . '"' . $selected . '>' . $item['doc_name'] . '</a>';

                    $key = array_search($item['doc_id'], array_column($array, 'parent_id'));

                    if ($key !== false) {
                        self::createTree($item['doc_id'], $array, $selected_id, $selected_parent_id);
                    }
                    $menu .= '</li>';
                }
            }
        }
        $menu .= '</ul>';
        return $menu;
    }
    /**
     * 搜索项目
     * @param string $keyword 关键字
     * @param int $pageIndex
     * @param int $pageSize
     * @return bool|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function search($keyword,$pageSize = 20, $memberId = null)
    {
        if(empty($keyword)) {
            return false;
        }
        $keyword = '%'. preg_replace('/\s+/','%',trim($keyword)).'%';

        if(empty($memberId) === false) {

            //如果是管理员，则不限制
            if(Member::isSuperMember($memberId)) {
                $searchResult=  Project::where('project_name', 'like', $keyword)
                    ->whereOr('description', 'like', $keyword)
                    ->order("project_id desc")
                    ->paginate($pageSize)
                    ->appends([
                        'keyword' => $keyword
                    ]);
            }else {
                $searchResult = DB::table('project as pro')->select(['pro.*'])
                    ->leftJoin('relationship as rel', function ($join) use ($memberId) {
                        $join->on('pro.project_id', '=', 'rel.project_id')
                            ->where('rel.member_id', '=', $memberId);
                    })
                    ->where(function ($query) {
                        $query->where('rel_id', '>', 0)
                            ->orWhere('project_open_state', '<>', 0);
                    })
                    ->where(function ($query) use ($keyword) {
                        $query->where('project_name', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword);
                    })
                    ->orderBy('project_id', 'DESC')
                    ->paginate($pageSize, ['*'], 'page', $pageIndex)
                    ->appends([
                        'keyword' => $keyword
                    ]);
            }
        }else {
            $searchResult = DB::table('project')->select(['*'])
                ->where('project_open_state', '<>', 0)
                ->where(function ($query) use ($keyword) {
                    $query->where('project_name', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                })
                ->orderBy('project_id', 'DESC')
                ->paginate($pageSize, ['*'], 'page', $pageIndex)
                ->appends([
                    'keyword' => $keyword
                ]);
        }
        return $searchResult;
    }


    /**
     * 查询可编辑的项目列表
     * @param int $member_id
     * @param int $pageIndex
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getParticipationProjectList($member, $pageSize = 20)
    {
        //如果是超级管理员则无限制
        if(empty($member) === false && $member->group_level === 0){
              $Result=  Project::alias('pro')
                  ->leftJoin('relationship rel','pro.project_id = rel.project_id and rel.member_id='.$member->member_id)
                  ->leftJoin('member m','m.member_id =rel.member_id')
                  ->order("pro.project_id desc")
                  ->paginate($pageSize)
                  ->each(function($item,$key){
                          $doc = Document::alias('d')->where('project_id','=',$item->project_id)
                              ->field('d.modify_time,d.create_time,m.account,m.nickname')
                              ->leftJoin('member m','m.member_id =d.create_at')
                              ->limit(1)->order('modify_time DESC')->find();
                          if($doc) {
                              $item->last_document_time = $doc->modify_time ?: $doc->create_time;
                              $item->last_document_user = $doc->nickname?:$doc->account;
                          }

                  });
                        return $Result;

        }else{
            $Result=  Project::alias('pro')
                ->where('rel.member_id','=',$member->member_id)
                ->leftJoin('relationship rel','pro.project_id = rel.project_id')
                ->leftJoin('member m','m.member_id =rel.member_id')
                ->order("pro.project_id desc")
                ->paginate($pageSize)
                ->each(function($item,$key){
                    $doc = Document::alias('d')->where('project_id','=',$item->project_id)
                        ->field('d.modify_time,d.create_time,m.account,m.nickname')
                        ->leftJoin('member m','m.member_id =d.create_at')
                        ->limit(1)->order('modify_time DESC')->find();
                    if($doc) {
                        $item->last_document_time = $doc->modify_time ?: $doc->create_time;
                        $item->last_document_user = $doc->nickname?:$doc->account;
                    }

                });
            return $Result;
        }

    }

    /**
     * 判断制定用户是否有创建项目权限
     * @param $member
     * @return bool
     */
    public static function isCanCreateProject($member){

        if(!$member || ($member->group_level != 0 && $member->group_level != 1)){
            return false;
        }
        return true;
    }

    /**
     * 判断是否是指定项目的拥有者
     * @param int $project_id
     * @param int $member_id
     * @return bool
     */
    public static function isOwner($project_id,$member_id)
    {
        $result = Relationship::where('project_id','=',$project_id)->where('member_id','=',$member_id)->find();

        return (empty($result) || $result->role_type == 0) ? false : true;
    }
    /**
     * 添加或更新项目
     * @return bool
     * @throws \Exception
     */
    public function addOrUpdate()
    {
        if(empty($this->project_name) || mb_strlen($this->project_name) < 2 || mb_strlen($this->project_name) > 50){
            throw new \Exception('项目名称必须在2-50字之间',40201);
        }
        if(mb_strlen($this->description) > 1000){
            throw new \Exception('项目描述不能超过1000字',40202);
        }

        if(in_array($this->project_open_state,['0','1','2']) === false){
            throw new \Exception('项目公开状态错误',40204);
        }

        if($this->project_open_state == 2 and (strlen($this->project_password) < 6 or strlen($this->project_password) > 20)){
            throw new \Exception('项目密码必须在6-20字之间',40203);
        }

        if($this->project_open_state != 2){
            $this->project_password = null;
        }

        Db::startTrans();
        try{


            if($this->project_id <= 0){
                $relationship = new Relationship();
                $relationship->member_id = $this->create_at;
                $relationship->project_id = 0;
                $relationship->role_type = 1;

            }
            $this->save();
            if(isset($relationship)){
                $relationship->project_id = $this->id;
                $relationship->save();
            }

            Db::commit();
            return true;
        }catch (\Exception $ex){
            Db::rollback();
            throw new \Exception($ex->getMessage(),500);
        }
    }
    /**
     * 删除项目以及项目相关的文档
     * @param $project_id
     * @return bool
     * @throws DataNullException|ResultException
     */
    public static function deleteProjectByProjectId($project_id)
    {
        $project = Project::where('project_id',$project_id)->find();
        if(empty($project)){
            throw new \Exception('项目不存在',40206);
        }

        $docs = Document::where('project_id','=',$project_id)->field('doc_id')->select();
        Db::startTrans();
        try {
            if (empty($docs) === false) {
                Document::where('project_id', '=', $project_id)->delete();
                DocumentHistory::whereIn('doc_id', $docs)->delete();
            }
            Relationship::where('project_id', '=', $project_id)->delete();
            $project->delete();
            Db::commit();
            return true;
        }catch (\Exception $ex){
            Db::rollback();
            throw new \Exception($ex->getMessage(),500);
        }

    }

    /**
     * 获取指定项目的参与用户列表
     * @param int $project_id
     * @return array|static[]
     */
    public static function getProjectMemberByProjectId($project_id)
    {
        $query = DB::name('relationship')
            ->alias('rel')
            ->leftJoin('member m','rel.member_id = m.member_id')
            ->where('rel.project_id','=',$project_id)
            ->order('rel.rel_id DESC')
            ->select();
        return $query;
    }
    /**
     * 获取指定用户是否有指定文档编辑权限
     * @param int $project_id
     * @param int $member_id
     * @return bool
     */
    public static function hasProjectEdit($project_id,$member_id)
    {
        if(empty($project_id) or empty($member_id)){
            return false;
        }
        //超级管理员不限制权限
        $member = Member::where('member_id',$member_id)->find();

        if(empty($member) === false && $member->group_level == 0){
            return true;
        }
        $project = DB::name('relationship')->alias('ship')
            ->field('pro.*')
            ->leftJoin('project as pro','ship.project_id','=','pro.project_id')
            ->where('ship.member_id','=',$member_id)
            ->where('ship.project_id','=',$project_id)
            ->find();
        return empty($project) === false;
    }
    /**
     * 获取项目的文档树
     * @param int $project_id
     * @return array
     */
    public static function getProjectArrayTree($project_id)
    {
        if(empty($project_id)){
            return [];
        }
        $tree = Document::where('project_id','=',$project_id)
            ->field('doc_id,doc_name,parent_id')
            ->order('doc_sort ASC')
            ->select();
        $jsonArray = [];

        if(empty($tree) === false){
            foreach ($tree as &$item){
                $tmp['id'] = $item ->doc_id.'';
                $tmp['text'] = $item->doc_name;
                $tmp['parent'] = ($item->parent_id == 0 ? '#' : $item->parent_id).'';

                $jsonArray[] = $tmp;
            }
        }
        return $jsonArray;
    }


}