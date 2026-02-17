---
title: "symfonyConsole.optionNotFound"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GreetCommand extends Command
{
	protected function configure(): void
	{
		$this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Who to greet');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$greeting = $input->getOption('greeting');

		return Command::SUCCESS;
	}
}
```

## Why is it reported?

This error is reported by the [phpstan-symfony](https://github.com/phpstan/phpstan-symfony) extension.

The command calls `$input->getOption()` with an option name that was not defined in the `configure()` method. In the example above, the command defines the option `name` but tries to retrieve the option `greeting`, which does not exist. This will throw an `InvalidArgumentException` at runtime.

## How to fix it

Use an option name that matches one defined in the `configure()` method:

```diff-php
 <?php declare(strict_types = 1);

 protected function execute(InputInterface $input, OutputInterface $output): int
 {
-	$greeting = $input->getOption('greeting');
+	$name = $input->getOption('name');

 	return Command::SUCCESS;
 }
```

Or add the missing option definition:

```diff-php
 <?php declare(strict_types = 1);

 protected function configure(): void
 {
 	$this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Who to greet');
+	$this->addOption('greeting', null, InputOption::VALUE_REQUIRED, 'The greeting to use');
 }
```
