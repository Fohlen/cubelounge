<?php

// Kickstart the framework
$f3=require('lib/base.php');

// Fire up composer
require_once('vendor/autoload.php');

if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');

// Prepare the database
$dbConfig = $f3->get('database');
if (!isset($dbConfig['port'])) 
	$dbConfig['port'] = 3306;

$f3->set('DB', new DB\SQL(
	'mysql:host='.$dbConfig['host'].';port='.$dbConfig['port'].';dbname='.$dbConfig['database'],
	$dbConfig['user'],
	$dbConfig['password']
));

$f3->route('GET /',
	function($f3) {
		$f3->set('content','welcome.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->route('GET /commands',
		function($f3) {
			foreach (new DirectoryIterator('app/commands') as $Command) {
				if($Command->isDot()) continue;
				$commandName = "Commands\\" . $Command->getBasename('.php');
				$task = new $commandName;
				$task->run();
			}
		}
);

$f3->run();
