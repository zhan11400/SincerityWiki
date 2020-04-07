<?php

use think\migration\Migrator;
use think\migration\db\Column;

class DocumentHistory extends Migrator
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
        $table  =  $this->table('document_history',array(
            'engine'=>'innoDB','id' => 'history_id', 'comment' => '文档编辑历史记录表',
            'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true
        ));
        $table
            ->addColumn('doc_id', 'biginteger',array('limit'  =>  20,'default'=>0,'comment'=>'文档ID'))
            ->addColumn('parent_id', 'biginteger',array('limit'  =>  20,'default'=>0,'comment'=>'父ID'))
            ->addColumn('doc_name', 'string',array('limit'  =>200,'default'=>0,'comment'=>'文档名称'))
            ->addColumn('doc_content', 'text',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'文档内容'))
            ->addColumn('create_at', 'integer',array('limit'  =>11,'default'=>0,'comment'=>'历史记录创建人'))
            ->addColumn('modify_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'修改时间'))
            ->addColumn('modify_at', 'integer',array('limit'  =>0,'default'=>0,'comment'=>'修改人'))
            ->addColumn('version', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'当前时间'))
            ->addColumn('create_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'创建时间'))
            ->addIndex(array('history_id'), array('unique'  =>  true))
            ->addIndex(array('doc_id'))
            ->create();
    }
}
