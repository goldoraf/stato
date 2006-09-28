<?php

require('lib/recordset.php');
require('lib/attribute.php');
require('lib/definitions.php');
require('lib/validation.php');
require('lib/observable.php');
require('lib/record.php');
require('lib/active_record.php');
require('lib/active_store.php');
require('lib/fixture.php');
require('lib/migration.php');
require('lib/paginator.php');

require('lib/decorators/base_decorator.php');
require('lib/decorators/list_decorator.php');

require('lib/drivers/abstract_driver.php');
require('lib/drivers/mysql_driver.php');

require('lib/filesystem/csv.php');
require('lib/filesystem/folder.php');
require('lib/filesystem/dir.php');
if (extension_loaded('gd')) require('lib/filesystem/image.php');
if (extension_loaded('zip')) require('lib/filesystem/zip.php');

require('lib/associations/association_proxy.php');
require('lib/associations/association.php');
require('lib/associations/association_collection.php');
require('lib/associations/belongs_to_association.php');
require('lib/associations/has_one_association.php');
require('lib/associations/has_many_association.php');
require('lib/associations/many_to_many_association.php');

?>
