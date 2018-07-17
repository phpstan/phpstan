<?php declare(strict_types = 1);

namespace PHPStan\Command\ProgressPrinter;

use Symfony\Component\Console\Style\OutputStyle;

interface ProgressPrinter
{

	public function setOutputStyle(OutputStyle $style): void;

	public function start(int $max): void;

	public function beforeAnalyzingFile(string $file): void;

	public function afterAnalyzingFile(string $file, array $errors): void;

	public function finish(): void;

}
