<?php

require('lib/column.php');
require('lib/table.php');
require('lib/validation.php');
require('lib/observable.php');
require('lib/mapper.php');
require('lib/table_map.php');
require('lib/active_record.php');
require('lib/manager.php');
require('lib/query_set.php');
require('lib/fixture.php');
require('lib/migration.php');
require('lib/paginator.php');
require('lib/csv.php');

require('lib/decorators/base_decorator.php');
require('lib/decorators/list_decorator.php');
require('lib/decorators/tree_decorator.php');

require('lib/adapters/abstract.php');
require('lib/adapters/mysql.php');
require('lib/adapters/library_wrappers/mysql.php');
require('lib/adapters/library_wrappers/pdo_mysql.php');

require('lib/associations/association.php');
require('lib/associations/belongs_to_association.php');
require('lib/associations/has_one_association.php');
require('lib/associations/has_many_association.php');
require('lib/associations/has_many_through_association.php');
require('lib/associations/many_to_many_association.php');

?>
