<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Member extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {

        // create the table
        $table  =  $this->table('member',array(
            'engine'=>'innoDB','id' => 'member_id', 'comment' => '用户信息表',
            'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true
        ));
        $table
            ->addColumn('account', 'string',array('limit'  =>100,'default'=>'','comment'=>'账号'))
            ->addColumn('member_passwd', 'string',array('limit'  =>255,'default'=>'','comment'=>'密码'))
            ->addColumn('nickname', 'string',array('limit'  =>255,'default'=>'','comment'=>'昵称'))
            ->addColumn('description', 'string',array('limit'  =>255,'default'=>0,'comment'=>'描述'))
            ->addColumn('group_level', 'integer',array('limit'  =>1,'default'=>2,'comment'=>'用户基本：0 超级管理员，1 普通用户，2 访客'))
            ->addColumn('email', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'用户邮箱'))
            ->addColumn('phone', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'用户手机号'))
            ->addColumn('headimgurl', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'用户头像'))
            ->addColumn('remember_token', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'用户session'))
            ->addColumn('state', 'integer',array('limit'  =>1,'default'=>0,'comment'=>'用户状态：0 正常，1 禁用'))
            ->addColumn('create_at', 'integer',array('limit'  =>10,'default'=>0,'comment'=>'创建人'))
            ->addColumn('modify_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'修改时间'))
            ->addColumn('last_login_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'最后登陆时间'))
            ->addColumn('last_login_ip', 'string',array('limit'  =>20,'default'=>null,'null'=>true,'comment'=>'最后登录IP'))
            ->addColumn('user_agent', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'最后登录浏览器信息'))
            ->addColumn('create_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'创建时间'))
            ->addColumn('version', 'datetime',array('limit'  =>0,'default'=>null,'comment'=>'当前时间','null'=>true))
            ->addIndex(array('account'), array('unique'  =>  true))
            ->addIndex(array('email'), array('unique'  =>  true))
            ->create();
    }
}
