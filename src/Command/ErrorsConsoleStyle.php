<?php declare(strict_types = 1);

namespace PHPStan\Command;

use Symfony\Component\Console\Application;

class ErrorsConsoleStyle extends \Symfony\Component\Console\Style\SymfonyStyle
{

	public function table(array $headers, array $rows)
	{
		$application = new Application();
		$dimensions = $application->getTerminalDimensions();
		$terminalWidth = $dimensions[0] ?: self::MAX_LINE_LENGTH;
		$maxHeaderWidth = strlen($headers[0]);
		foreach ($rows as $row) {
			$length = strlen($row[0]);
			if ($maxHeaderWidth === null || $length > $maxHeaderWidth) {
				$maxHeaderWidth = $length;
			}
		}

		$wrap = function ($rows) use ($terminalWidth, $maxHeaderWidth) {
			return array_map(function ($row) use ($terminalWidth, $maxHeaderWidth) {
				return array_map(function ($s) use ($terminalWidth, $maxHeaderWidth) {
					if ($terminalWidth > $maxHeaderWidth + 5) {
						return wordwrap(
							$s,
							$terminalWidth - $maxHeaderWidth - 5,
							"\n",
							true
						);
					}

					return $s;
				}, $row);
			}, $rows);
		};

		return parent::table($headers, $wrap($rows));
	}

}
