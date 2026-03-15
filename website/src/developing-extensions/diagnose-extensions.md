---
title: Diagnose Extensions
---

PHPStan can output diagnostic information when running the [`analyse` command](/user-guide/command-line-usage#analysing-code) with the `-vvv` CLI option, or when using the [`diagnose` command](/user-guide/command-line-usage#diagnose-problems). If your extension has configuration or runtime state that could help users troubleshoot issues, you can write a diagnose extension to include it in this output.

This is [the interface](https://apiref.phpstan.org/__BRANCH__/PHPStan.Diagnose.DiagnoseExtension.html) your extension needs to implement:

```php
namespace PHPStan\Diagnose;

use PHPStan\Command\Output;

interface DiagnoseExtension
{

	public function print(Output $output): void;

}
```

The `print()` method receives an [`Output`](https://apiref.phpstan.org/__BRANCH__/PHPStan.Command.Output.html) instance that writes to stderr. Use its methods to write formatted output:

* `$output->writeLineFormatted(string $message)` — writes a line with formatting
* `$output->writeRaw(string $message)` — writes raw text without formatting

Here's an example extension that outputs OS-dependent PHP constants relevant to file handling:

```php
namespace App\PHPStan;

use PHPStan\Command\Output;
use PHPStan\Diagnose\DiagnoseExtension;

class FileSystemDiagnoseExtension implements DiagnoseExtension
{

	public function print(Output $output): void
	{
		$output->writeLineFormatted(sprintf(
			'PHP_OS: <info>%s</info>',
			PHP_OS,
		));
		$output->writeLineFormatted(sprintf(
			'PHP_MAXPATHLEN: <info>%d</info>',
			PHP_MAXPATHLEN,
		));
		$output->writeLineFormatted(sprintf(
			'DIRECTORY_SEPARATOR: <info>%s</info>',
			DIRECTORY_SEPARATOR,
		));
	}

}
```

The implementation needs to be registered in your [configuration file](/config-reference):

```yaml
services:
	-
		class: App\PHPStan\FileSystemDiagnoseExtension
		tags:
			- phpstan.diagnoseExtension
```
