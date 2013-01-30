<?php
use \gerkirill\ParallelProcessing\ProcessManager;
use \gerkirill\ParallelProcessing\Process;
use \Symfony\Component\EventDispatcher\EventDispatcher;

use \Example\FetcherTask;

require_once(__DIR__.'/vendor/autoload.php');

// the same as task pool, but new tasks may arrive even after processing of some tasks
// has started. New tasks may be added in special handlers invoked upon some events, like 
// some task finish or pool iddle.
// Open task pool continue running in iddle mode even if there are no tasks to process, so
// should be stopped explicitly with the call to stop() method (also called in some event handler)
// Ctrl+C will also work for the CLI mode

/*
$taskPool = new OpenTaskPool;
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


$eventDispatcher = new EventDispatcher;
$eventDispatcher->addListener('process.finished', 
	function($event) use($processManager, $urls)
	{
		echo 'finished: '.$event->getProcess()->getTask()->getUrl()."\n";
	}
);
$eventDispatcher->addListener('process_manager.free_slots_available', 
	function($event) use($processManager, $urls)
	{
		$i = rand(0, 10000);
		if ($i === 10000)
		{
			// TODO: exit only with ctrl+C ?
			echo "stopping loop\n";
			$processManager->stopInfiniteLoop();
		}
		if ($i >= count($urls))
		{
			return;
		}
		echo 'adding: '.$urls[$i]."\n";
		$task = new FetcherTask($urls[$i]);
		$process = new Process(__DIR__.'/Example/processBootstrap.php');
		$process->setTask($task);
		$processManager->addProcess($process);		
	}
);



$processManager->setEventDispatcher($eventDispatcher);
$processManager->startInfiniteLoop();