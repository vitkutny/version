<?php declare(strict_types = 1);

namespace PdTests\Version\Cache;

class TestDevNullStorage extends \Nette\Caching\Storages\DevNullStorage
{

	/**
	 * @var ?array $dependencies
	 */
	private $dependencies;


	public function write(string $key, $data, array $dependencies): void
	{
		$this->dependencies = $dependencies;

		parent::write($key,$data, $dependencies);
	}


	public function getDependencies(): array
	{
		return $this->dependencies;
	}

}
