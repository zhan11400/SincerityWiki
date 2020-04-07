<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class ResetPassword extends Validate
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
        'oldPasswd'  => 'require',
        'password'   => 'require|confirm|min:6|max:20|alphaNum',
        'email' => 'email',
    ];
        $this->message= [
            'oldPasswd.require' => '旧密码不能为空',
            'password.require'  => '新密码不能为空',
            'password.confirm'   => '两次密码不一致',
            'password.min'  => '密码不能小于6位数',
            'password.max'  => '密码不能大于于20位数',
            'password.alphaNum'  => '密码必须字母或者数字',
        ];
    }
}
