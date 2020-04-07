<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Document extends Migrator
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
        $table  =  $this->table('document',array(
            'engine'=>'innoDB','id' => 'doc_id', 'comment' => '文档表',
            'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true
        ));
        $table
            ->addColumn('doc_name', 'string',array('limit'  =>  200,'default'=>'','comment'=>'文档名称'))
            ->addColumn('parent_id', 'biginteger',array('limit'  =>  20,'default'=>0,'comment'=>'父ID'))
            ->addColumn('project_id', 'integer',array('limit'  =>  20,'default'=>0,'comment'=>'所属项目'))
            ->addColumn('doc_sort', 'integer',array('limit'  =>10,'default'=>0,'comment'=>'排序'))
            ->addColumn('doc_content', 'text',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'文档内容'))
            ->addColumn('create_at', 'integer',array('limit'  =>11,'default'=>0,'comment'=>'创建人'))
            ->addColumn('modify_at', 'integer',array('limit'  =>11,'default'=>0,'comment'=>'修改人'))
            ->addColumn('version', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'当前时间'))
            ->addColumn('create_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'创建时间'))
            ->addIndex(array('doc_id'), array('unique'  =>  true))
            ->addIndex(array('project_id'))
            ->addIndex(array('doc_sort'))
            ->create();
    }
}
