<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class FindPassword extends Validate
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
            'email'  => 'require|email',
            'code' => 'require|captcha',
        ];
        $this->message= [
            'email.require' => 'email不能为空',
            'email.email'  => 'email格式有误',
            'code.require'   => '验证码必填',
            'code.captcha'   => '验证码不正确',
        ];
    }
}
