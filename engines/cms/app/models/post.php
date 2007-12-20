<?php

class PostManager extends SManager
{
    public function find_by_permalink($year, $month, $day, $title)
    {
        list($from, $to) = $this->time_delta($year, $month, $day);
        return $this->get_or_404(
            'permalink = ?', 'created_on BETWEEN ? AND ?',
            array($title, $from, $to)
        );
    }
    
    public function find_latest($limit = 20)
    {
        return $this->filter("published = '1'")
                    ->limit($limit)
                    ->order_by('-created_on');
    }
    
    private function time_delta($year, $month, $day)
    {
        $from = new SDateTime($year, $month, $day);
        $to   = $from->step(1);
        return array($from, $to);
    }
}

class Post extends SActiveRecord
{
    public static $objects;
    
    public function before_save()
    {
        if ($this->permalink == '' || $this->permalink === null)
            $this->permalink = SInflection::urlize($this->title);
    }
}

?>
