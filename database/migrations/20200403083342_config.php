<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Config extends Migrator
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
        $table  =  $this->table('config',array('engine'=>'innoDB','id' => 'id', 'comment' => '开发设置', 'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true));
        $table
            ->addColumn('name', 'string',array('limit'  =>  100,'default'=>'','comment'=>'名称'))
            ->addColumn('key', 'string',array('limit'  =>  200,'default'=>'','comment'=>'键'))
            ->addColumn('value', 'string',array('limit'  =>  200,'default'=>'','comment'=>'值'))
            ->addColumn('config_type', 'string',array('limit'  =>  20,'default'=>'user','comment'=>'变量类型：system 系统内置/user 用户定义'))
            ->addColumn('remark', 'string',array('limit'  =>  200,'default'=>'','comment'=>'备注'))
            ->addColumn('create_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'创建时间'))
            ->addColumn('modify_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'修改时间'))
            ->addIndex(array('id'), array('unique'  =>  true))
            ->addIndex(array('key'), array('unique'  =>  true))
            ->create();
    }
}
