---
title: "symfonyConsole.argumentNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MyCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('my-command');
		$this->addArgument('name');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$input->getArgument('undefined');

		return 0;
	}
}
```

## Why is it reported?

The `getArgument()` call references an argument name that was not defined in the command's `configure()` method. At runtime, this would throw an `InvalidArgumentException`.

This error is reported by the [phpstan-symfony](https://github.com/phpstan/phpstan-symfony) extension, which resolves Symfony Console commands and validates that referenced arguments exist in the command's definition.

## How to fix it

Use an argument name that matches one defined via `addArgument()` in the `configure()` method:

```diff-php
-$input->getArgument('undefined');
+$input->getArgument('name');
```

Or add the missing argument definition:

```diff-php
 protected function configure(): void
 {
 	$this->setName('my-command');
 	$this->addArgument('name');
+	$this->addArgument('undefined');
 }
```
