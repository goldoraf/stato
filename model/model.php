<?php

require_once('lib/database.class.php');
require_once('lib/recordset.class.php');

require_once('lib/drivers/abstractdriver.class.php');
require_once('lib/drivers/mysqldriver.class.php');

require_once('lib/attribute.class.php');
require_once('lib/validation.class.php');

require_once('lib/observable.class.php');
require_once('lib/mixins.class.php');

require_once('lib/record.class.php');
require_once('lib/activerecord.class.php');
require_once('lib/activestore.class.php');
require_once('lib/fixture.class.php');
require_once('lib/paginator.class.php');

//require_once('lib/mixins/listmixin.class.php');
//require_once('lib/mixins/filemixin.class.php');
//require_once('lib/mixins/imagemixin.class.php');

require_once('lib/filesystem/csv.class.php');
require_once('lib/filesystem/image.class.php');
require_once('lib/filesystem/folder.class.php');
require_once('lib/filesystem/dir.class.php');

require_once('lib/associations/associationproxy.class.php');
require_once('lib/associations/association.class.php');
require_once('lib/associations/associationcollection.class.php');
require_once('lib/associations/belongstoassociation.class.php');
require_once('lib/associations/onetooneassociation.class.php');
require_once('lib/associations/hasmanyassociation.class.php');
require_once('lib/associations/manytomanyassociation.class.php');

?>
