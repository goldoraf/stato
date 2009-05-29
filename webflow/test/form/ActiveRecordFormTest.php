<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class ArticleForm extends SActiveRecordForm
{
    protected $class = 'Article';
}

class ArticleFormWithOverride extends SActiveRecordForm
{
    protected $class = 'Article';
    
    public function __construct($data = null, $files = null)
    {
        parent::__construct($data, $files);
        $this->title = new STextField;
    }
}

class ArticleFormWithSubset extends SActiveRecordForm
{
    protected $class = 'Article';
    protected $include = array('title', 'text');
}


class SActiveRecordFormTest extends ActiveTestCase
{
    public $models = array('article');
    public $fixtures = array('articles', 'comments', 'categories', 'articles_categories', 'developers');
    
    public function test_collection_choice_field_rendering()
    {
        $html = <<<EOT
<select name="article_id">
<option value="1">PHP6 overview</option>
<option value="2">PHP5 and observer pattern</option>
</select>
EOT;
        $field = new SCollectionChoiceField(Article::$objects->all(), array('text_property' => 'title'));
        $this->assertEquals($html, $field->render('article_id'));
    }
    
    public function test_collection_choice_field_cleaning()
    {
        $field = new SCollectionChoiceField(Article::$objects->all(), array('text_property' => 'title'));
        $value = $field->clean(2);
        $this->assertTrue($value instanceof Article);
    }
    
    public function test_collection_choice_field_validation_error()
    {
        $this->setExpectedException('SValidationError');
        $field = new SCollectionChoiceField(Article::$objects->all(), array('text_property' => 'title'));
        $field->clean(99);
    }
    
    public function test_collection_multiple_choice_field_rendering()
    {
        $html = <<<EOT
<select multiple="multiple" name="article_id[]">
<option value="1">PHP6 overview</option>
<option value="2">PHP5 and observer pattern</option>
</select>
EOT;
        $field = new SCollectionMultipleChoiceField(Article::$objects->all(), array('text_property' => 'title'));
        $this->assertEquals($html, $field->render('article_id'));
    }
    
    public function test_collection_multiple_choice_field_cleaning()
    {
        $field = new SCollectionMultipleChoiceField(Article::$objects->all(), array('text_property' => 'title'));
        $value = $field->clean(array(1,2));
        $this->assertTrue(is_array($value));
        $this->assertTrue($value[1] instanceof Article);
        $this->assertTrue($value[2] instanceof Article);
    }
    
    public function test_collection_multiple_choice_field_validation_error()
    {
        $this->setExpectedException('SValidationError');
        $field = new SCollectionMultipleChoiceField(Article::$objects->all(), array('text_property' => 'title'));
        $field->clean(array(1,99));
    }
    
    public function test_columns_to_fields()
    {
        $categories = <<<EOT
<select multiple="multiple" name="article[categories][]" id="article_categories">
<option value="1">PHP</option>
<option value="2">XUL</option>
<option value="3">Open Source</option>
<option value="4">MySQL</option>
<option value="5">Dev</option>
<option value="6">Mozilla</option>
</select>
EOT;
        $form = new ArticleForm;
        $this->assertEquals('<input type="text" name="article[title]" id="article_title" />', $form->title->render());
        $this->assertEquals('<textarea name="article[text]" cols="40" rows="10" id="article_text"></textarea>', $form->text->render());
        $this->assertEquals($categories, $form->categories->render());
    }
    
    public function test_field_override()
    {
        $form = new ArticleFormWithOverride;
        $this->assertEquals('<textarea name="article[title]" cols="40" rows="10" id="article_title"></textarea>', $form->title->render());
    }
    
    public function test_field_subset()
    {
        $form = new ArticleFormWithSubset;
        $this->assertEquals('<p><label for="article_title">Title</label><input type="text" name="article[title]" id="article_title" /></p>
<p><label for="article_text">Text</label><textarea name="article[text]" cols="40" rows="10" id="article_text"></textarea></p>', $form->render());
    }
    
    public function test_form_with_instance()
    {
        $categories = <<<EOT
<select multiple="multiple" name="article[categories][]" id="article_categories">
<option value="1" selected="selected">PHP</option>
<option value="2">XUL</option>
<option value="3" selected="selected">Open Source</option>
<option value="4">MySQL</option>
<option value="5" selected="selected">Dev</option>
<option value="6">Mozilla</option>
</select>
EOT;
        $authors = <<<EOT
<select name="article[author]" id="article_author">
<option value="1" selected="selected">ben</option>
<option value="2">richard</option>
</select>
EOT;
        $form = new ArticleForm(Article::$objects->get(1));
        $this->assertEquals('<input type="hidden" name="article[id]" id="article_id" value="1" />', $form->id->render());
        $this->assertEquals('<input type="text" name="article[title]" id="article_title" value="PHP6 overview" />', $form->title->render());
        $this->assertEquals($categories, $form->categories->render());
        $this->assertEquals($authors, $form->author->render());
    }
}