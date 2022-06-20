<?php declare(strict_types = 1);

namespace Pd\Version\Resolvers;

abstract class AbstractPathResolver implements \Pd\Version\Resolvers\PathResolverInterface
{

	public const NAMESPACE_SEPARATOR = "\x00";


	protected ?\Nette\Caching\Cache $cache = NULL;

	protected ?\Nette\Http\IRequest $request = NULL;


	public function setRequest(\Nette\Http\IRequest $request): void
	{
		$this->request = $request;
	}


	public function setStorage(\Nette\Caching\IStorage $storage): void
	{
		$this->cache = new \Nette\Caching\Cache($storage, \strtr(self::class, '\\', self::NAMESPACE_SEPARATOR));
	}


	protected function getPath(\Nette\Http\Url $url, string $version, string $parameter): string
	{
		$url->setQueryParameter($parameter, $version ?: \time());

		return (string) \preg_replace($pattern = '#^(\\+|/+)#', \preg_match($pattern, $url->getPath()) ? \DIRECTORY_SEPARATOR : '', $url->getAbsoluteUrl());
	}

}
