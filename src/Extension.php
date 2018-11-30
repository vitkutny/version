<?php declare(strict_types = 1);

namespace Pd\Version;

final class Extension extends \Nette\DI\CompilerExtension
{

	/**
	 * @var array
	 */
	private $defaults = [
		'directory' => '%wwwDir%',
		'parameter' => 'version',
	];


	public function beforeCompile(): void
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();
		$filter = $builder->getDefinition($this->prefix('filter'));

		$request = $builder->getByType(\Nette\Http\IRequest::class);
		if ($request) {
			$filter->addSetup('setRequest', [$builder->getDefinition($request)]);
		}

		$storage = $builder->getByType(\Nette\Caching\IStorage::class);
		if ($storage) {
			$filter->addSetup('setStorage', [
				$builder->getDefinition($storage),
				$this->config['expire'],
			]);
		}

		$engine = $builder->getByType(\Nette\Bridges\ApplicationLatte\ILatteFactory::class);
		if ($engine) {
			$builder->getDefinition($engine)->addSetup('addFilter', [
				'version',
				$filter,
			])
			;
		}
	}


	public function loadConfiguration(): void
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

		if ( ! \class_exists(\Kdyby\Console\DI\ConsoleExtension::class) || \PHP_SAPI !== 'cli') {
			return;
		}
		$builder = $this->getContainerBuilder();
		$builder
			->addDefinition($this->prefix('console.cleanCache'))
			->setClass(CleanCacheCommand::class)
			->addTag(\Kdyby\Console\DI\ConsoleExtension::COMMAND_TAG)
		;
	}
}
