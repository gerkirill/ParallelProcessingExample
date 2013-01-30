<?php
use \gerkirill\ParallelProcessing\ProcessManager;
use \gerkirill\ParallelProcessing\Process;
use \Symfony\Component\EventDispatcher\EventDispatcher;

use \Example\FetcherTask;

require_once(__DIR__.'/vendor/autoload.php');

// TODO: rename this to pool (task pool)
// all the task are known at the start, but we can't execute them all at once - 
// means we should limit the number of processes running at the same time

/*
$taskPool = new TaskPool;
$taskPool->setProcessManager($pm);
$taskPool->setProcessFactory($pf); // could $pf be a callable ?
$taskPool->setConcurrencyLimit(2); // execute at most 2 processes at once

$taskPool->addTask($task1);
$taskPool->addTask($task2);
$taskPool->addTask($task3);

$taskPool->execute(2); //or pass concurrency limit here ?
*/
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