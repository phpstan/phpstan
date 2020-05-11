---
title: The Baseline
---

The fundamental way of how PHPStan handles and accommodates analysis of codebases of varying quality is the concept of [rule levels](/user-guide/rule-levels). It allows developers to start using PHPStan on a project right away, and raise the strictness accompanied by a more confident type-safety as they fix the errors.

The usual workflow when introducing PHPStan to the CI pipeline is to get the number of errors reported on level 0 to zero and merge that into the main branch. When the developers feel like it, they can try raising the level by one, go through the list of errors, fix all of them, and enjoy increased strictness from that point on.

Rule levels are serving us well, but they come with a few caveats:

1. Upgrade to a new and more capable version of PHPStan might represent a substantial amount of work —you need to fix all the new errors to keep the same level of analysis.
2. Developers are missing out on real bugs when they’re running one of the lower levels.
3. Introducing new advanced custom rules also comes with the need to fix all the violations before the ability to use them.

This is where the baseline comes into play.

PHPStan allows you to declare the **currently reported list of errors as "the baseline"** and cause it not being reported in subsequent runs. It allows you to be interested in violations only in new and changed code.

It works best when you want to get rid of a few dozen to a few hundred reported errors that you don’t have time or energy to deal with right now. It’s not the best tool when you have 15,000 errors — you should probably spend more time configuring PHPStan or run it with a lower rule level.

Generating the baseline
--------------

If you want to export the current list of errors and use it as the baseline, run PHPStan with `--generate-baseline` option:

```bash
vendor/bin/phpstan analyse --level 7 \
  --configuration phpstan.neon \
  src/ tests/ --generate-baseline
```

It generates the list of errors with the number of occurrences per file and saves it as `phpstan-baseline.neon`. You can also specify a different filename after the `--generate-baseline` CLI option.

This is how the baseline file looks like:

```yaml
parameters:
	ignoreErrors:
		-
			message: "#^Only numeric types are allowed in pre\\-decrement, bool\\|float\\|int\\|string\\|null given\\.$#"
			count: 1
			path: src/Analyser/Scope.php
		-
			message: "#^Anonymous function has an unused use \\$container\\.$#"
			count: 2
			path: src/Command/CommandHelper.php
```

You should then include this file in the [configuration file](/config-reference):

```yaml
includes:
	- phpstan-baseline.neon

parameters:
	# your usual configuration options
```

Next time you run PHPStan, the errors in the baseline will be skipped in the analysis result. You can manage the baseline manually by editing the file, or generate the whole file again by running PHPStan with `--generate-baseline`.

If some of the ignored errors do not occur in the result anymore, PHPStan will let you know and you will have to remove the pattern from the baseline file. You can turn off this behaviour by setting `reportUnmatchedIgnoredErrors` to `false` in the configuration:

```yaml
parameters:
	reportUnmatchedIgnoredErrors: false
```

The use-cases
------------------

Baseline successfully solves all the problems mentioned above.

### Upgrade to new versions of PHPStan immediately

It’s especially great if you’re on ancient release branches of PHPStan like 0.10.x or even 0.9.x. You might have been postponing the upgrade because you tried it but had been scared away by hundreds of new errors found by PHPStan. But with the baseline, you can take advantage of the new rules immediately. They will be analysing new code you write with a more critical eye, while the issues found in the old code wait silently in the baseline for you to be fixed when you feel like it.

### Run higher rule level even if there are errors

If you achieved zero errors on level 2, congratulations to you! PHPStan now reports calls to unknown methods on all variables, or validates types in phpDocs, besides dozens of other checks. But it won’t tell you that you’re passing wrong argument types to a method until level 5!

So you’re missing out on real bugs that might get into production.

With the baseline you can for example run PHPStan on level 5, and export all reported errors. The effect is that the new code you write will be checked more strictly — barring any new bugs to get into production.

### Introduce custom rules and hold new code to a higher standard

One strategy to fight technical debt is to come up with best practices you want to follow and gradually apply them to the codebase and especially to new code you write.

With the baseline, you can enable [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) or exquisite rules from the [ergebnis/phpstan-rules](https://github.com/ergebnis/phpstan-rules) package and have them applied only to new code. For example, if you choose to never extend any class again (because inheritance is evil and you should only use composition), you can enable this rule but don’t have to deal with all existing classes with parents right away.
