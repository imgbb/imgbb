<?php
/* temp */
$time_start = microtime(true);
require_once 'constants.php';
require_once IBB_ROOT_PATH . '/app/ibbController.php';
require_once IBB_LIB_PATH . '/PHPTAL-1.2.2/PHPTAL.php';
require_once IBB_ROOT_PATH . '/classes/furl_class.php';
require_once IBB_ROOT_PATH . '/classes/loader_class.php';
// TODO BASIC Exception handler
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
echo '<br /><b style="color:black;">Peak Memory Usage</b>: <span style="color:black;">'. turnMemoryToKB(memory_get_peak_usage()) . ' KB</span>';
echo '<br /><b style="color:black;">Memory Usage at End of Script</b>: <span style="color:black;">' . turnMemoryToKB(memory_get_usage()) . ' KB</span>';
echo '<br /><b style="color:black;">Query Execution Count</b>: <span style="color:black;">' . ibbCore::$queryc . '</span>';


function turnMemoryToKB( $memory_get_usage )
{
	return substr($memory_get_usage, 0, strlen($memory_get_usage) - 3);
}