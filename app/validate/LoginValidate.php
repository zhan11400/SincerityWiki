<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class LoginValidate extends Validate
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
            'account'  => 'require',
            'password' => 'require',
            'code' => 'captcha',
        ];
        $this->message= [
            'account.require' => '登录账号不能为空',
            'password.require'  => '登录密码不能为空',
            'code.captcha'   => '验证码不正确',
        ];
    }
}
