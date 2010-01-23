<?php



class Stato_Mailer_Mime_Multipart extends Stato_Mailer_Mime_Entity
{
    const MIXED = 'mixed';
    
    const RELATED = 'related';
    
    const ALTERNATIVE = 'alternative';
    
    protected $subtype;
    
    protected $parts;
    
    protected $boundary;
    
    protected $preamble;
    
    public function __construct($subtype = self::ALTERNATIVE, $parts = array(), $boundary = null)
    {
        parent::__construct();
        $this->subtype = $subtype;
        $this->parts = $parts;
        $this->preamble = 'This is a multi-part message in MIME format.';
        $this->setBoundary($boundary);
    }
    
    public function getContent()
    {
        $body = $this->preamble;
        
        foreach ($this->parts as $part)
            $body.= $this->boundaryLine().$part->__toString();
            
        return $body.$this->boundaryEnd();
    }
    
    public function addPart($part)
    {
        if ($part instanceof Stato_Mailer_Mime_Part || $part instanceof Stato_Mailer_Mime_Multipart)
            $this->parts[] = $part;
        else
            throw new Stato_Mailer_Mime_Exception('Parts added to a Stato_Mailer_Mime_Multipart must be Stato_Mailer_Mime_Part or Stato_Mailer_Mime_Multipart instances');
    }
    
    public function setSubtype($subtype)
    {
        $this->subtype = $subtype;
    }
    
    public function setBoundary($boundary = null)
    {
        if ($boundary === null) $boundary = md5(uniqid(time()));
        $this->boundary = $boundary;
        $this->setHeader('Content-Type', "multipart/{$this->subtype}; boundary=\"{$this->boundary}\"", false);
    }
    
    public function setPreamble($preamble)
    {
        $this->preamble = $preamble;
    }
    
    public function getSubtype()
    {
        return $this->subtype;
    }
    
    protected function boundaryLine()
    {
        return $this->eol.'--'.$this->boundary.$this->eol;
    }
    
    protected function boundaryEnd()
    {
        return $this->eol.'--'.$this->boundary.'--';
    }
}