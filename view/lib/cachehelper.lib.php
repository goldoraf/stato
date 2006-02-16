<?php

function fragment_cache_start($id, $lifetime = 30)
{
    if (is_fragment_cache_valid($id, $lifetime))
    {
        echo file_get_contents(fragment_cache_path($id));
        return true;
    }
    ob_start();
    //ob_implicit_flush(false); necessary ?
    return false;
}

function fragment_cache_end($id)
{
    $str = ob_get_contents();
    ob_end_clean();
    file_put_contents(fragment_cache_path($id), 'Fragment cached !'.$str.'Fragment cached !');
    echo $str;
}

function is_fragment_cache_valid($id, $lifetime)
{
    if (file_exists(fragment_cache_path($id))) return true;
    return false;
}

function fragment_cache_path($id)
{
    return ROOT_DIR."/cache/fragments/{$id}";
}

?>
