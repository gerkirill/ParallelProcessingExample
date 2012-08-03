<?php
use gerkirill\ParallelProcessing\Process;
require_once(dirname(__DIR__).'/vendor/autoload.php');

$task = Process::getTaskFromProcess();
$task->run();
Process::updateTaskFromProcess($task);
exit(0);