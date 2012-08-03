<?php
use \gerkirill\ParallelProcessing\ProcessManager;
use \gerkirill\ParallelProcessing\Process;
use \Symfony\Component\EventDispatcher\EventDispatcher;

use \Example\FetcherTask;

require_once(__DIR__.'/autoload.php');

$urls = array('http://twitter.com', 'http://php.net', 'http://github.com');
$processLimit = 2;
$processManager = new ProcessManager;
for($i=0; $i<min($processLimit, count($urls)); $i++)
{
	$task = new FetcherTask($urls[$i]);
	$process = new Process(__DIR__.'/Example/processBootstrap.php');
	$process->setTask($task);
	$processManager->addProcess($process);
}

$eventDispatcher = new EventDispatcher;
$eventDispatcher->addListener('process.finished', 
	function($event) use($processManager, $urls, &$i)
	{
		echo 'finished: '.$event->getProcess()->getTask()->getUrl()."\n";
		if ($i >= count($urls)) return;
		echo 'adding: '.$urls[$i]."\n";
		$task = new FetcherTask($urls[$i]);
		$process = new Process(__DIR__.'/Example/processBootstrap.php');
		$process->setTask($task);
		$processManager->addProcess($process);
		$i++;
	}
);
$processManager->setEventDispatcher($eventDispatcher);
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