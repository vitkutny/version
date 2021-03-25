<?php declare(strict_types = 1);

namespace PdTests\Version\DI;

require __DIR__ . '/../../../bootstrap.php';


class ExtensionTest extends \PdTests\Version\TestCase
{

	public function testExtension()
	{
		$service = $this->container->getByType(\Pd\Version\Filter::class);
		\Tester\Assert::truthy($service);

		$service = $this->container->getByType(\Pd\Version\CleanCacheCommand::class);
		\Tester\Assert::truthy($service);
	}
}
(new ExtensionTest())->run();
