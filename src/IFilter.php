<?php declare(strict_types = 1);

namespace Pd\Version;

interface IFilter
{

	/**
	 * @param string|\Nette\Http\Url|\Nette\Http\UrlImmutable $url
	 */
	public function __invoke(
		$url,
		?string $directory = NULL,
		?string $parameter = NULL
	): ?string;

}
