<?php

declare(strict_types=1);

namespace ciri;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskScheduler;

class Ciri extends PluginBase
{
	/** @var Ciri */
	public static $instance;

	/** @var TaskScheduler */
	public $scheduler;

	/** @var Process */
	public $process;

	public function getProcess() :Process
	{
		return $this->process;
	}

	public static function getInstance() :Ciri
	{
		return self::$instance;
	}

	public function init()
	{
		self::$instance = $this;
		require_once $this->getFile().'vendor/autoload.php';
		$this->scheduler = $this->getScheduler();
		$this->process = new Process();
		date_default_timezone_set('Asia/Tokyo');
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function onEnable()
	{
		$this->init();
	}
}