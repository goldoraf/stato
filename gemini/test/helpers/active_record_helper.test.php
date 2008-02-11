<?php

require_once(STATO_CORE_PATH.'/mercury/lib/column.php');
require_once(STATO_CORE_PATH.'/mercury/lib/validation.php');

class MockContent extends MockRecord
{
    public $id = null;
    public $content_attributes = array();
    public $new_record  = true;
    public $errors = array();
    protected $attributes = array('title', 'body', 'private', 'written_on');
    
    public function is_new_record() { return $this->new_record; }
    
    public function content_attributes() { return $this->content_attributes; }
}

class RecordHelperTest extends StatoTestCase
{
    public function setUp()
    {
        $this->post = new MockContent();
        $this->post->title      = 'PHP for ever';
        $this->post->body       = 'PHP is a general-purpose scripting language...';
        $this->post->private    = true;
        $this->post->written_on = new SDate(2006, 3, 31);
    }
    
    public function test_basic_input_tag()
    {
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
        );
        $this->assertDomEqual(
            input('post', 'title', $this->post),
            '<input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" />'
        );
    }
    
    public function test_basic_input_tag_with_error()
    {
        $this->post->errors['title'] = 'Error !';
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
        );
        $this->assertDomEqual(
            input('post', 'title', $this->post),
            '<div class="field-with-errors">
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" />
            </div>'
        );
    }
    
    public function test_error_message_for()
    {
        $this->post->errors['title'] = 'Title can\'t be empty';
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
        );
        $this->assertDomEqual(
            error_message_for('post', $this->post),
            '<div id="form-errors" class="form-errors">
            <h2>Please correct the following errors :</h2>
            <ul>
            <li>Title can\'t be empty</li>
            </ul>
            </div>'
        );
        $this->assertDomEqual(
            error_message_for('post', $this->post, array('id' => 'bad-errors', 'header_tag' => 'h5')),
            '<div id="bad-errors" class="form-errors">
            <h5>Please correct the following errors :</h5>
            <ul>
            <li>Title can\'t be empty</li>
            </ul>
            </div>'
        );
    }
    
    public function test_error_message_on()
    {
        $this->post->errors['title'] = 'can\'t be empty';
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
        );
        $this->assertDomEqual(
            error_message_on('title', $this->post),
            '<div class="form-error">can\'t be empty</div>'
        );
        $this->assertDomEqual(
            error_message_on('title', $this->post, 'Title ', ' you stupid !', 'mistake'),
            '<div class="mistake">Title can\'t be empty you stupid !</div>'
        );
    }
    
    public function test_form_with_strings()
    {
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
            'body' => new SColumn('body', 'text'),
        );
        $this->assertDomEqual(
            form('post', $this->post, array('controller' => 'posts')),
            '<form method="post" action="/posts/create">
            <p><label for="post_title">Title</label>
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" /></p>
            <p><label for="post_body">Body</label>
            <textarea name="post[body]" id="post_body" cols="40" rows="20">PHP is a general-purpose scripting language...</textarea></p>
            <input type="submit" name="commit" value="Create" />
            </form>'
        );
    }
    
    public function test_form_with_boolean()
    {
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
            'private' => new SColumn('private', 'boolean')
        );
        $this->assertDomEqual(
            form('post', $this->post, array('controller' => 'posts')),
            '<form method="post" action="/posts/create">
            <p><label for="post_title">Title</label>
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" /></p>
            <p><label for="post_private">Private</label>
            <input name="post[private]" type="hidden" value="0" />
            <input checked="checked" id="post_private" name="post[private]" type="checkbox" value="1" />
            </p>
            <input type="submit" name="commit" value="Create" />
            </form>'
        );
    }
    
    public function test_form_with_existent_record()
    {
        $this->post->id = 1;
        $this->post->new_record = false;
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
        );
        $this->assertDomEqual(
            form('post', $this->post, array('controller' => 'posts')),
            '<form method="post" action="/posts/update">
            <input type="hidden" name="post[id]" id="post_id" value="1" />
            <p><label for="post_title">Title</label>
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" /></p>
            <input type="submit" name="commit" value="Update" />
            </form>'
        );
    }
    
    public function test_form_with_errors()
    {
        $this->post->errors['title'] = 'Title can\'t be empty';
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
        );
        $this->assertDomEqual(
            form('post', $this->post, array('controller' => 'posts')),
            '<form method="post" action="/posts/create">
            <p><label for="post_title">Title</label>
            <div class="field-with-errors">
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" />
            </div>
            </p>
            <input type="submit" name="commit" value="Create" />
            </form>'
        );
    }
}

?>
