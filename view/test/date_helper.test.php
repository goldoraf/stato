<?php

require_once(CORE_DIR.'/view/view.php');

SLocale::$language = 'en_US';
SLocale::setLocale();

class MockArticle
{
    private $attributes = array('written_on');
    private $values = array();
    
    public function __set($key, $value)
    {
        if (in_array($key, $this->attributes)) $this->values[$key] = $value;
    }
    
    public function __get($key)
    {
        if (isset($this->values[$key])) return $this->values[$key];
        else return null;
    }
}

class DateHelperTest extends HelperTestCase
{
    public function testSelectDay()
    {
        $expect = <<<EOT
        <select name="date[day]">
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
EOT;
        $this->assertDomEqual($expect, select_day(new SDate(2006, 3, 31)));
        $this->assertDomEqual($expect, select_day(31));
    }
    
    public function testSelectDayWithBlank()
    {
        $expect = <<<EOT
        <select name="date[day]">
        <option value=""></option>
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
EOT;
        $this->assertDomEqual($expect, select_day(new SDate(2006, 3, 31), array('include_blank' => true)));
        $this->assertDomEqual($expect, select_day(31, array('include_blank' => true)));
    }
    
    public function testSelectDayNull()
    {
        $expect = <<<EOT
        <select name="date[day]">
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
        <option value="31">31</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_day(null));
    }
    
    public function testSelectMonth()
    {
        $expect = <<<EOT
        <select name="date[month]">
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(new SDate(2006, 3, 31)));
        $this->assertDomEqual($expect, select_month(3));
    }
    
    public function testSelectMonthWithBlank()
    {
        $expect = <<<EOT
        <select name="date[month]">
        <option value=""></option>
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(new SDate(2006, 3, 31), array('include_blank' => true)));
        $this->assertDomEqual($expect, select_month(3, array('include_blank' => true)));
    }
    
    public function testSelectMonthWithDisabled()
    {
        $expect = <<<EOT
        <select name="date[month]" disabled="disabled">
        <option value=""></option>
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(3, array('include_blank' => true, 'disabled' => true)));
    }
    
    public function testSelectMonthNull()
    {
        $expect = <<<EOT
        <select name="date[month]">
        <option value="1">January</option><option value="2">February</option>
        <option value="3">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(null));
    }
    
    public function testSelectMonthWithNumbers()
    {
        $expect = <<<EOT
        <select name="date[month]">
        <option value="1">1</option><option value="2">2</option>
        <option value="3" selected="selected">3</option><option value="4">4</option>
        <option value="5">5</option><option value="6">6</option>
        <option value="7">7</option><option value="8">8</option>
        <option value="9">9</option><option value="10">10</option>
        <option value="11">11</option><option value="12">12</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(3, array('use_numbers' => true)));
    }
    
    public function testSelectMonthWithNumbersAndNames()
    {
        $expect = <<<EOT
        <select name="date[month]">
        <option value="1">1 - January</option><option value="2">2 - February</option>
        <option value="3" selected="selected">3 - March</option><option value="4">4 - April</option>
        <option value="5">5 - May</option><option value="6">6 - June</option>
        <option value="7">7 - July</option><option value="8">8 - August</option>
        <option value="9">9 - September</option><option value="10">10 - October</option>
        <option value="11">11 - November</option><option value="12">12 - December</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(3, array('add_numbers' => true)));
    }
    
    public function testSelectMonthWithAbbrvs()
    {
        $expect = <<<EOT
        <select name="date[month]">
        <option value="1">Jan</option><option value="2">Feb</option>
        <option value="3" selected="selected">Mar</option><option value="4">Apr</option>
        <option value="5">May</option><option value="6">Jun</option>
        <option value="7">Jul</option><option value="8">Aug</option>
        <option value="9">Sep</option><option value="10">Oct</option>
        <option value="11">Nov</option><option value="12">Dec</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(3, array('use_abbrv' => true)));
    }
    
    public function testSelectMonthWithAbbrvsAndNames()
    {
        $expect = <<<EOT
        <select name="date[month]">
        <option value="1">1 - Jan</option><option value="2">2 - Feb</option>
        <option value="3" selected="selected">3 - Mar</option><option value="4">4 - Apr</option>
        <option value="5">5 - May</option><option value="6">6 - Jun</option>
        <option value="7">7 - Jul</option><option value="8">8 - Aug</option>
        <option value="9">9 - Sep</option><option value="10">10 - Oct</option>
        <option value="11">11 - Nov</option><option value="12">12 - Dec</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_month(3, array('use_abbrv' => true, 'add_numbers' => true)));
    }
    
    public function testSelectYear()
    {
        $expect = <<<EOT
        <select name="date[year]">
        <option value="2001">2001</option><option value="2002">2002</option>
        <option value="2003">2003</option><option value="2004">2004</option>
        <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
        <option value="2007">2007</option><option value="2008">2008</option>
        <option value="2009">2009</option><option value="2010">2010</option>
        <option value="2011">2011</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_year(new SDate(2006, 3, 31)));
        $this->assertDomEqual($expect, select_year(2006));
    }
    
    public function testSelectYearWithLimits()
    {
        $expect = <<<EOT
        <select name="date[year]">
        <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
        <option value="2007">2007</option><option value="2008">2008</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_year(new SDate(2006, 3, 31), array('start_year' => 2005, 'end_year' => 2008)));
        $this->assertDomEqual($expect, select_year(2006, array('start_year' => 2005, 'end_year' => 2008)));
    }
    
    public function testSelectYearReverse()
    {
        $expect = <<<EOT
        <select name="date[year]">
        <option value="2008">2008</option><option value="2007">2007</option>
        <option value="2006" selected="selected">2006</option><option value="2005">2005</option>
        </select>
EOT;
        $this->assertDomEqual($expect, select_year(new SDate(2006, 3, 31), array('start_year' => 2008, 'end_year' => 2005)));
    }
    
    public function testSelectHour()
    {
        $expect = <<<EOT
        <select name="date[hour]">
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
EOT;
        $this->assertDomEqual($expect, select_hour(new SDateTime(2006, 3, 31, 1, 29, 35)));
        $this->assertDomEqual($expect, select_hour(1));
    }
    
    public function testSelectMinute()
    {
        $expect = <<<EOT
        <select name="date[min]">
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
EOT;
        $this->assertDomEqual($expect, select_minute(new SDateTime(2006, 3, 31, 1, 29, 35)));
        $this->assertDomEqual($expect, select_minute(29));
    }
    
    public function testSelectSecond()
    {
        $expect = <<<EOT
        <select name="date[sec]">
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
EOT;
        $this->assertDomEqual($expect, select_second(new SDateTime(2006, 3, 31, 1, 29, 35)));
        $this->assertDomEqual($expect, select_second(35));
    }
    
    public function testSelectDate()
    {
        $expect = <<<EOT
        <select name="date[test][year]">
        <option value="2001">2001</option><option value="2002">2002</option>
        <option value="2003">2003</option><option value="2004">2004</option>
        <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
        <option value="2007">2007</option><option value="2008">2008</option>
        <option value="2009">2009</option><option value="2010">2010</option>
        <option value="2011">2011</option>
        </select>
        <select name="date[test][month]">
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
        <select name="date[test][day]">
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
EOT;
        $this->assertDomEqual($expect, select_date(new SDate(2006, 3, 31), array('prefix' => 'date[test]')));
    }
    
    public function testDateSelect()
    {
        $article = new MockArticle();
        $article->written_on = new SDate(2006, 3, 31);
        
        $expect = <<<EOT
        <select name="article[written_on][year]">
        <option value="2001">2001</option><option value="2002">2002</option>
        <option value="2003">2003</option><option value="2004">2004</option>
        <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
        <option value="2007">2007</option><option value="2008">2008</option>
        <option value="2009">2009</option><option value="2010">2010</option>
        <option value="2011">2011</option>
        </select>
        <select name="article[written_on][month]">
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
        <select name="article[written_on][day]">
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
EOT;
        $this->assertDomEqual($expect, date_select('article', 'written_on', $article));
    }
    
    public function testDateSelectWithOrder()
    {
        $article = new MockArticle();
        $article->written_on = new SDate(2006, 3, 31);
        
        $expect = <<<EOT
        <select name="article[written_on][day]">
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
        <select name="article[written_on][month]">
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
        <select name="article[written_on][year]">
        <option value="2001">2001</option><option value="2002">2002</option>
        <option value="2003">2003</option><option value="2004">2004</option>
        <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
        <option value="2007">2007</option><option value="2008">2008</option>
        <option value="2009">2009</option><option value="2010">2010</option>
        <option value="2011">2011</option>
        </select>
EOT;
        $this->assertDomEqual($expect, date_select('article', 'written_on', $article, array('order' => array('day', 'month', 'year'))));
    }
    
    public function testDateSelectWithBlankAndYearLimits()
    {
        $article = new MockArticle();
        $article->written_on = new SDate(2006, 3, 31);
        
        $expect = <<<EOT
        <select name="article[written_on][year]">
        <option value=""></option>
        <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
        <option value="2007">2007</option><option value="2008">2008</option>
        </select>
        <select name="article[written_on][month]">
        <option value=""></option>
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
        <select name="article[written_on][day]">
        <option value=""></option>
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
EOT;
        $this->assertDomEqual($expect, date_select('article', 'written_on', $article, 
            array('include_blank' => true, 'start_year' => 2005, 'end_year' => 2008)));
    }
    
    public function testDateTimeSelect()
    {
        $article = new MockArticle();
        $article->written_on = new SDateTime(2006, 3, 31, 1, 29, 35);
        
        $expect = <<<EOT
        <select name="article[written_on][year]">
        <option value="2001">2001</option><option value="2002">2002</option>
        <option value="2003">2003</option><option value="2004">2004</option>
        <option value="2005">2005</option><option value="2006" selected="selected">2006</option>
        <option value="2007">2007</option><option value="2008">2008</option>
        <option value="2009">2009</option><option value="2010">2010</option>
        <option value="2011">2011</option>
        </select>
        <select name="article[written_on][month]">
        <option value="1">January</option><option value="2">February</option>
        <option value="3" selected="selected">March</option><option value="4">April</option>
        <option value="5">May</option><option value="6">June</option>
        <option value="7">July</option><option value="8">August</option>
        <option value="9">September</option><option value="10">October</option>
        <option value="11">November</option><option value="12">December</option>
        </select>
        <select name="article[written_on][day]">
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
        <select name="article[written_on][hour]">
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
        <select name="article[written_on][min]">
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
        <select name="article[written_on][sec]">
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
EOT;
        $this->assertDomEqual($expect, date_time_select('article', 'written_on', $article));
    }
}

?>
