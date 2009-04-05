<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class MockPost extends MockRecord
{
    protected $attributes = array('title', 'author', 'body', 'private', 'written_on');
}

class FormHelperTest extends StatoTestCase
{
    public function setUp()
    {
        $this->post = new MockPost();
        $this->post->title      = 'PHP for ever';
        $this->post->author     = 'GoldoRaf';
        $this->post->body       = 'PHP is a general-purpose scripting language...';
        $this->post->private    = true;
        $this->post->written_on = new SDate(2006, 3, 31);
    }
    
    public function test_textfield()
    {
        $this->assertDomEquals(
            text_field('post', 'title', $this->post),
            '<input id="post_title" name="post[title]" size="30" type="text" value="PHP for ever" />'
        );
        $this->assertDomEquals(
            password_field('post', 'title', $this->post),
            '<input id="post_title" name="post[title]" size="30" type="password" value="PHP for ever" />'
        );
        $this->assertDomEquals(
            file_field('post', 'title', $this->post),
            '<input id="post_title" name="post[title]" type="file" />'
        );
        $this->assertDomEquals(
            text_field('post', 'title', $this->post, array('size' => 35, 'maxlength' => 35)),
            '<input id="post_title" name="post[title]" size="35" maxlength="35" type="text" value="PHP for ever" />'
        );
        $this->assertDomEquals(
            text_field('post', 'title', $this->post, array('index' => 2)),
            '<input id="post_2_title" name="post[2][title]" size="30" type="text" value="PHP for ever" />'
        );
    }
    
    public function test_checkbox()
    {
        $this->assertDomEquals(
            check_box('post', 'private', $this->post),
            '<input name="post[private]" type="hidden" value="0" />
            <input checked="checked" id="post_private" name="post[private]" type="checkbox" value="1" />'
        );
        $this->assertDomEquals(
            check_box('post', 'private', $this->post, array(), 'on', 'off'),
            '<input name="post[private]" type="hidden" value="off" />
            <input checked="checked" id="post_private" name="post[private]" type="checkbox" value="on" />'
        );
        $this->post->private = false;
        $this->assertDomEquals(
            check_box('post', 'private', $this->post),
            '<input name="post[private]" type="hidden" value="0" />
            <input id="post_private" name="post[private]" type="checkbox" value="1" />'
        );
        $this->assertDomEquals(
            check_box('post', 'private', $this->post, array('checked' => 'checked')),
            '<input name="post[private]" type="hidden" value="0" />
            <input checked="checked" id="post_private" name="post[private]" type="checkbox" value="1" />'
        );
    }
    
    public function test_radiobutton()
    {
        $this->assertDomEquals(
            radio_button('post', 'title', $this->post, 'PHP for ever'),
            '<input checked="checked" id="post_title_php_for_ever" name="post[title]" type="radio" value="PHP for ever" />'
        );
        $this->assertDomEquals(
            radio_button('post', 'title', $this->post, 'Hello World'),
            '<input id="post_title_hello_world" name="post[title]" type="radio" value="Hello World" />'
        );
    }
    
    public function test_textarea()
    {
        $this->assertDomEquals(
            text_area('post', 'body', $this->post),
            '<textarea cols="40" id="post_body" name="post[body]" rows="20">PHP is a general-purpose scripting language...</textarea>'
        );
        $this->post->body = 'Hello <b>world</b>';
        $this->assertDomEquals(
            text_area('post', 'body', $this->post),
            '<textarea cols="40" id="post_body" name="post[body]" rows="20">Hello &lt;b&gt;world&lt;/b&gt;</textarea>'
        );
    }
}

