<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Symfony\Component\Console\Style\StyleInterface;

class RawErrorFormatter implements ErrorFormatter
{

	/**
	 * Format the errors and output them to the console.
	 *
	 * @param array $errors
	 * @param array $paths
	 * @param StyleInterface $style
	 * @return int Error code.
	 */
	public function formatErrors(array $errors, array $paths, StyleInterface $style): int
	{
		if (count($errors) === 0) {
			return 0;
		}

		foreach ($errors as $error) {
			$style->text(sprintf('%s:%d:%s', $error->getFile(), $error->getLine(), $error->getMessage()));
		}
		return 1;
	}

}
