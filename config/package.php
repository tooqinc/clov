<?php  defined('C5_EXECUTE') or die('Access Denied.');

// Note that this file is only used during Clov's installation. Changing these 
// values after install will have no effect.


// These stylesheets will be included on every Clov page. See clov/css/.
define('STYLESHEETS', serialize(array(
	'basic.css',
	'bling.css',
)));

// Rules used to format money amounts.
define('LOCALECONV_FILE', __DIR__.'/localeconv.utf8.php');
define('MONEY_AMOUNT_LOCALE', 'en_US');

// These will be used to categorize timesheet entries. Also projects will have 
// a separate budget line for each time code.
define('DEFAULT_TIME_CODES', serialize(array(
	'100 Design',
	'110 Execution',
	'120 Testing',
	'200 Admin',
	'300 Other',
)));
