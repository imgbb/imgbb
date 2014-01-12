<?php
#for /imgbb/, irrelevent to actual software
require_once dirname(dirname(__FILE__)) . '/intruder_process.php';
#temp
$time_start = microtime(true);
require_once 'constants.php';
require_once IBB_ROOT_PATH . '/app/ibbController.php';
require_once IBB_LIB_PATH . '/PHPTAL-1.2.2/PHPTAL.php';
require_once IBB_ROOT_PATH . '/classes/furl_class.php';
require_once IBB_ROOT_PATH . '/classes/loader_class.php';
require_once IBB_ROOT_PATH . '/classes/upload.php';
require_once IBB_ROOT_PATH . '/classes/text/text.php';
require_once IBB_ROOT_PATH . '/classes/interface/appCore.php';
session_start();
//echo 'Loading all metadata takes ' . (microtime(true) - $time_start);
//echo '<b>index</b><br />';
//echo 'Exec: ' . (microtime(true) - $time_start) . '<br />';
//echo 'Mem: '.turnMemoryToKB(memory_get_usage()) . '<br />';

/* temporary catchall for exceptions until exceptions are prepped */
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

# debugging & efficiency, temp
$time_end = microtime(true);

$execution_time = $time_end - $time_start;

echo 'Execution time must not go above 1.0. Memory usage must not go above 5MB. Query exection count may not go above 15.<br />';
echo '<b style="color:black;">Total Execution Time:</b> <span style="color:black;">'.$execution_time.'</span>';
echo '<br /><b style="color:black;">Peak Memory Usage</b>: <span style="color:black;">'. turnMemoryToKB(memory_get_peak_usage()) . ' KB</span>';
echo '<br /><b style="color:black;">Memory Usage at EoS</b>: <span style="color:black;">' . turnMemoryToKB(memory_get_usage()) . ' KB</span>';
echo '<br /><b style="color:black;">Query Execution Count</b>: <span style="color:black;">' . count(ibbCore::$queryc) . '</span>';

/**
 * Convert memory to kilobytes
 *
 * @param $memory_get_usage
 *
 * @return string
 */
function turnMemoryToKB( $memory_get_usage )
{
	return substr($memory_get_usage, 0, strlen($memory_get_usage) - 3);
}