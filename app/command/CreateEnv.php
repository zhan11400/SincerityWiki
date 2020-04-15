<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class CreateEnv extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('createenv')
            ->setDescription('the create env command');
    }

    protected function execute(Input $input, Output $output)
    {
        $envPath = root_path() . '.env';
        $message='File '.$envPath.' exist!';
        if(!file_exists($envPath)){
            @copy(root_path()  . '.example.env', root_path()  . '.env');
            $message='create env success';
        }
    	// 指令输出
    	$output->writeln($message);
    }
}
