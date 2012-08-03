<?php
use \gerkirill\ParallelProcessing\ProcessManager;
use \gerkirill\ParallelProcessing\Process;

use \Example\FetcherTask;

require_once(__DIR__.'/autoload.php');

$urls = array('http://twitter.com', 'http://php.net', 'http://github.com');
$processManager = new ProcessManager;
foreach($urls as $url)
{
	$task = new FetcherTask($url);
	$process = new Process(__DIR__.'/Example/processBootstrap.php');
	$process->setTask($task);
	$processManager->addProcess($process);
}
$startTime = time();
$processManager->startAllAndWait();
$execTime = time() - $startTime;
foreach($processManager->getProcesses() as $k => $process)
{
	$task = $process->getTask();
	$i = $k+1;
	echo 
	"\n$i. site title: ".$task->getTitle().
	"\n$i. process OUTPUT: ".$process->getOutput().
	"\n$i. process ERROR: ".$process->getError().
	"\n$i. process EXITCODE: ".$process->getExitCode();
}
echo "\nTOTAL exec. time: $execTime s.\n";