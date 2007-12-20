<?php

class CmsSchema extends SDbSchema
{
    public function define()
    {
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('parent_id', 'integer');
        $t->add_column('position', 'integer');
        $t->add_column('slug', 'string');
        $t->add_column('full_path', 'string');
        $t->add_column('title', 'string');
        $t->add_column('content', 'text');
        $t->add_column('published', 'boolean');
        $t->add_column('created_on', 'datetime');
        $t->add_column('updated_on', 'datetime');
        $this->create_table('pages', $t);
        
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('permalink', 'string');
        $t->add_column('title', 'string');
        $t->add_column('teaser', 'text');
        $t->add_column('content', 'text');
        $t->add_column('published', 'boolean');
        $t->add_column('created_on', 'datetime');
        $t->add_column('updated_on', 'datetime');
        $this->create_table('posts', $t);
        
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('name', 'string');
        $t->add_column('value', 'string');
        $this->create_table('settings', $t);
        
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('lastname', 'string');
        $t->add_column('firstname', 'string');
        $t->add_column('email', 'string');
        $t->add_column('login', 'string');
        $t->add_column('password', 'string');
        $t->add_column('active', 'boolean');
        $t->add_column('superuser', 'boolean');
        $t->add_column('last_access', 'datetime');
        $this->create_table('users', $t);
        
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('name', 'string');
        $t->add_column('email', 'string');
        $t->add_column('subject', 'string');
        $t->add_column('body', 'text');
        $this->create_table('messages', $t);
    }
}

?>
