---
title: Output Format
---

PHPStan supports different output formats through various so-called error formatters.

You can pass the following keywords to the `--error-format=X` CLI option of the `analyse` command in order to affect the output:

- `table`: Default. Grouped errors by file, colorized. For human consumption. Additionally, the `table` formatter will detect it runs in a Continuous Integration environment like GitHub Actions and TeamCity, and besides the table it will also output errors in the specific format for that environment.
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

Setting this parameter should most likely be done in [your local configuration file](/config-reference#multiple-files) that isn't commited to version control. The common pattern is to have `phpstan.neon.dist` with project-specific settings shared by everyone on the team, and *.gitignored* `phpstan.neon` that includes `phpstan.neon.dist` and overrides values specific to a single developer:

```neon
includes:
	- phpstan.neon.dist

parameters:
	editorUrl: 'phpstorm://open?file=%%file%%&line=%%line%%'
```

To make the text really clickable in your terminal you might need to register the protocol in your system; see [eclemens/atom-url-handler](https://github.com/eclemens/atom-url-handler) for an example.
