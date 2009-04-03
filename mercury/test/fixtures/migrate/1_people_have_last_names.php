<?php

class PeopleHaveLastNames extends SMigration
{
    public function up()
    {
        $this->add_column('people', 'last_name', 'string');
    }
    
    public function down()
    {
        $this->remove_column('people', 'last_name');
    }
}

?>
