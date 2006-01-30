<?php

return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'   => 'English',
    'FORMAT_COUNTRY'    => 'United Kingdom',
    'FORMAT_CURRENCY'   => '$%s', // printf()
    'FORMAT_DATE'       => '%b %d, %Y', // strftime(): Mar 19, 2005
    'FORMAT_DATETIME'   => '%d %B %Y - %I:%M:%S',
    'FORMAT_TIME'       => '%r', // strftime: 12-hour am/pm
    
    // validation error messages
    'ERR_VALID_FORM'      => 'Please correct the noted errors :',
    'ERR_VALID_REQUIRED'  => '%s is required.',
    'ERR_VALID_UNIQUE'    => '%s is taken.',
    'ERR_VALID_FORMAT'    => '%s is invalid.',
    'ERR_VALID_LENGTH'    => '%s is too long or too short (%d characters required).',
    'ERR_VALID_MINLENGTH' => '%s is too short (min is %d characters).',
    'ERR_VALID_MAXLENGTH' => '%s is too long (max is %d characters).',
    'ERR_VALID_INCLUSION' => '%s is not included in the list.',
    'ERR_VALID_EXCLUSION' => '%s is reserved.',
    'ERR_VALID_CONFIRM'   => '%s doesn\'t match confirmation.',
    'ERR_VALID_ACCEPT'    => '%s must be accepted.',
    
    'ERR_UPLOAD_PREPEND'  => 'The %s file ',
    'ERR_UPLOAD_REQUIRED' => 'is required.',
    'ERR_UPLOAD_PARTIAL'  => 'has not been properly uploaded.',
    'ERR_UPLOAD_SAVE'     => 'could not be moved in upload dir.'
);
?>
