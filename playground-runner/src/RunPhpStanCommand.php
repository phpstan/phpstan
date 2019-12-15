<?php declare(strict_types = 1);

namespace App;

use PHPStan\Command\AnalyseApplication;
use PHPStan\Command\CommandHelper;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use Silly\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunPhpStanCommand extends Command
{

	protected function configure()
	{
		$this->setName('phpstan:run');
		$this->addArgument('input', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$phpFile = $input->getArgument('input');
		$filePath = '/tmp/tmp.php';
		file_put_contents($filePath, $phpFile);
		$inceptionResult = CommandHelper::begin(
			$input,
			$output,
			[$filePath],
			null,
			null,
			null,
			'7'
		);
		$container = $inceptionResult->getContainer();

		/** @var AnalyseApplication $application */
		$application = $container->getByType(AnalyseApplication::class);
		return $application->analyse(
			$inceptionResult->getFiles(),
			$inceptionResult->isOnlyFiles(),
			$inceptionResult->getConsoleStyle(),
			$container->getService('errorFormatter.json'),
			$inceptionResult->isDefaultLevelUsed(),
			false
		);
	}

}
