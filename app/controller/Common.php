<?php


namespace app\controller;




use app\BaseController;
use app\model\Config;
use app\middleware\Authenticate;
use app\middleware\Install;
use app\Request;
use think\facade\View;

class Common extends BaseController
{
    protected $middleware = [Install::class,Authenticate::class];
    protected $data = array();
    protected $config=[];
    protected $member=[];
    protected $member_id;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->response = response();
        $member = session('member');
        if($member) {
            $this->member = $member;
            $this->data['member'] = $member;
            $this->member_id = $member->member_id;
            // $this->assign('member_id',$this->member_id);
            View::assign('group_level', $member->group_level);
             View::assign('role_type', $member->role_type);
        }

        $this->config=Config::getConfigFromCache();
        View::assign('config',$this->config);
    }
}