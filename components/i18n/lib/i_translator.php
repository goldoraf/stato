<?php

interface SITranslator
{
    public function fetch($key, SLocale $locale, $plural_number = null);
}

?>
