<?php
namespace VitKutny\Version;

use DateTime;
use Nette;

final class Filter
{

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

	public function __construct(
		string $directory,
		string $parameter
	) {
		$this->directory = $directory;
		$this->parameter = $parameter;
		$this->time = time();
	}

	public function setRequest(Nette\Http\IRequest $request)
	{
		$this->request = $request;
	}

	public function setStorage(
		Nette\Caching\IStorage $storage,
		$expire
	) {
		$this->cache = new Nette\Caching\Cache(
			$storage,
			self::class
		);
		$this->expire = $expire instanceof DateTime ? $expire : new DateTime($expire);
	}

	private function process(
		$url,
		string $directory,
		string $parameter,
		array & $dependencies = []
	) : string
	{
		$url = new Nette\Http\Url($url);
		$time = NULL;
		if ($url->getHost() && ( ! $this->request || $url->getHost() !== $this->request->getUrl()->getHost())) {
			$headers = @get_headers(
				$url,
				TRUE
			);
			if (is_array($headers) && isset($headers['Last-Modified'])) {
				$time = new DateTime($headers['Last-Modified']);
				$time = $time->getTimestamp();
			}
		} elseif ($time = @filemtime($filename = $directory . $url->getPath())) {
			if ($dependencies) {
				unset($dependencies[Nette\Caching\Cache::EXPIRE]);
				$dependencies[Nette\Caching\Cache::FILES] = $filename;
			}
		}
		$url->setQueryParameter(
			$parameter,
			$time ? : $this->time
		);

		return preg_replace(
			'#^/+#',
			'/',
			$url
		);
	}

	public function __invoke(
		$url,
		string $directory = NULL,
		string $parameter = NULL
	) : string
	{
		$arguments = [
			$url,
			$directory ? : $this->directory,
			$parameter ? : $this->parameter,
		];

		return $this->cache ? $this->cache->load(
			$arguments,
			function (& $dependencies) use
			(
				$arguments
			) {
				$dependencies[Nette\Caching\Cache::EXPIRE] = $this->expire;
				$arguments[] = &$dependencies;

				return $this->process(
					...
					$arguments
				);
			}
		) : $this->process(
			...
			$arguments
		);
	}
}