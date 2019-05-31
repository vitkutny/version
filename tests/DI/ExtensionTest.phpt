<?php declare(strict_types = 1);

namespace PdTests\Version\DI;

require __DIR__ . '/../bootstrap.php';


class ExtensionTest extends \Tester\TestCase
{

	public function testExtension()
	{
		$configurator = new \Nette\Configurator();
		$configurator->addConfig(__DIR__ . '/extension.neon');
		$configurator->setTempDirectory(__DIR__);
		$container = $configurator->createContainer();

		$service = $container->getByType(\Pd\Version\Filter::class);
		\Tester\Assert::truthy($service);

		$service = $container->getByType(\Pd\Version\CleanCacheCommand::class);
		\Tester\Assert::truthy($service);
	}
}


(new ExtensionTest())->run();
