class <?php echo $class_name; ?> extends SMigration
{
    public function up()
    {
        $t = new STable();
        
        $t->add_primary_key('id');
        // $t->add_column('name', 'string');
        
        $this->create_table('<?php echo $table_name; ?>', $t);
    }
    
    public function down()
    {
        $this->drop_table('<?php echo $table_name; ?>');
    }
}
