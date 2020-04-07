<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Passwords extends Migrator
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
        $table  =  $this->table('passwords',array(
            'engine'=>'innoDB','id' => 'id', 'comment' => '找回密码',
            'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true
        ));
        $table
            ->addColumn('token', 'string',array('limit'  =>100,'default'=>null,'comment'=>'唯一认证码'))
            ->addColumn('email', 'string',array('limit'  =>255,'default'=>null,'comment'=>'用户邮箱'))
            ->addColumn('is_valid', 'integer',array('limit'  =>1,'default'=>0,'comment'=>'0有效，1无效'))
            ->addColumn('user_address', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'用户IP地址'))
            ->addColumn('send_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'邮件发送时间'))
            ->addColumn('create_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'创建时间'))
            ->addColumn('valid_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'校验时间'))
            ->addIndex(array('token'), array('unique'  =>  true))
            ->create();
    }
}
