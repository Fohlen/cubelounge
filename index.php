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

// Custom error page
$f3->set('ONERROR',
		function($f3) {
			$f3->set('content','404.htm');
			echo View::instance()->render('layout.htm');
		}
);

// Routing scheme. Set the news page as our index and route all requests to their corresponding controllers
// @controller -> @action (@param, optionally)
$f3->route('GET|POST /', 'Controllers\News->index');
$f3->route('GET|POST /@controller', 'Controllers\@controller->index');
$f3->route('GET|POST /@controller/@action', 'Controllers\@controller->@action');
$f3->route('GET|POST /@controller/@action/@param', 'Controllers\@controller->@action');

// Deny access to the dashboard if not logged in
$f3->route('GET|POST /login', function($f3){
	if (Helpers\User::instance()->isLoggedIn()) {
		$dash = new Controllers\Login();
		$dash->index();
	} else {
		$f3->error(404);
	}
});

// Command line cronjobs.
$f3->route('GET /command',
		function($f3) {			
			if (php_sapi_name() == 'cli') 
			{
				foreach (new DirectoryIterator('app/commands') as $Command) {
					if($Command->isDot()) continue;
					$commandName = "Commands\\" . $Command->getBasename('.php');
					$task = new $commandName;
					$task->run();
				}
			} else {
				$f3->error(404);
			}
		}
);

// Initialize user sessions
new Session();

$f3->run();
