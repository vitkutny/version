<?php declare(strict_types = 1);

namespace Pd\Version\Resolvers\Getter;

final class RelativePathGetter implements \Pd\Version\Resolvers\Getter\RelativePathGetterInterface
{

	public function getFileName(string $directory, string $path): ?string
	{
		$filename = \implode(\DIRECTORY_SEPARATOR, [
			\rtrim($directory, '\\/'),
			\ltrim($path, '\\/'),
		]);

		if ( ! \is_file($filename)) {
			return NULL;
		}

		return $filename;
	}

}
