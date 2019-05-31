<?php declare(strict_types = 1);

namespace Pd\Version;

final class Extension extends \Nette\DI\CompilerExtension
{

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var string
	 */
	private $parameter;

	/**
	 * @var bool
	 */
	private $debugMode;


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

		$request = $builder->getByType(\Nette\Http\IRequest::class);
		if ($request) {
			$filter->addSetup('setRequest', [$builder->getDefinition($request)]);
		}

		$storage = $builder->getByType(\Nette\Caching\IStorage::class);
		if ($storage) {
			$filter->addSetup('setStorage', [
				$builder->getDefinition($storage),
			]);
		}

		$engine = $builder->getByType(\Nette\Bridges\ApplicationLatte\ILatteFactory::class);
		if ($engine) {
			/** @var \Nette\DI\ServiceDefinition $latteEngine */
			$latteEngine = $builder->getDefinition($engine);
			$latteEngine->addSetup('addFilter', [
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

		$arguments = [
			$this->directory,
			$this->parameter,
			$this->debugMode,
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
