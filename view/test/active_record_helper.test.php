<?php

require_once(STATO_CORE_PATH.'/view/view.php');
require_once(STATO_CORE_PATH.'/model/lib/column.php');
require_once(STATO_CORE_PATH.'/common/lib/locale.php');

SLocale::initialize(false);

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

if (!class_exists('SUrlRewriter', false))
{
    class SUrlRewriter
    {
        public static function url_for($options)
        {
            return $options['action'];
        }
    }
}

class RecordHelperTest extends HelperTestCase
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
            <li><a href="#" onclick="Field.focus(\'post_title\'); return false;">Title can\'t be empty</a></li>
            </ul>
            </div>'
        );
        $this->assertDomEqual(
            error_message_for('post', $this->post, array('id' => 'bad-errors', 'header_tag' => 'h5')),
            '<div id="bad-errors" class="form-errors">
            <h5>Please correct the following errors :</h5>
            <ul>
            <li><a href="#" onclick="Field.focus(\'post_title\'); return false;">Title can\'t be empty</a></li>
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
            form('post', $this->post),
            '<form method="post" action="create">
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
            form('post', $this->post),
            '<form method="post" action="create">
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
            form('post', $this->post),
            '<form method="post" action="update">
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
            form('post', $this->post),
            '<form method="post" action="create">
            <p><label for="post_title">Title</label>
            <div class="field-with-errors">
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" />
            </div>
            </p>
            <input type="submit" name="commit" value="Create" />
            </form>'
        );
    }
    
    public function test_form_with_date()
    {
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
            'written_on' => new SColumn('written_on', 'date')
        );
        $this->assertDomEqual(
            form('post', $this->post),
            '<form method="post" action="create">
            <p><label for="post_title">Title</label>
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" /></p>
            <p><label for="post_written_on">Written on</label>
            <select name="post[written_on][year]">
            <option value="2002">2002</option>
            <option value="2003">2003</option><option value="2004">2004</option>
            <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
            <option value="2007">2007</option><option value="2008">2008</option>
            <option value="2009">2009</option><option value="2010">2010</option>
            <option value="2011">2011</option><option value="2012">2012</option>
            </select>
            <select name="post[written_on][month]">
            <option value="1">January</option><option value="2">February</option>
            <option value="3" selected="selected">March</option><option value="4">April</option>
            <option value="5">May</option><option value="6">June</option>
            <option value="7">July</option><option value="8">August</option>
            <option value="9">September</option><option value="10">October</option>
            <option value="11">November</option><option value="12">December</option>
            </select>
            <select name="post[written_on][day]">
            <option value="1">1</option><option value="2">2</option>
            <option value="3">3</option><option value="4">4</option>
            <option value="5">5</option><option value="6">6</option>
            <option value="7">7</option><option value="8">8</option>
            <option value="9">9</option><option value="10">10</option>
            <option value="11">11</option><option value="12">12</option>
            <option value="13">13</option><option value="14">14</option>
            <option value="15">15</option><option value="16">16</option>
            <option value="17">17</option><option value="18">18</option>
            <option value="19">19</option><option value="20">20</option>
            <option value="21">21</option><option value="22">22</option>
            <option value="23">23</option><option value="24">24</option>
            <option value="25">25</option><option value="26">26</option>
            <option value="27">27</option><option value="28">28</option>
            <option value="29">29</option><option value="30">30</option>
            <option value="31" selected="selected">31</option>
            </select>
            </p>
            <input type="submit" name="commit" value="Create" />
            </form>'
        );
    }
    
    public function test_form_with_date_time()
    {
        $this->post->written_on = new SDateTime(2006, 3, 31, 1, 29, 35);
        $this->post->content_attributes = array
        (
            'title' => new SColumn('title', 'string'),
            'written_on' => new SColumn('written_on', 'datetime')
        );
        $this->assertDomEqual(
            form('post', $this->post),
            '<form method="post" action="create">
            <p><label for="post_title">Title</label>
            <input type="text" name="post[title]" value="PHP for ever" id="post_title" size="30" /></p>
            <p><label for="post_written_on">Written on</label>
            <select name="post[written_on][year]">
            <option value="2002">2002</option>
            <option value="2003">2003</option><option value="2004">2004</option>
            <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
            <option value="2007">2007</option><option value="2008">2008</option>
            <option value="2009">2009</option><option value="2010">2010</option>
            <option value="2011">2011</option><option value="2012">2012</option>
            </select>
            <select name="post[written_on][month]">
            <option value="1">January</option><option value="2">February</option>
            <option value="3" selected="selected">March</option><option value="4">April</option>
            <option value="5">May</option><option value="6">June</option>
            <option value="7">July</option><option value="8">August</option>
            <option value="9">September</option><option value="10">October</option>
            <option value="11">November</option><option value="12">December</option>
            </select>
            <select name="post[written_on][day]">
            <option value="1">1</option><option value="2">2</option>
            <option value="3">3</option><option value="4">4</option>
            <option value="5">5</option><option value="6">6</option>
            <option value="7">7</option><option value="8">8</option>
            <option value="9">9</option><option value="10">10</option>
            <option value="11">11</option><option value="12">12</option>
            <option value="13">13</option><option value="14">14</option>
            <option value="15">15</option><option value="16">16</option>
            <option value="17">17</option><option value="18">18</option>
            <option value="19">19</option><option value="20">20</option>
            <option value="21">21</option><option value="22">22</option>
            <option value="23">23</option><option value="24">24</option>
            <option value="25">25</option><option value="26">26</option>
            <option value="27">27</option><option value="28">28</option>
            <option value="29">29</option><option value="30">30</option>
            <option value="31" selected="selected">31</option>
            </select>
            <select name="post[written_on][hour]">
            <option value="00">00</option>
            <option value="01" selected="selected">01</option><option value="02">02</option>
            <option value="03">03</option><option value="04">04</option>
            <option value="05">05</option><option value="06">06</option>
            <option value="07">07</option><option value="08">08</option>
            <option value="09">09</option><option value="10">10</option>
            <option value="11">11</option><option value="12">12</option>
            <option value="13">13</option><option value="14">14</option>
            <option value="15">15</option><option value="16">16</option>
            <option value="17">17</option><option value="18">18</option>
            <option value="19">19</option><option value="20">20</option>
            <option value="21">21</option><option value="22">22</option>
            <option value="23">23</option>
            </select>
            <select name="post[written_on][min]">
            <option value="00">00</option>
            <option value="01">01</option><option value="02">02</option><option value="03">03</option>
            <option value="04">04</option><option value="05">05</option><option value="06">06</option>
            <option value="07">07</option><option value="08">08</option><option value="09">09</option>
            <option value="10">10</option><option value="11">11</option><option value="12">12</option>
            <option value="13">13</option><option value="14">14</option><option value="15">15</option>
            <option value="16">16</option><option value="17">17</option><option value="18">18</option>
            <option value="19">19</option><option value="20">20</option><option value="21">21</option>
            <option value="22">22</option><option value="23">23</option><option value="24">24</option>
            <option value="25">25</option><option value="26">26</option><option value="27">27</option>
            <option value="28">28</option><option value="29" selected="selected">29</option><option value="30">30</option>
            <option value="31">31</option><option value="32">32</option><option value="33">33</option>
            <option value="34">34</option><option value="35">35</option><option value="36">36</option>
            <option value="37">37</option><option value="38">38</option><option value="39">39</option>
            <option value="40">40</option><option value="41">41</option><option value="42">42</option>
            <option value="43">43</option><option value="44">44</option><option value="45">45</option>
            <option value="46">46</option><option value="47">47</option><option value="48">48</option>
            <option value="49">49</option><option value="50">50</option><option value="51">51</option>
            <option value="52">52</option><option value="53">53</option><option value="54">54</option>
            <option value="55">55</option><option value="56">56</option><option value="57">57</option>
            <option value="58">58</option><option value="59">59</option>
            </select>
            <select name="post[written_on][sec]">
            <option value="00">00</option>
            <option value="01">01</option><option value="02">02</option><option value="03">03</option>
            <option value="04">04</option><option value="05">05</option><option value="06">06</option>
            <option value="07">07</option><option value="08">08</option><option value="09">09</option>
            <option value="10">10</option><option value="11">11</option><option value="12">12</option>
            <option value="13">13</option><option value="14">14</option><option value="15">15</option>
            <option value="16">16</option><option value="17">17</option><option value="18">18</option>
            <option value="19">19</option><option value="20">20</option><option value="21">21</option>
            <option value="22">22</option><option value="23">23</option><option value="24">24</option>
            <option value="25">25</option><option value="26">26</option><option value="27">27</option>
            <option value="28">28</option><option value="29">29</option><option value="30">30</option>
            <option value="31">31</option><option value="32">32</option><option value="33">33</option>
            <option value="34">34</option><option value="35" selected="selected">35</option><option value="36">36</option>
            <option value="37">37</option><option value="38">38</option><option value="39">39</option>
            <option value="40">40</option><option value="41">41</option><option value="42">42</option>
            <option value="43">43</option><option value="44">44</option><option value="45">45</option>
            <option value="46">46</option><option value="47">47</option><option value="48">48</option>
            <option value="49">49</option><option value="50">50</option><option value="51">51</option>
            <option value="52">52</option><option value="53">53</option><option value="54">54</option>
            <option value="55">55</option><option value="56">56</option><option value="57">57</option>
            <option value="58">58</option><option value="59">59</option>
            </select>
            </p>
            <input type="submit" name="commit" value="Create" />
            </form>'
        );
    }
}

?>
