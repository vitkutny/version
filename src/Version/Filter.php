<?php

namespace VitKutny\Version;

use DateTime;
use Nette;


final class Filter
{

	const CACHE_TAG = 'vitkutny-version';

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var Nette\Http\IRequest
	 */
	private $request;

	/**
	 * @var Nette\Caching\Cache
	 */
	private $cache;

	/**
	 * @var DateTime
	 */
	private $expire;

	/**
	 * @var string
	 */
	private $parameter;

	/**
	 * @var int
	 */
	private $time;

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


	public function setRequest(Nette\Http\IRequest $request)
	{
		$this->request = $request;
	}


	public function setStorage(
		Nette\Caching\IStorage $storage,
		$expire
	) {
		$this->cache = new Nette\Caching\Cache($storage, strtr(self::class, '\\', Nette\Caching\Cache::NAMESPACE_SEPARATOR));
		$this->expire = $expire instanceof DateTime ? $expire : new DateTime($expire);
	}


	public function __invoke(
		$url,
		$directory = NULL,
		$parameter = NULL
	) {
		$directory = $directory ?: $this->directory;
		$parameter = $parameter ?: $this->parameter;

		$cacheCallback = function (& $dependencies) use (
			$url,
			$directory,
			$parameter
		) {
			$dependencies[Nette\Caching\Cache::EXPIRE] = $this->expire;

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
		$url = new Nette\Http\Url($url);
		$time = NULL;
		if ($url->getHost() && ( ! $this->request || $url->getHost() !== $this->request->getUrl()->getHost())) {
			$headers = @get_headers($url, TRUE);
			if (is_array($headers) && isset($headers['Last-Modified'])) {
				$time = (new DateTime($headers['Last-Modified']))->getTimestamp();
			}
		} elseif (is_file($filename = implode(DIRECTORY_SEPARATOR, [
			rtrim($directory, '\\/'),
			ltrim($url->getPath(), '\\/'),
		]))) {
			$time = filemtime($filename);
			if ($this->debugMode) {
				$dependencies[Nette\Caching\Cache::FILES] = $filename;
			}
		}
		$dependencies[Nette\Caching\Cache::SLIDING] = TRUE;
		$dependencies[Nette\Caching\Cache::TAGS] = [self::CACHE_TAG];
		$url->setQueryParameter($parameter, $time ?: ($this->time ?: $this->time = time()));

		return preg_replace($pattern = '#^(\\+|/+)#', preg_match($pattern, $url->getPath()) ? DIRECTORY_SEPARATOR : NULL, $url);
	}
}
