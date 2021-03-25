<?php declare(strict_types = 1);

namespace PdTests\Version\Cache;

require_once __DIR__ . '/../../../bootstrap.php';


class TagsTest extends \PdTests\Version\TestCase
{

	public function testSaveTags(): void
	{
		/** @var \PdTests\Version\Cache\TestDevNullStorage $storage */
		$storage = $this->container->getByType(\PdTests\Version\Cache\TestDevNullStorage::class);

		/** @var \Pd\Version\Resolvers\PathResolver $resolver */
		$resolver = $this->container->getByType(\Pd\Version\Resolvers\PathResolver::class);
		$resolver->resolve(new \Nette\Http\Url('test.txt'), __DIR__ . '/files', '');

		\Tester\Assert::true(isset($storage->getDependencies()[\Nette\Caching\Cache::TAGS]));

		$defaultTag = \current($storage->getDependencies()[\Nette\Caching\Cache::TAGS]);

		\Tester\Assert::same($defaultTag, \Pd\Version\Filter::CACHE_TAG);
	}

}
(new TagsTest())->run();
