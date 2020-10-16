<?php declare(strict_types = 1);

namespace Pd\Version\Resolvers\Getter;

interface RelativePathGetterInterface
{

	public function getFileName(string $directory, string $path): ?string;

}
