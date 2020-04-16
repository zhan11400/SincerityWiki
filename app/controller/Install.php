<?php


namespace app\controller;


use app\BaseController;


class Install extends BaseController
{
     protected  $data=[];

    /**下一步，安装
     * @return \think\response\Json|\think\response\View
     */
    public function next()
    {
        if($this->request->isPost()) {
            $dbHost = $this->request->param('dataAddress');
            $dbUser = $this->request->param('dataAccount');
            $dbName = $this->request->param('dataName');
            $dbPassword = $this->request->param('dataPassword');
            $dbPort = $this->request->param('dataPort','3306');

            $account = $this->request->param('account');
            $password = $this->request->param('password');
            $email = $this->request->param('email');

            try{
                system_install($dbHost,$dbName,$dbPort,$dbUser,$dbPassword,$account,$password,$email);
            }catch (\Exception $ex){
                return show($ex->getCode(),null,$ex->getMessage());
            }

            @file_put_contents(public_path('install.lock'),'true');
            session('install.result',true);
            $url=(string)url('account/login');
            return show(0,'success',["url" => $url]);
        }
        return view('next',$this->data);
    }


}