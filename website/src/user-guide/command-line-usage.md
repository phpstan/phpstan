---
title: Command Line Usage
---

PHPStan's executable file is installed in Composer's `bin-dir` which defaults to `vendor/bin`.

Analysing code
--------------

To analyse your code, run the `analyse` command. Exit code 0 means there are no errors.

```bash
vendor/bin/phpstan analyse [options] [<paths>...]
```

As **`<paths>`** you can pass one or multiple paths to PHP files or directories separated by spaces. When looking for files in directories, various [configuration options](/config-reference#analysed-files) are respected. Relative paths are resolved based on the current working directory.

### `--level|-l`

Specifies the [rule level](/user-guide/rule-levels) to run.

### `--configuration|-c`

Specifies the path to a [configuration file](/config-reference). Relative paths are resolved based on the current working directory.

### `--generate-baseline|-b`

Generates [the baseline](/user-guide/baseline) to a file. Accepts a path (`--generate-baseline foo.neon`) which defaults to `phpstan-baseline.neon`. Relative paths are resolved based on the current working directory.

If you already use the baseline, the path to the baseline file should match the one already in use. This guarantees that the baseline isn't double-used and that the command functions correctly.

Please note that the exit code differs in this case. Exit code 0 means that the baseline generation was successful and the baseline is not empty. If there are no errors that the baseline would consist of, the exit code is 1.

By default PHPStan will not generate an empty baseline. However you can pass `--allow-empty-baseline` alongside `--generate-baseline` to allow an empty baseline file to be generated.

### `--pro`

Launches [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro){.phpstan-pro-label} which lets you browse errors (including ignored errors) in a beautiful web UI. Try it out by running PHPStan with `--pro` or by going to [account.phpstan.com](https://account.phpstan.com/) and creating an account.

<video class="w-full aspect-[1652/1080] mb-8 border border-gray-200 rounded-lg overflow-hidden" autoplay muted loop playsinline poster="/tmp/images/phpstan-pro-browsing-poster.jpg">
  <source src="/tmp/images/phpstan-pro-browsing.mp4" type="video/mp4">
</video>

### `--autoload-file|-a`

If your application uses a custom autoloader, you should set it up and register in a PHP file that is passed to this CLI option. Relative paths are resolved based on the current working directory.

[Learn more »](/user-guide/discovering-symbols)

### `--error-format`

Specifies a custom error formatter. [Learn more about output formats »](/user-guide/output-format)

### `--no-progress`

Turns off the progress bar. Does not accept any value.

### `--memory-limit`

Specifies the memory limit in the same format `php.ini` accepts.

Example: `--memory-limit 1G`

### `--xdebug`

PHPStan turns off Xdebug if it's enabled to achieve better performance.

If you need to debug PHPStan itself or your [custom extensions](/developing-extensions/extension-types) and want to run PHPStan with Xdebug enabled, pass this option. It does not accept any value.

### `--debug`

Instead of the progress bar, it outputs lines with each analysed file before its analysis.

Additionally, it stops on the first internal error and prints a stack trace.

This option also disables the [result cache](/user-guide/result-cache) and [parallel processing](/config-reference#parallel-processing) for debugging purposes.

### `-v`, `-vv`, `-vvv`

Increases the verbosity and makes PHPStan show various debugging information like consumed memory or [why result cache is not used](/user-guide/result-cache#debugging-the-result-cache).

Combining `-vvv` with `--debug` is great for [identifying slow files](/blog/debugging-performance-identify-slow-files).

Running with `-vvv` will also print same information as the [`diagnose` command](/user-guide/command-line-usage#diagnose-problems).

### `--ansi, --no-ansi`

Overrides the autodetection of whether colors should be used in the output and how nice the progress bar should be.

### `--quiet|-q`

Silences all the output. Useful if you're interested only in the exit code.

### `--version|-V`

Instead of running the analysis, it just outputs the current PHPStan version in use.

### `--help`

Outputs a summary of available CLI options, but not as in much detail as this page.

Running without arguments
--------------

You can analyse your project just by running `vendor/bin/phpstan` if you satisfy the following conditions:

* You have `phpstan.neon` or `phpstan.neon.dist` in your current working directory
* This file contains the [`paths`](/config-reference#analysed-files) parameter to set a list of analysed paths
* This file contains the [`level`](/config-reference#rule-level) parameter to set the current rule level


Clearing the result cache
--------------

To clear the current state of the [result cache](/user-guide/result-cache), for example if you're developing [custom extensions](/developing-extensions/extension-types) and the result cache is getting stale too often.

```bash
vendor/bin/phpstan clear-result-cache [options]
```

The `clear-result-cache` command shares some of the options with the `analyse` command. The reason is that the [configuration file](/config-reference) might be setting a custom [`tmpDir`](/config-reference#caching) which is where the result cache is saved.

### `--configuration|-c`

Specifies the path to a [configuration file](/config-reference). Relative paths are resolved based on the current working directory.

### `--autoload-file|-a`

If your application uses a custom autoloader, you should set it up and register in a PHP file that is passed to this CLI option. Relative paths are resolved based on the current working directory.

[Learn more »](/user-guide/discovering-symbols)

### `--memory-limit`

Specifies the memory limit in the same format `php.ini` accepts.

Example: `--memory-limit 1G`

### `--debug`

If the `clear-result-cache` command is failing with an uncaught exception, run it again with `--debug` to see the stack trace.

### `--quiet|-q`

Silences all the output. Useful if you're interested only in the exit code.

### `--version|-V`

Instead of clearing the result cache, it just outputs the current PHPStan version in use.

### `--help`

Outputs a summary of available CLI options, but not as in much detail as this page.


Diagnose problems
--------------

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.11.8</div>

To diagnose why PHPStan is behaving a certain way, you can run the `diagnose` command:

```bash
vendor/bin/phpstan diagnose [options]
```

It outputs useful information like current PHP runtime version, current PHP version for analysis (which might be different based on configuration), current PHPStan version etc. Custom extensions can also implement [`DiagnoseExtension interface`](https://apiref.phpstan.org/1.12.x/PHPStan.Diagnose.DiagnoseExtension.html) to add their own information that also gets printed when running the `diagnose` command.

The same information is also printed when you run [`analyse` command](/user-guide/command-line-usage#analysing-code) with `-vvv`.

### `--configuration|-c`

Specifies the path to a [configuration file](/config-reference). Relative paths are resolved based on the current working directory.

### `--autoload-file|-a`

If your application uses a custom autoloader, you should set it up and register in a PHP file that is passed to this CLI option. Relative paths are resolved based on the current working directory.

### `--level|-l`

Specifies the [rule level](/user-guide/rule-levels) to run.

### `--memory-limit`

Specifies the memory limit in the same format `php.ini` accepts.

Example: `--memory-limit 1G`

### `--debug`

Thrown exceptions (internal errors) will not be caught and their stack trace will be printed in full when this option is added.
