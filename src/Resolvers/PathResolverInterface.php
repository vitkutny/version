<?php declare(strict_types = 1);

namespace Pd\Version\Resolvers;

interface PathResolverInterface
{

	public function resolve(\Nette\Http\Url $url, string $directory, string $parameter): ?string;


	public function setStorage(\Nette\Caching\IStorage $storage): void;

}
