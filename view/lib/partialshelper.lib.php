<?php

function render_partial($partialPath, $localAssigns = Null)
{
    list($path, $partial) = partial_pieces($partialPath);
    $template = SContext::inclusionPath()."/views/$path/_$partial.php";
    
    if ($localAssigns == Null)
        $localAssigns = array($partial => SContext::$response->values[$partial]);
    
    $renderer = new SRenderer($template, $localAssigns);
    return $renderer->render();
}

function render_partial_collection($partialPath, $collection, $spacerTemplate = Null)
{
    list($path, $partial) = partial_pieces($partialPath);
    $template = SContext::inclusionPath()."/views/$path/_$partial.php";
    $partialsCollec = array();
    $counterName = $partial.'_counter';
    $counter = 1;
    foreach($collection as $element)
    {
        $localAssigns[$counterName] = $counter;
        $localAssigns[$partial] = $element;
        $renderer = new SRenderer($template, $localAssigns);
        $partialsCollec[] = $renderer->render();
        $counter++;
    }
    return implode('', $partialsCollec);
}

function partial_pieces($partialPath)
{
    if (!strpos($partialPath, '/'))
        return array(SContext::$request->controller, $partialPath);
    else
        return explode('/', $partialPath);
}

?>
