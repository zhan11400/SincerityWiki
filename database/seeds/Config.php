<?php

use think\migration\Seeder;

class Config extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data=[
          //  ['name'=>'启用文档历史','key'=>'ENABLED_HISTORY','value'=>1,'config_type'=>'system','remark'=>'是否启用文档历史记录：0 否/1 是','create_time'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],
            ['name'=>'站点名称','key'=>'SITE_NAME','value'=>'SincerityWiki','config_type'=>'system','remark'=>'站点名称','create_time'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],
            ['name'=>'邮件有效期','key'=>'MAIL_TOKEN_TIME','value'=>3600,'config_type'=>'system','remark'=>'找回密码邮件有效期,单位为秒','create_time'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],
            ['name'=>'启用匿名访问','key'=>'ENABLE_ANONYMOUS','value'=>1,'config_type'=>'system','remark'=>'是否启用匿名访问：0 否/1 是','create_time'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],
            ['name'=>'启用登录验证码','key'=>'ENABLED_CAPTCHA','value'=>1,'config_type'=>'system','remark'=>'是否启用登录验证码：0 否/1 是','create_time'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],
            ['name'=>'是否启用注册','key'=>'ENABLED_REGISTER','value'=>1,'config_type'=>'system','remark'=>'是否启用注册：0 否/1 是','create_time'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],
            ['name'=>'注册默认的用户角色','key'=>'DEFAULT_GROUP_LEVEL','value'=>2,'config_type'=>'system','remark'=>'注册默认的用户角色：0 超级管理员/1 普通用户/ 2 访客','create_time'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],
        ];
        $posts = $this->table('config');
        $posts->insert($data)
            ->save();

        $data=[
            'account'=>'admin',
            'member_passwd'=>password_hash('123456', PASSWORD_DEFAULT),
            'nickname'=>'admin',
            'headimgurl'=>'/static/images/middle.gif',
            'group_level'=>0,
            'email'=>'',
            'phone'=>'',
            'remember_token'=>'',
            'modify_time'=>date('Y-m-d H:i:s'),
            'last_login_time'=>date('Y-m-d H:i:s'),
            'last_login_ip'=>0,
            'user_agent'=>'',
            'create_time'=>date('Y-m-d H:i:s'),
            'version'=>date('Y-m-d H:i:s'),
        ];
        $posts = $this->table('member');
        $posts->insert($data)
            ->save();
        file_put_contents(root_path().'install.lock', 'true');
    }
}