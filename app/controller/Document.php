<?php


namespace app\controller;
use  \app\model\Document as DocumentModel;
use app\model\Project;

class Document extends Common
{
    /**显示文档
     * @param $doc_id
     * @return \think\response\Json|\think\response\Redirect|\think\response\View
     */
    public function show($doc_id)
    {
        $doc_id = intval($doc_id);
        if($doc_id <= 0){
            abort(404);
        }

        $doc = DocumentModel::getDocumentFromCache($doc_id);

        if(empty($doc) ){
            abort(404);
        }
        $project = Project::getProjectFromCache($doc->project_id);

        if(empty($project)){
            abort(404);
        }

        $permissions = Project::hasProjectShow($project->project_id,$this->member_id);

        //校验是否有权限访问文档
        if($permissions === 0){
            abort(404);
        }elseif($permissions === 2){
            $role = session_project_role($project->project_id);
            if(empty($role)){
                $this->data = $project;
                return view('home.password',$this->data);
            }
        }else if($permissions === 3 && empty($member_id)){
            return redirect(route("account.login"));
        }elseif($permissions === 3) {
            abort(403);
        }

        $this->data['project'] = Project::getProjectFromCache($doc->project_id);

        $this->data['tree'] = Project::getProjectHtmlTree($doc->project_id,$doc->doc_id);
        $this->data['title'] = $doc->doc_name;

        if(empty($doc->doc_content) === false){
            $this->data['body'] = DocumentModel::getDocumnetHtmlFromCache($doc_id);
        }else{
            $this->data['body'] = '';
        }

        if($this->request->isAjax()){
            unset($this->data['member']);
            unset($this->data['project']);
            unset($this->data['tree']);
            $this->data['doc_title'] = $doc->doc_name;


            return show(0,null,$this->data);
        }
        $this->data['domain']=request()->domain();
        return view('',$this->data);
    }

