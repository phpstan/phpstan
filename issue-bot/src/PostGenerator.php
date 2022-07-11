<?php declare(strict_types = 1);

namespace App;

use App\Playground\PlaygroundResult;
use App\Playground\PlaygroundResultTab;
use SebastianBergmann\Diff\Differ;

class PostGenerator
{

	private Differ $differ;

	private string $gitBranch;

	private string $commitBefore;

	private string $commitAfter;

	public function __construct(Differ $differ, string $gitBranch, string $commitBefore, string $commitAfter)
	{
		$this->differ = $differ;
		$this->gitBranch = $gitBranch;
		$this->commitBefore = $commitBefore;
		$this->commitAfter = $commitAfter;
	}

	/**
	 * @param PlaygroundResult $result
	 * @param BotComment[] $botComments
	 * @return string|null
	 */
	public function createText(PlaygroundResult $result, array $botComments): ?string
	{
		foreach ($result->getCurrentTabs() as $tab) {
			foreach ($tab->getErrors() as $error) {
				if (strpos($error->getMessage(), 'Internal error') === false) {
					continue;
				}

				return null;
			}
		}

		$maxDigit = 1;
		foreach (array_merge($result->getOriginalTabs(), $result->getCurrentTabs()) as $tab) {
			foreach ($tab->getErrors() as $error) {
				$length = strlen((string) $error->getLine());
				if ($length <= $maxDigit) {
					continue;
				}

				$maxDigit = $length;
			}
		}
		$originalErrorsText = $this->generateTextFromTabs($result->getOriginalTabs(), $maxDigit);
		$currentErrorsText = $this->generateTextFromTabs($result->getCurrentTabs(), $maxDigit);
		if ($originalErrorsText === $currentErrorsText) {
			return null;
		}

		$diff = $this->differ->diff($originalErrorsText, $currentErrorsText);
		foreach ($botComments as $botComment) {
			if ($botComment->getResultHash() !== $result->getHash()) {
				continue;
			}

			if ($botComment->getDiff() === $diff) {
				return null;
			}
		}

		$text = sprintf(
			"%s After [the latest push in %s](https://github.com/phpstan/phpstan-src/compare/%s...%s), PHPStan now reports different result with your [code snippet](https://phpstan.org/r/%s):\n\n```diff\n%s```",
			implode(' ', array_map(static function (string $user): string {
				return sprintf('@%s', $user);
			}, $result->getUsers())),
			$this->gitBranch,
			$this->commitBefore,
			$this->commitAfter,
			$result->getHash(),
			$diff
		);

		if (count($result->getCurrentTabs()) === 1 && count($result->getCurrentTabs()[0]->getErrors()) === 0) {
			return $text;
		}

		$details = [];
		foreach ($result->getCurrentTabs() as $tab) {
			$detail = '';
			if (count($result->getCurrentTabs()) > 1) {
				$detail .= sprintf("%s\n-----------\n\n", $tab->getTitle());
			}

			if (count($tab->getErrors()) === 0) {
				$detail .= "No errors\n";
				$details[] = $detail;
				continue;
			}

			$detail .= "| Line | Error |\n";
			$detail .= "|---|---|\n";

			foreach ($tab->getErrors() as $error) {
				$errorText = \Nette\Utils\Strings::replace($error->getMessage(), "/\r|\n/", '');
				$detail .= sprintf("| %d | `%s` |\n", $error->getLine(), $errorText);
			}

			$details[] = $detail;
		}

		return $text . "\n\n" . sprintf('<details>
 <summary>Full report</summary>

%s
</details>', implode("\n", $details));
	}

	/**
	 * @param PlaygroundResultTab[] $tabs
	 * @return string
	 */
	private function generateTextFromTabs(array $tabs, int $maxDigit): string
	{
		$parts = [];
		foreach ($tabs as $tab) {
			$text = '';
			if (count($tabs) > 1) {
				$text .= sprintf("%s\n==========\n\n", $tab->getTitle());
			}

			if (count($tab->getErrors()) === 0) {
				$text .= 'No errors';
				$parts[] = $text;
				continue;
			}

			$errorLines = [];
			foreach ($tab->getErrors() as $error) {
				$errorLines[] = sprintf('%s: %s', str_pad((string) $error->getLine(), $maxDigit, ' ', STR_PAD_LEFT), $error->getMessage());
			}

			$text .= implode("\n", $errorLines);

			$parts[] = $text;
		}

		return implode("\n\n", $parts);
	}

}
