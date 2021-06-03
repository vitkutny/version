<?php declare(strict_types = 1);

namespace PdTests\Version\DI;

require __DIR__ . '/../../../bootstrap.php';


class ExtensionTest extends \PdTests\Version\TestCase
{

	public function testExtension()
	{
		$service = $this->container->getByType(\Pd\Version\Filter::class);
		\Tester\Assert::type(\Pd\Version\Filter::class, $service);

		$service = $this->container->getByType(\Pd\Version\CleanCacheCommand::class);
		\Tester\Assert::type(\Pd\Version\CleanCacheCommand::class, $service);
	}
}
(new ExtensionTest())->run();
