<?php

/**
 * Prince XML PHP interface
 * http://www.princexml.com 
 * 
 * USAGE :
 * $pdf = new PdfMaker();
 * $pdf->set_exe_path(STATO_APP_ROOT_PATH.'/lib/Prince/Engine/bin/prince.exe');
 * $pdf->add_stylesheets(STATO_APP_ROOT_PATH.'/public/styles/main.css');
 * $this->send_data(
 *      $pdf->pdf_from_string($this->render_to_string($this->template_path('home', 'index'))),
 *      array('file' => 'test.pdf', 'type' => 'application/pdf')
 * ); 
 */
class PdfMaker
{
    private $exe_path;
    private $stylesheets = '';
    
    public function set_exe_path($path)
    {
        $this->exe_path = $path;
    }
    
    public function add_stylesheets()
    {
        $sheets = func_get_args();
        foreach ($sheets as $sheet) $this->stylesheets.= " -s $sheet ";
    }
    
    public function command_line()
    {
        return $this->exe_path." --input=html --server "//."--log=#{@log_file} "
                              .$this->stylesheets.' --silent - -o -';
    }
    
    public function pdf_from_string($string)
    {
        $buffer = '';
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
        );
        $process = proc_open($this->command_line(), $descriptorspec, $pipes);
        if (is_resource($process))
        {
            fwrite($pipes[0], $string);
            fclose($pipes[0]);
            while($s = fgets($pipes[1], 1024)) $buffer.= $s;
            fclose($pipes[1]);
        }
        return $buffer;
    }
}

?>