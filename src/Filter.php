<?php declare(strict_types = 1);

namespace Pd\Version;

final class Filter
{

	public const CACHE_TAG = 'pd-version';

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var \Nette\Http\IRequest
	 */
	private $request;

	/**
	 * @var \Nette\Caching\Cache|null
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $parameter;

	/**
	 * @var bool
	 */
	private $debugMode;


	public function __construct(
		$directory,
		$parameter,
		$debugMode
	) {
		$this->directory = $directory;
		$this->parameter = $parameter;
		$this->debugMode = $debugMode;
	}


	public function setRequest(\Nette\Http\IRequest $request)
	{
		$this->request = $request;
	}


	public function setStorage(
		\Nette\Caching\IStorage $storage
	) {
		$this->cache = new \Nette\Caching\Cache($storage, \strtr(self::class, '\\', \Nette\Caching\Cache::NAMESPACE_SEPARATOR));
	}


	public function __invoke(
		$url,
		$directory = NULL,
		$parameter = NULL
	) {
		$directory = $directory ?: $this->directory;
		$parameter = $parameter ?: $this->parameter;

		$cacheCallback = function (?array &$dependencies) use (
			$url,
			$directory,
			$parameter
		) {
			if ( ! $dependencies) {
				$dependencies = [];
			}

			return $this->process($url, $directory, $parameter, $dependencies);
		};

		return $this->cache ? $this->cache->load([$url, $directory, $parameter, $this->debugMode], $cacheCallback) : $this->process($url, $directory, $parameter);
	}


	private function process(
		$url,
		$directory,
		$parameter,
		array & $dependencies = []
	) {
		$url = new \Nette\Http\Url($url);
		$version = NULL;
		if ($url->getHost() && ( ! $this->request || $url->getHost() !== $this->request->getUrl()->getHost())) {
			$headers = @\get_headers($url->getAbsoluteUrl(), 1);
			if (\is_array($headers) && isset($headers['ETag'])) {
				$version = \preg_replace('~[^a-z0-9\-]~', '', $headers['ETag']);
			} elseif (\is_array($headers) && isset($headers['Last-Modified'])) {
				$version = (new \DateTime($headers['Last-Modified']))->getTimestamp();
			}
		} else {
			$filename = \implode(\DIRECTORY_SEPARATOR, [
				\rtrim($directory, '\\/'),
				\ltrim($url->getPath(), '\\/'),
			]);
			if (\is_file($filename)) {
				$version = \sha1_file($filename);
				if ($this->debugMode) {
					$dependencies[\Nette\Caching\Cache::FILES] = $filename;
				}
			}
		}
		$dependencies[\Nette\Caching\Cache::TAGS] = [self::CACHE_TAG];
		$url->setQueryParameter($parameter, $version ?: \time());

		return \preg_replace($pattern = '#^(\\+|/+)#', \preg_match($pattern, $url->getPath()) ? \DIRECTORY_SEPARATOR : NULL, $url->getAbsoluteUrl());
	}
}
