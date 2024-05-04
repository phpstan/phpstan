---
title: Output Format
---

PHPStan supports different output formats through various so-called error formatters.

You can pass the following keywords to the `--error-format=X` CLI option of the `analyse` command in order to affect the output:

- `table`: Default. Grouped errors by file, colorized. For human consumption. Additionally, the `table` formatter will detect if it runs in a Continuous Integration environment like GitHub Actions and TeamCity, and besides the table it will also output errors in the specific format for that environment.
- `raw`: Contains one error per line, with path to file, line number, and error description
- `checkstyle`: Creates a checkstyle.xml compatible output. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `json`: Creates minified .json output without whitespaces. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `prettyJson`: Creates human readable .json output with whitespaces and indentations. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `junit`: Creates JUnit compatible output. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `github`: Creates GitHub Actions compatible output.
- `gitlab`: Creates format for use Code Quality widget on GitLab Merge Request.
- `teamcity`: Creates TeamCity compatible output.

You can also implement your own custom error formatter. [Learn how »](/developing-extensions/error-formatters)

You can change the default error format in the configuration. [Learn how »](/config-reference#errorformat)

Opening file in an editor
--------------

The default `table` error formatter offers a configuration parameter `editorUrl` that lets you specify a URL with placeholders that will be printed next to the error message in the output:

```
 ------ -------------------------------------------------------------------
  Line   test.php
 ------ -------------------------------------------------------------------
  3      Parameter #1 (stdClass) of echo cannot be converted to string.
         ✏️  phpstorm://open?file=/home/dev/test.php&line=3
 ------ -------------------------------------------------------------------
```

Here's how the parameter can be set in the [configuration file](/config-reference):

```neon
parameters:
	editorUrl: 'phpstorm://open?file=%%file%%&line=%%line%%'
```

Examples of URLs for the most common editors are:

* PhpStorm: `'phpstorm://open?file=%%file%%&line=%%line%%'`
* Visual Studio Code: `'vscode://file/%%file%%:%%line%%'`
* Atom: `'atom://core/open/file?filename=%%file%%&line=%%line%%'`

Setting this parameter should most likely be done in [your local configuration file](/config-reference#multiple-files) that isn't committed to version control. The common pattern is to have `phpstan.neon.dist` with project-specific settings shared by everyone on the team, and *.gitignored* `phpstan.neon` that includes `phpstan.neon.dist` and overrides values specific to a single developer:

```neon
includes:
	- phpstan.neon.dist

parameters:
	editorUrl: 'phpstorm://open?file=%%file%%&line=%%line%%'
```

To make the text really clickable in your terminal you might need to register the protocol in your system; see [eclemens/atom-url-handler](https://github.com/eclemens/atom-url-handler) for an example.

<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.7.15</div>

If you run PHPStan analysis within Docker container (or using other virtualization tools) you may need to use `%relFile%` instead of `%file%`. This will use file's path relative to the current working directory. In the end your `editorUrl` should look like this: `phpstorm://open?file=/path/to/your/project/%%relFile%%&line=%%line%%`.


<div class="text-xs inline-block border border-green-600 text-green-600 bg-green-100 rounded px-1 mb-4">Available in PHPStan 1.9.3</div>

You may also want to change the default title of the clickable link to contain line to be able to quickly copy-paste it to your IDE when used within an environment that is not clickable (like CI output). Here is how:
```neon
parameters:
	editorUrlTitle: '%%relFile%%:%%line%%'
```

--------------------

Since every team member working on the same project will likely have different absolute path to the project, machine-specific `editorUrl` should be used in `phpstan.neon` [paired with `phpstan.neon.dist` where all the common project settings live](/config-reference#multiple-files).
