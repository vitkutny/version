<?php declare(strict_types = 1);

namespace Pd\Version\Resolvers;

class PathResolver extends \Pd\Version\Resolvers\AbstractPathResolver
{

	private bool $debugMode;

	private \Pd\Version\Resolvers\Getter\RelativePathGetterInterface $relativePathGetter;


	public function __construct(
		bool $debugMode,
		\Pd\Version\Resolvers\Getter\RelativePathGetterInterface $relativePathGetter
	)
	{
		$this->debugMode = $debugMode;
		$this->relativePathGetter = $relativePathGetter;
	}


	public function resolve(\Nette\Http\Url $url, string $directory, string $parameter): ?string
	{
		$realPath = $this->relativePathGetter->getFileName($directory, $url->getPath());

		if ( ! $realPath) {
			return NULL;
		}

		if ( ! $this->cache) {
			return $this->process($url, $realPath, $parameter);
		}

		return $this->cache->load([$realPath, $directory, $parameter], function (?array &$dependencies) use ($url, $realPath, $parameter): string {
			return $this->process($url, $realPath, $parameter, $dependencies);
		});
	}


	/**
	 * @param array<string, mixed> $dependencies
	 */
	private function process(\Nette\Http\Url $url, string $realPath, string $parameter, ?array &$dependencies = NULL): string
	{
		$version = (string) \sha1_file($realPath);
		if ($this->debugMode) {
			$dependencies[\Nette\Caching\Cache::FILES] = $realPath;
		}
		$dependencies[\Nette\Caching\Cache::TAGS] = [\Pd\Version\Filter::CACHE_TAG];

		return $this->getPath($url, $version, $parameter);
	}

}
