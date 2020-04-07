<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class UpdateProfile extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [];
    public function __construct()
    {
        parent::__construct();
        $this->rule =[
            'userNickname'  => 'require',
            'userPhone'   => 'mobile',
            'userEmail' => 'email',
        ];
        $this->message= [
            'userNickname.require' => '昵称不能为空',
            'userPhone.mobile'  => '手机号码格式有误',
            'userEmail.email'   => '邮箱格式有误',
        ];
    }
}
