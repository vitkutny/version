<?php

namespace VitKutny\Version;

use Symfony;


class CleanCacheCommand extends Symfony\Component\Console\Command\Command
{

	protected function configure()
	{
		$this->setName('vitkutny:version:clean-cache');
		$this->setDescription('Smaže cache verzí');
	}


	protected function execute(
		Symfony\Component\Console\Input\InputInterface $input,
		Symfony\Component\Console\Output\OutputInterface $output
	) {
		/** @var \Nette\Caching\IStorage $storage */
		$storage = $this->getHelper('container')->getByType(\Nette\Caching\IStorage::class);
		$conditions = [
			'tags' => [
				Filter::CACHE_TAG,
			],
		];

		if ($output->getVerbosity() > Symfony\Component\Console\Output\Output::VERBOSITY_QUIET) {
			$output->write('Smaže se cache verzí: ');
		}

		$storage->clean($conditions);

		if ($output->getVerbosity() > Symfony\Component\Console\Output\Output::VERBOSITY_QUIET) {
			$output->writeln('<info>Smazáno</info>');
		}
	}

}
