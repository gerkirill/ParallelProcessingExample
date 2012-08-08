<?php
use \gerkirill\ParallelProcessing\ProcessManager;
use \gerkirill\ParallelProcessing\Process;
use \Symfony\Component\EventDispatcher\EventDispatcher;

use \Example\FetcherTask;

require_once(__DIR__.'/vendor/autoload.php');

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