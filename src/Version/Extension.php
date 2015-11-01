<?php
namespace VitKutny\Version;

use Nette;

final class Extension
	extends Nette\DI\CompilerExtension
{

	/**
	 * @var array
	 */
	private $defaults = [
		'directory' => '%wwwDir%',
		'parameter' => 'version',
		'expire' => '+1 hour',
	];

	public function beforeCompile()
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();
		$filter = $builder->getDefinition($this->prefix('filter'));
		if ($request = $builder->getByType(Nette\Http\IRequest::class)) {
			$filter->addSetup(
				'setRequest',
				[$builder->getDefinition($request)]
			);
		}
		if ($storage = $builder->getByType(Nette\Caching\IStorage::class)) {
			$filter->addSetup(
				'setStorage',
				[
					$builder->getDefinition($storage),
					$this->config['expire'],
				]
			);
		}
		if ($engine = $builder->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class)) {
			$builder->getDefinition($engine)->addSetup(
				'addFilter',
				[
					'version',
					$filter,
				]
			);
		}
	}

	public function loadConfiguration()
	{
		parent::loadConfiguration();
		$this->config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('filter'))->setClass(Filter::class)->setArguments(
			[
				$this->config['directory'],
				$this->config['parameter'],
			]
		);
	}
}