    /**文档编辑首页
     * @param $id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index($id)
    {
        if(empty($id) or $id <= 0){
            abort(404);
        }
        $project = Project::where('project_id',$id)->find();
        if(empty($project)){
            abort(404);
        }
        //判断是否有编辑权限
        if(Project::hasProjectEdit($id,$this->member->member_id) === false){
            abort(403);
        }

        $jsonArray = Project::getProjectArrayTree($id);
        if(empty($jsonArray) === false){
            $jsonArray[0]['state']['selected'] = true;
            $jsonArray[0]['state']['opened'] = true;
        }
        $this->data['project_id'] = $id;
        $this->data['project'] = $project;
        $this->data['json'] = json_encode($jsonArray,JSON_UNESCAPED_UNICODE);
        return view('',$this->data);
    }

    /**获取文档编辑内容
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getContent($id)
    {
        if (empty($id) or $id <= 0) {
            abort(404);
        }

        $doc = DocumentModel::where("doc_id",$id)->find();
        if (empty($doc)) {
            return show(40301,'文档不存在');
        }
        $role = Project::hasProjectShow($doc->project_id, $this->member_id);
        if ($role == false) {
            return show(40305,'没有权限');
        }
        $this->data['doc']['doc_id'] = $doc->doc_id;
        $this->data['doc']['name'] = $doc->doc_name;
        $this->data['doc']['project_id'] = $doc->project_id;
        $this->data['doc']['parent_id'] = $doc->parent_id;
        $this->data['doc']['content'] = $doc->doc_content;

        unset($this->data['member']);
        return show(0,'成功',$this->data);
    }

    /**保存文档
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function save()
    {
        $project_id = input('project_id');

        if($this->request->isPost()){
            $document = null;

            $content = input('editormd-markdown-doc',null);
            //如果是保存文档内容
            if(empty($content) === false){
                $doc_id = intval(input('doc_id'));
                if($doc_id <= 0){
                    return show(40301);
                }
                $document = DocumentModel::where('doc_id',$doc_id)->find();
                if(empty($document)){
                    return show(40301);
                }
                //如果没有编辑权限
                if(Project::hasProjectEdit($document->project_id,$this->member_id) == false){
                    return show(40305);
                }
                //如果文档内容没有变更
                if(strcasecmp(md5($content),md5($document->doc_content)) === 0) {
                    return show(0, ['doc_id' => $doc_id, 'parent_id' => $document->parent_id, 'name' => $document->doc_name]);
                }
                $document->doc_content = $content;
                $document->modify_at = $this->member_id;
            }else {
                //如果是新建文档
                if (Project::hasProjectEdit($project_id, $this->member_id) == false) {
                    return show(40305);
                }
                $doc_id = intval(input('id', 0));
                $parentId = intval(input('parentId', 0));
                $name = trim(input('documentName', ''));
                $sort = intval(input('sort'));

                //文档名称不能为空
                if (empty($name)) {
                    return show(40303,'文档名称不能为空');
                }


                //查看是否存在指定的文档
                if ($doc_id > 0) {
                    $document = DocumentModel::where('project_id', '=', $project_id)->where('doc_id', '=', $doc_id)->find();
                    if (empty($document)) {
                        return show(40301,'记录不存在');
                    }
                }
                //判断父文档是否存在
                if ($parentId > 0) {
                    $parentDocument = DocumentModel::where('project_id', '=', $project_id)->where('doc_id', '=', $parentId)->find();
                    if (empty($parentDocument)) {
                        return show(40301,'父文档不存在');
                    }
                }

                if($doc_id > 0) {
                    //查看是否有重名文档
                    $doc = DocumentModel::where('project_id', '=', $project_id)->where('doc_name', '=', $name)->where('doc_id','<>',$doc_id)->find();
                    if (empty($doc) === false) {
                        return show(40304,'已存在同名文档');
                    }
                }else{
                    //查看是否有重名文档
                    $doc = DocumentModel::where('project_id', '=', $project_id)->where('doc_name', '=', $name)->find();
                    if (empty($doc) === false) {
                        return show(40304,'已存在同名文档');
                    }
                }

                if (empty($document) === false and $document->parent_id == $parentId and strcasecmp($document->doc_name, $name) === 0 and $sort <= 0) {
                    return show(0,'success', ['doc_id' => $doc_id, 'parent_id' => $parentId, 'name' => $name]);
                }

                $document = $document ?: new DocumentModel();

                $document->project_id = $project_id;
                $document->doc_name = $name;
                $document->parent_id = $parentId;

                if ($doc_id <= 0) {
                    $document->create_at = $this->member_id;
                    $sort = DocumentModel::where('parent_id','=',$parentId)->order('doc_sort','DESC')->find();

                    $sort = ($sort ? $sort['doc_sort'] : -1) + 1;

                }else{
                    $document->modify_at = $this->member_id;
                }

                if($sort > 0) {
                    $document->doc_sort = $sort;
                }
            }

            if($document->save() === false){
                return show(500,'保存失败');
            }
            $data = ['doc_id' => $document->doc_id.'','parent_id' => ($document->parent_id == 0 ? '#' : $document->parent_id .''),'name' => $document->doc_name];

            return show(0,'success',$data);
        }

        return show(405);
    }
    /**
     * 保存排序信息
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sort($id)
    {
        if(Project::hasProjectEdit($id,$this->member_id) == false){
            return show(40305,'没有编辑权限');
        }

        $params = $this->request->getContent();
        if(empty($params) === false){
            $params = json_decode($params,true);

            if(empty($params) === false){
                foreach ($params as $item){
                    $data = ['parent_id'=>intval($item['parent']),'doc_sort'=>$item['sort'],'modify_at' => $this->member_id];

                    DocumentModel::where('project_id','=',$id)->where('doc_id','=',$item['id'])->update($data);
                }
            }
        }
        return show(0);
    }

    /**删除文档
     * @param $doc_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($doc_id)
    {
        $doc_id = intval($doc_id);

        if($doc_id <= 0){
            return show(40301,'文档id不能为空');
        }

        $doc = DocumentModel::where('doc_id',$doc_id)->find();
        //如果文档不存在
        if(empty($doc)){
            return show(40301,'文档不存在');
        }
        //判断是否有编辑权限
        if(Project::hasProjectEdit($doc->project_id,$this->member_id) == false){
            return show(40305,'没有权限');
        }
        $result = DocumentModel::deleteDocument($doc_id);

        if($result){
            return show(0);
        }else{
            return show(500,'系统繁忙');
        }
    }

    /**导出work文档
     * @param $id
     * @return \think\response\Redirect|\think\response\View
     */
    public function export($id)
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
            return redirect(url("account/login"));
        }elseif($permissions === 3) {
            abort(403);
        }

        $tree = Project::getProjectArrayTree($id);


        $filename = $project->project_name;

        header('pragma:public');
        header('Content-type:application/vnd.ms-word;charset=utf-8;name="'.$filename.'".doc');
        header("Content-Disposition:attachment;filename=$filename.doc");
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        $path = root_path().'public/static/styles/kancloud.css';
        $content = '';
        if(file_exists($path)){
            $content .= '<head><style type="text/css">' . file_get_contents($path) . '</style></head>';
        }
        $content .= '<body><div class="m-manual"><div class="manual-article"><div class="article-content"><div class="article-body editor-content"><div style="width: 100%;text-align: center;font-size: 25px;padding: 50px 0;"><h1>' . $project->project_name . '</h1></div>';

        $content .= self::recursion('#',1,$tree) . '</div></div></div></div></body>';
        echo output_word($content);
    }

    /**循环拼接
     * @param $parent
     * @param $level
     * @param $tree
     * @return string
     */
    private static function recursion($parent,$level,&$tree)
    {
        global $content;
        $content .= '';

        if ($level > 7) {
            $level = 6;
        }
        foreach ($tree as $item) {

            if ($item['parent'] == $parent) {

                $doc = DocumentModel::getDocumnetHtmlFromCache($item['id']);

                if ($doc !== false) {
                    $text = '<h' . $level . '>' . $item['text'] . '</h' . $level . '>'  . '<div style="margin: 50px auto;">' . $doc . '</div>';

                    $content  .= $text;
                }

                $key = array_search($item['id'], array_column($tree, 'parent'));

                if ($key !== false) {
                    $level++;
                    self::recursion($item['id'], $level, $tree);
                }
            }
        }
        $content .= '';
        return $content;
    }

}