<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Relationship extends Migrator
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
        $table  =  $this->table('relationship',array(
            'engine'=>'innoDB','id' => 'rel_id', 'comment' => '项目成员表表',
            'collation' => 'utf8_general_ci', 'row_format' => 'DYNAMIC', 'signed' => true
        ));
        $table
            ->addColumn('member_id', 'integer',array('limit'  =>10,'default'=>0,'comment'=>'会员id'))
            ->addColumn('project_id', 'integer',array('limit'  =>10,'default'=>null,'null'=>true,'comment'=>'项目id'))
            ->addColumn('role_type', 'integer',array('limit'  =>0,'default'=>0,'comment'=>'项目角色：0 参与者，1 所有者'))
            ->addIndex(array('project_id'))
            ->addIndex(array('member_id'))
            ->create();
    }
}
