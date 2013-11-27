<?php
/* temp */
$time_start = microtime(true);
require_once 'constants.php';
require_once 'app/ibbController.php';
require_once IBB_LIB_PATH . '/PHPTAL-1.2.2/PHPTAL.php';
require_once dirname(IBB_ROOT_PATH) . '/config.php';
require_once IBB_ROOT_PATH . '/classes/furl_class.php';
require_once IBB_ROOT_PATH . '/classes/loader_class.php';
//require_once IBB_ROOT_PATH . 'app/ibbCore.php';
// TODO BASIC Exception handler
//echo 'god index req_once<br />';
session_start();
/* temp */

/* temporary catchall for exceptions until exceptions are prepped*/
try
{
	ibbController::run();
} catch (Exception $e)
{
	echo($e->getMessage().'<pre>'.$e->getTraceAsString().'</pre>');

	while ($e = $e->getPrevious())
	{
		echo('Caused by: ' . $e->getMessage().'<pre>'.$e->getTraceAsString().'</pre>');
	}
}

// debugging & efficiency, temp
$time_end = microtime(true);

$execution_time = $time_end - $time_start;

echo '<b style="color:black;">Total Execution Time:</b> <span style="color:black;">'.$execution_time.'</span>';