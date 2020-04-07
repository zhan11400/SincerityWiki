<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class RegisterValidate extends Validate
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
            'account'  => 'require|unique:member|alphaNum|min:4|max:20',
            'password' => 'require|alphaNum|min:6|max:20|confirm',
            'email'=>'require|email|unique:member',
            'code' => 'captcha',
        ];
        $this->message= [
            'account.unique'  => '用户名已存在',
            'account.require'  => '用户名不能为空',
            'account.min'  => '用户名不能小于6位数',
            'account.max'  => '用户名不能大于于20位数',
            'account.alphaNum'  => '用户名必须字母或者数字',
            'password.require'  => '密码不能为空',
            'password.min'  => '密码不能小于6位数',
            'password.max'  => '密码不能大于于20位数',
            'password.alphaNum'  => '密码必须字母或者数字',
            'password.confirm'  => '两次密码不一致',
            'code.captcha'   => '验证码不正确',
            'email.require'   => 'email必填',
            'email.unique'   => 'email已注册',
            'email.email'   => 'email格式不正确',
        ];
    }
}
