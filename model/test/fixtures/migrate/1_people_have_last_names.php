<?php

class PeopleHaveLastNames extends SMigration
{
    public function up()
    {
        $this->addColumn('people', 'last_name', 'string');
    }
    
    public function down()
    {
        $this->removeColumn('people', 'last_name');
    }
}

?>
