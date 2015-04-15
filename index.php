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

// Automatically route /controller/page to /controller/page/0
$f3->route('GET /@controller/page', function($f3) {
	$f3->reroute('/'.$f3->get('PARAMS.controller').'/page/0');
});

// Registered user routes. Automatically route to dashboard once logged in.
/*$f3->route('GET|POST /login/signup', function($f3) {
	if (null !== Base::instance()->get('SESSION.uid')) {
		$f3->reroute('/login');
	} else {
		$login = new Controllers\Login();
		$login->signup();	
	}
});

$f3->route('GET|POST /login/signin', function($f3) {
	if (null !== Base::instance()->get('SESSION.uid')) {
		$f3->reroute('/login');
	} else {
		$login = new Controllers\Login();
		$login->signin();
	}
});*/

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
