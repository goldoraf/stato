<?php

class AddReminders extends SMigration
{
    public function up()
    {
        $t = new STable();
        $t->addPrimaryKey('id');
        $t->addColumn('content', 'text');
        $t->addColumn('remind_at', 'datetime');
        $this->createTable('reminders', $t);
    }
    
    public function down()
    {
        $this->dropTable('reminders');
    }
}

?>
