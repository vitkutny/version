<?php declare(strict_types = 1);

namespace Pd\Version;

final class Filter implements IFilter
{

	public const CACHE_TAG = 'pd-version';


	private string $directory;

	private string $parameter;

	/**
	 * @var \Pd\Version\Resolvers\PathResolverInterface[]
	 */
	private array $getters;


	public function __construct(
		string $directory,
		string $parameter,
		\Pd\Version\Resolvers\PathResolverInterface ...$getters
	)
	{
		$this->directory = $directory;
		$this->parameter = $parameter;
		$this->getters = $getters;
	}


	/**
	 * @param string|\Nette\Http\Url|\Nette\Http\UrlImmutable $url
	 */
	public function __invoke(
		$url,
		?string $directory = NULL,
		?string $parameter = NULL
	): ?string
	{
		$directory = $directory ?: $this->directory;
		$parameter = $parameter ?: $this->parameter;

		$url = new \Nette\Http\Url($url);
		foreach ($this->getters as $getter) {
			$filePath = $getter->resolve($url, $directory, $parameter);
			if ($filePath) {
				return $filePath;
			}
		}

		return NULL;
	}

}
