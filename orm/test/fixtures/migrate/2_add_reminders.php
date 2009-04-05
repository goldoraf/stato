<?php

class AddReminders extends SMigration
{
    public function up()
    {
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('content', 'text');
        $t->add_column('remind_at', 'datetime');
        $this->create_table('reminders', $t);
    }
    
    public function down()
    {
        $this->drop_table('reminders');
    }
}

?>
