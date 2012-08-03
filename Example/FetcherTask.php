<?php
namespace Example;
use gerkirill\ParallelProcessing\TaskInterface;

class FetcherTask implements TaskInterface
{
	private $url;
	private $content;

	public function __construct($url)
	{
		$this->url = $url;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function run()
	{
		$start = time();
		$sleepTime = rand(2, 8);
		sleep($sleepTime);
		$this->content = file_get_contents($this->url);
		$execTime = time() - $start;
		echo strlen($this->content).' bytes read, execution time: '.$execTime.' s.';
		//foreach($undefined as $k){}
		//$this->fatallyNonExistent();
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getTitle()
	{
		if (preg_match('%<title>(.*?)</title>%i', $this->content, $m))
		{
			return $m[1];
		}
		else
		{
			return 'No title';
		}
	}

	public function syncWith(TaskInterface $task)
	{
		$this->content = $task->getContent();
	}
}