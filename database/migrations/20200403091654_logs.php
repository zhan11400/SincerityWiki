<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Logs extends Migrator
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
        $table  =  $this->table('logs',array(
            'engine'=>'innoDB','id' => 'id', 'comment' => '文档编辑历史记录表',
            'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true
        ));
        $table
            ->addColumn('original_data', 'text',array('limit'  =>0,'default'=>null,'comment'=>'操作前的原数据'))
            ->addColumn('present_data', 'text',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'操作后的数据'))
            ->addColumn('content', 'text',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'日志内容'))
            ->addColumn('create_at', 'integer',array('limit'  =>0,'default'=>0,'comment'=>'创建人'))
            ->addColumn('create_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'创建时间'))
            ->create();
    }
}
