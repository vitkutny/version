<?php

namespace VitKutny\Version;

use Kdyby;
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
			$filter->addSetup('setRequest', [$builder->getDefinition($request)]);
		}
		if ($storage = $builder->getByType(Nette\Caching\IStorage::class)) {
			$filter->addSetup('setStorage', [
				$builder->getDefinition($storage),
				$this->config['expire'],
			]);
		}
		if ($engine = $builder->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class)) {
			$builder->getDefinition($engine)->addSetup('addFilter', [
				'version',
				$filter,
			])
			;
		}
	}


	public function loadConfiguration()
	{
		parent::loadConfiguration();
		$this->config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$arguments = [
			$this->config['directory'],
			$this->config['parameter'],
			$builder->parameters['debugMode'],
		];
		$builder->addDefinition($this->prefix('filter'))->setClass(Filter::class)->setArguments($arguments);

		if ( ! class_exists(Kdyby\Console\DI\ConsoleExtension::class) || PHP_SAPI !== 'cli') {
			return;
		}
		$builder = $this->getContainerBuilder();
		$builder
			->addDefinition($this->prefix('console.cleanCache'))
			->setClass(CleanCacheCommand::class)
			->addTag(Kdyby\Console\DI\ConsoleExtension::COMMAND_TAG)
		;
	}
}
