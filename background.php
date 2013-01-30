<?php
declare(ticks=1);
use \gerkirill\ParallelProcessing\ProcessManager;
use \gerkirill\ParallelProcessing\Process;
use \Symfony\Component\EventDispatcher\EventDispatcher;

use \Example\FetcherTask;

require_once(__DIR__.'/vendor/autoload.php');
// designed for the rare cases when you have some foreground job, which occasionally
// may outsource some work to the parallel processes, running in background. Nice for legacy
// code to leverage the power of parallel processing with minimum changes required.
// Example: code which imports a large set of object (let's say ads) from CSV file into database
// and some of the CSV records contain image URLs, related to the ad. For such records - we need
// to grab the images by the URL and store them locally. In this case we could left CSV processing
// code as is, while perform image fetching in the background.

/*
$taskPool = new BackgroundTaskPool;
$taskPool->setProcessManager($pm);
$taskPool->setProcessFactory($pf); // could $pf be a callable ?
$taskPool->setConcurrencyLimit(2); // execute at most 2 processes at once

// define handlers somehow - callable ?

$taskPool->addTask($task1);
$taskPool->addTask($task2);
$taskPool->addTask($task3);

$taskPool->execute(2); //or pass concurrency limit here ?
*/
$urls = array('http://twitter.com', 'http://php.net', 'http://github.com');
$processManager = new ProcessManager;

$processManager->runInBackground();
$processManager->setConcurrencyLimit(2);

$eventDispatcher = new EventDispatcher;
$eventDispatcher->addListener('process.started', 
	function($event) use($processManager)
	{
		echo 'started: '.$event->getProcess()->getTask()->getUrl()."\n";
	}
);
$eventDispatcher->addListener('process.finished', 
	function($event) use($processManager)
	{
		echo 'finished: '.$event->getProcess()->getTask()->getUrl()."\n";
	}
);
$processManager->setEventDispatcher($eventDispatcher);


$startTime = time();
foreach($urls as $url)
{
	$task = new FetcherTask($url);
	$process = new Process(__DIR__.'/Example/processBootstrap.php');
	$process->setTask($task);
	$processManager->addProcess($process);
	for($i=0; $i<100; $i++)
	{
		// some time-consuming operations left in the main process
		$x = 4 * rand(1,10);
		usleep(30000);
	}
}
$looptime = time() - $startTime;
$waitStart = time();
echo "start waiting \n";
// if our slow loop was faster than parallel processes anyway - wait for them to finish here
$processManager->waitForAll();
$waitTime = time() - $waitStart;

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
echo "\nTOTAL exec. time: $execTime s (loop time: $looptime s, wait time: $waitTime s).\n";