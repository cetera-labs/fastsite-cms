<?php
/**
 * 
 *
 * @version $Id$
 * @copyright 2007 
 **/

set_time_limit(99999);
define('DOCROOT', __DIR__.'/../');
require('include/common.php');
$application->connectDb();
$application->initPlugins();
$application->cronJob(DOCROOT.'../logs/cron.log');

foreach ($application->getCronJobs() as $file) {

	if (is_callable($file)) {
		$file();
	}
	elseif (is_string($file) && file_exists($file)) {
        include_once($file);
	}

}