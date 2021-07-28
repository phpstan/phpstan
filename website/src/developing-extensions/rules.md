---
title: Custom Rules
---

PHPStan allows writing custom rules to check for specific situations in your own codebase. The implementation is all about applying the [core concepts](/developing-extensions/core-concepts) so check out that guide first and then continue here.

Your rule class needs to implement the [`PHPStan\Rules\Rule`](https://github.com/phpstan/phpstan-src/blob/master/src/Rules/Rule.php) interface and registered as a service in the [configuration file](/config-reference):

```yaml
services:
	-
		class: MyApp\PHPStan\Rules\DefaultValueTypesAssignedToPropertiesRule
		tags:
			- phpstan.rules.rule
```

For inspiration on how to implement a rule turn to [src/Rules](https://github.com/phpstan/phpstan-src/tree/master/src/Rules) to see a lot of built-in rules.
