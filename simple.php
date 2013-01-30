<?php
use \gerkirill\ParallelProcessing\ProcessManager;
use \gerkirill\ParallelProcessing\Process;

use \Example\FetcherTask;

require_once(__DIR__.'/vendor/autoload.php');

// here is how it could be (all the tasks are known at the start and can be
// executed at once):

/*
$taskBunch = new TaskBunch; //extends TaskManager, strategy pattern
$taskBunch->setProcessManager($pm);
$taskBunch->setProcessFactory($pf); // could $pf be a callable ?

$taskBunch->addTask($task1);
$taskBunch->addTask($task2);

$taskBunch->execute();

foreach($taskBunch) // iterator, array access
{
	// ...
}
*/

/*
Example of the simplest scenario possible - fetch data (page titles) from multiple URLs
in parallel. In this scenario total execution time approx. equivalent to the longest fetch task.
*/
// URLs to fetch titles from
$urls = array('http://twitter.com', 'http://php.net', 'http://github.com');
// we need process manager to rule the concurrent processes - in this case - start them and wait
// untill all finished
$processManager = new ProcessManager;
// create tasks and processes for all urls and excute them later all at once in parallel
foreach($urls as $url)
{
	// FetcherTask - fetches HTML page and detects its title
	$task = new FetcherTask($url);
	// create process to handle the task
	$process = new Process(__DIR__.'/Example/processBootstrap.php');
	$process->setTask($task);
	$processManager->addProcess($process);
}

$startTime = time();
// here we start fetching and parsing all the three URLs in parallel. startAllAndWait() will
// only return once all tasks are done.
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

/* Execution result may look like this 
(you may notice exec. time is equivalent to the longest task run time):

1. site title: Twitter
1. process OUTPUT: 72298 bytes read, execution time: 4 s.
1. process ERROR: 
1. process EXITCODE: 0

2. site title: PHP: Hypertext Preprocessor
2. process OUTPUT: 43218 bytes read, execution time: 11 s.
2. process ERROR: 
2. process EXITCODE: 0

3. site title: GitHub · Build software better, together.
3. process OUTPUT: 20890 bytes read, execution time: 7 s.
3. process ERROR: 
3. process EXITCODE: 0

TOTAL exec. time: 11 s.
*/