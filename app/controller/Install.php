<?php


namespace app\controller;


use app\BaseController;
use PDO;
use think\facade\Db;
use think\facade\Env;

class Install extends BaseController
{
     protected  $data=[];

    /**下一步，安装
     * @return \think\response\Json|\think\response\View
     */
    public function next()
    {
        $host=env('DATABASE_HOSTNAME');
        $db=Env::get('DATABASE_DATABASE');
        $user=Env::get('DATABASE_USERNAME');
        $password=Env::get('DATABASE_PASSWORD');
        $port=Env::get('DATABASE_HOSTPORT');
        $dsn="mysql:host=$host;port=$port;";
        try{
            $pdo=new PDO($dsn,$user,$password);//初始化一个PDO对象，就是创建了数据库连接对象$pdo
            echo '<pre>';
            $query="SELECT * FROM information_schema.SCHEMATA where SCHEMA_NAME='".$db."';";
            $res=$pdo->query($query);//执行添加语句并返回受影响行数
            var_dump($res);
            $SCHEMA_NAME='';
            foreach($res as $val){
                $SCHEMA_NAME=$val['SCHEMA_NAME'];
            }
            var_dump($SCHEMA_NAME);
            exit;
            $query="CREATE DATABASE if not exists `$db` CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';";//需要执行的sql语句
            $res=$pdo->exec($query);//执行添加语句并返回受影响行数
            var_dump($res);
        }catch(\Exception $e) {
        var_dump($e->getMessage());
        }
        exit;
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