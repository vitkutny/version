<?php declare(strict_types = 1);

namespace Pd\Version;

final class Extension extends \Nette\DI\CompilerExtension
{

	private string $directory;

	private string $parameter;

	private bool $debugMode;


	public function __construct(string $directory = '%wwwDir%', string $parameter = 'version', bool $debugMode = FALSE)
	{
		$this->directory = $directory;
		$this->parameter = $parameter;
		$this->debugMode = $debugMode;
	}


	public function beforeCompile(): void
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();
		/** @var \Nette\DI\Definitions\ServiceDefinition $filter */
		$filter = $builder->getDefinition($this->prefix('filter'));
		/** @var \Nette\DI\Definitions\ServiceDefinition $absoluteUrlResolver */
		$absoluteUrlResolver = $builder->getDefinition($this->prefix('absoluteUrlResolver'));
		/** @var \Nette\DI\Definitions\ServiceDefinition $pathResolver */
		$pathResolver = $builder->getDefinition($this->prefix('pathResolver'));

		$request = $builder->getByType(\Nette\Http\IRequest::class);
		if ($request) {
			$absoluteUrlResolver->addSetup('setRequest', [$builder->getDefinition($request)]);
			$pathResolver->addSetup('setRequest', [$builder->getDefinition($request)]);
		}

		$storage = $builder->getByType(\Nette\Caching\IStorage::class);
		if ($storage) {
			$absoluteUrlResolver->addSetup('setStorage', [
				$builder->getDefinition($storage),
			]);
			$pathResolver->addSetup('setStorage', [
				$builder->getDefinition($storage),
			]);
		}

		$engine = $builder->getByType(\Nette\Bridges\ApplicationLatte\ILatteFactory::class);
		if ($engine) {
			/** @var \Nette\DI\Definitions\FactoryDefinition $latteEngine */
			$latteEngine = $builder->getDefinition($engine);
			$latteEngine->getResultDefinition()->addSetup('addFilter', [
				'version',
				$filter,
			])
			;
		}
	}


	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();

		$absoluteUrlResolver = $builder->addDefinition($this->prefix('absoluteUrlResolver'))
			->setFactory(\Pd\Version\Resolvers\AbsoluteUrlResolver::class)
		;

		$pathResolver = $builder->addDefinition($this->prefix('pathResolver'))
			->setFactory(\Pd\Version\Resolvers\PathResolver::class)
			->setArguments([$this->debugMode])
		;

		$arguments = [
			$this->directory,
			$this->parameter,
			$absoluteUrlResolver,
			$pathResolver,
		];
		$builder->addDefinition($this->prefix('filter'))->setClass(Filter::class)->setArguments($arguments);

		$builder->addDefinition($this->prefix('relativePathGetter'))
			->setFactory(\Pd\Version\Resolvers\Getter\RelativePathGetter::class)
		;

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
