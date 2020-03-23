<?php declare(strict_types = 1);

namespace Pd\Version;

interface IFilter
{

	public function __invoke(
		$url,
		$directory = NULL,
		$parameter = NULL
	);

}
