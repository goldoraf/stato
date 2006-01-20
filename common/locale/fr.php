<?php

return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'   => 'Français',
    'FORMAT_COUNTRY'    => 'France',
    'FORMAT_CURRENCY'   => '%s €', // printf()
    'FORMAT_DATE'       => '%d %B %Y', // strftime(): 19 mars 2005
    'FORMAT_TIME'       => '%R', // strftime: heure au format 24
    
    // operation actions
    'OP_SAVE'       => 'Enregistrer',
    'OP_PREVIEW'    => 'Prévisualiser',
    'OP_CANCEL'     => 'Annuler',
    'OP_DELETE'     => 'Supprimer',
    'OP_RESET'      => 'Effacer',
    'OP_NEXT'       => 'Suivant',
    'OP_PREVIOUS'   => 'Précédent',
    'OP_SEARCH'     => 'Rechercher',
    'OP_GO'         => 'Valider',
    
    // error messages
    'ERR_FILE_FIND' => 'Cannot find file.',
    'ERR_FILE_OPEN' => 'Cannot open file.',
    'ERR_FILE_READ' => 'Cannot read file.',
    'ERR_EXTENSION' => 'Extension not loaded.',
    'ERR_CONNECT'   => 'Connection failed.',
    'ERR_INVALID'   => 'Invalid data.',
    
    // validation error messages
    'ERR_VALID_FORM'      => 'Veuillez renseigner ou corriger les champs ci-dessous :',
    'ERR_VALID_REQUIRED'  => 'Veuillez spécifier un(e) %s.',
    'ERR_VALID_UNIQUE'    => 'Ce %s est déjà pris.',
    'ERR_VALID_FORMAT'    => '%s n\'est pas valide.',
    'ERR_VALID_LENGTH'    => 'Votre %s doit contenir %d caractères.',
    'ERR_VALID_MINLENGTH' => 'Votre %s doit contenir au moins %d caractères.',
    'ERR_VALID_MAXLENGTH' => 'Votre %s doit contenir moins de %d caractères.',
    'ERR_VALID_INCLUSION' => 'Votre %s ne fait pas partie de la liste.',
    'ERR_VALID_EXCLUSION' => 'Ce %s est réservé.',
    'ERR_VALID_CONFIRM'   => 'Votre %s doit être confirmé.',
    'ERR_VALID_ACCEPT'    => 'Le %s doit être accepté.',
    
    'ERR_UPLOAD_PREPEND'  => 'The %s file ',
    'ERR_UPLOAD_REQUIRED' => 'is required.',
    'ERR_UPLOAD_PARTIAL'  => 'has not been properly uploaded.',
    'ERR_UPLOAD_SAVE'     => 'could not be moved in upload dir.'
);
?>
