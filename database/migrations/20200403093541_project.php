<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Project extends Migrator
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
        $table  =  $this->table('project',array(
            'engine'=>'innoDB','id' => 'project_id', 'comment' => '项目表',
            'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true
        ));
        $table
            ->addColumn('project_name', 'string',array('limit'  =>100,'default'=>null,'comment'=>'项目名称'))
            ->addColumn('description', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'项目描述'))
            ->addColumn('doc_tree', 'text',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'当前项目的文档树'))
            ->addColumn('project_open_state', 'integer',array('limit'  =>1,'default'=>null,'null'=>true,'comment'=>'项目公开状态：0 私密，1 完全公开，2 加密公开'))
            ->addColumn('project_password', 'string',array('limit'  =>255,'default'=>null,'null'=>true,'comment'=>'项目密码'))
            ->addColumn('doc_count', 'integer',array('limit'  =>10,'default'=>0,'comment'=>'文档数量'))
            ->addColumn('create_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'创建时间'))
            ->addColumn('create_at', 'integer',array('limit'  =>11,'default'=>0,'comment'=>'创建人'))
            ->addColumn('modify_time', 'datetime',array('limit'  =>0,'default'=>null,'null'=>true,'comment'=>'修改时间'))
            ->addColumn('modify_at', 'integer',array('limit'  =>11,'default'=>0,'comment'=>'修改人'))
            ->addColumn('version', 'string',array('limit'  =>50,'default'=>0.1,'comment'=>'版本号'))
            ->addIndex(array('project_id'), array('unique'  =>  true))
            ->create();
    }
}
