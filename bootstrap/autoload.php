<?php

define('LARAVEL_START', microtime(true));

$helperPath = __DIR__.'/../app/Support/helper.php';

if (!file_exists($helperPath)) {
	echo 'Missing vendor files, try running "composer install" or use the Wizard installer.'.PHP_EOL;
	exit(1);
}

require $helperPath;

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';
