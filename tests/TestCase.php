<?php declare(strict_types = 1);

namespace PdTests\Version;

require_once __DIR__ . '/bootstrap.php';


class TestCase extends \Tester\TestCase
{

	/**
	 * @var \Nette\DI\Container
	 */
	protected $container;


	public function setUp(): void
	{
		$configurator = new \Nette\Configurator();
		$configurator->addConfig(__DIR__ . '/PdTests/Version/DI/extension.neon');
		$configurator->setTempDirectory(__DIR__ . '/temp');

		$this->container = $configurator->createContainer();
	}

}
