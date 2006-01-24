<?php

function render_partial($partialPath, $localAssigns = Null)
{
    list($path, $partial) = partial_pieces($partialPath);
    $template = Context::inclusionPath()."/views/$path/_$partial.php";
    $localAssigns = Context::$response->values;
    $renderer = new Renderer($template, $localAssigns);
    return $renderer->render();
}

function render_partial_collection($partialPath, $collection, $spacerTemplate = Null)
{
    list($path, $partial) = partial_pieces($partialPath);
    $template = Context::inclusionPath()."/views/$path/_$partial.php";
    $partialsCollec = array();
    $counterName = $partial.'_counter';
    $counter = 0;
    foreach($collection as $element)
    {
        $localAssigns[$counterName] = $counter;
        $localAssigns[$partial] = $element;
        $renderer = new Renderer($template, $localAssigns);
        $partialsCollec[] = $renderer->render();
    }
    return implode('', $partialsCollec);
}

function partial_pieces($partialPath)
{
    if (!strpos($partialPath, '/'))
        return array(Context::$request->controller, $partialPath);
    else
        return explode('/', $partialPath);
}

?>
