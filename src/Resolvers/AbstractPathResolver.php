<?php declare(strict_types = 1);

namespace Pd\Version\Resolvers;

abstract class AbstractPathResolver implements \Pd\Version\Resolvers\PathResolverInterface
{

	/**
	 * @var ?\Nette\Caching\Cache
	 */
	protected $cache;

	/**
	 * @var ?\Nette\Http\IRequest
	 */
	protected $request;


	public function setRequest(\Nette\Http\IRequest $request): void
	{
		$this->request = $request;
	}


	public function setStorage(\Nette\Caching\IStorage $storage): void
	{
		$this->cache = new \Nette\Caching\Cache($storage, \strtr(self::class, '\\', \Nette\Caching\Cache::NAMESPACE_SEPARATOR));
	}


	protected function getPath(\Nette\Http\Url $url, string $version, string $parameter): string
	{
		$url->setQueryParameter($parameter, $version ?: \time());

		return (string) \preg_replace($pattern = '#^(\\+|/+)#', \preg_match($pattern, $url->getPath()) ? \DIRECTORY_SEPARATOR : '', $url->getAbsoluteUrl());
	}

}
