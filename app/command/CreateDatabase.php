<?php
declare (strict_types = 1);

namespace app\command;

use PDO;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use think\facade\Env;

class CreateDatabase extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('CreateDatabase')
            ->addArgument('name', Argument::OPTIONAL, "your name")
            ->addOption('city', null, Option::VALUE_REQUIRED, 'city name')
            ->setDescription('the hello command');        
    }


    protected function execute(Input $input, Output $output)
    {
        $host=env('DATABASE_HOSTNAME');
        $db=Env::get('DATABASE_DATABASE');
        $user=Env::get('DATABASE_USERNAME');
        $password=Env::get('DATABASE_PASSWORD');
        $port=Env::get('DATABASE_HOSTPORT');
        $dsn="mysql:host=$host;port=$port;";
        try{
            $pdo=new PDO($dsn,$user,$password);//初始化一个PDO对象，就是创建了数据库连接对象$pdo
            $query="SELECT * FROM information_schema.SCHEMATA where SCHEMA_NAME='".$db."';";
            $res=$pdo->query($query);//执行添加语句并返回受影响行数
            $SCHEMA_NAME='';
            foreach($res as $val){
                $SCHEMA_NAME=$val['SCHEMA_NAME'];
            }
            if($SCHEMA_NAME){
                $output->writeln("database $db is exists !");
            }else {
                $query = "CREATE DATABASE if not exists `$db` CHARACTER SET 'utf8' COLLATE 'utf8_general_ci';";//需要执行的sql语句
                $pdo->exec($query);//执行添加语句并返回受影响行数
                $output->writeln('CREATE SUCCESS !');
            }
        }catch(\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
