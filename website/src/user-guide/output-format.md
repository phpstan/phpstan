---
title: Output Format
---

PHPStan supports different output formats through various so-called error formatters.

You can pass the following keywords to the `--error-format=X` CLI option of the `analyse` command in order to affect the output:

- `table`: Default. Grouped errors by file, colorized. For human consumption.
- `raw`: Contains one error per line, with path to file, line number, and error description
- `checkstyle`: Creates a checkstyle.xml compatible output. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `json`: Creates minified .json output without whitespaces. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `prettyJson`: Creates human readable .json output with whitespaces and indentations. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `junit`: Creates JUnit compatible output. Note that you'd have to redirect output into a file in order to capture the results for later processing.
- `github`: Creates GitHub Actions compatible output.
- `gitlab`: Creates format for use Code Quality widget on GitLab Merge Request.
- `teamcity`: Creates TeamCity compatible output.

You can also implement your own custom error formatter. [Learn how »](/developing-extensions/error-formatters)
