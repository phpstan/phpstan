---
title: "Restricted Usage Extensions - You Don't Always Need a Custom Rule"
date: 2025-04-27
tags: releases
---

Recently I've wanted to implement support for the `@internal` PHPDoc tag. The good news was there was already a similar set of rules in [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) for the `@deprecated` tag. The bad news was the logic around `@deprecated` was hardcoded there, meaning I'd have to copy and adjust all the rules.

I thought there should be a better way because this is a very common use-case and not everyone should be forced to reinvent the same wheel. If people have a need for rules around attributes like [`#[NamespaceVisibility]`](https://github.com/DaveLiddament/php-language-extensions#namespacevisibility) or [`#[Friend]`](https://github.com/DaveLiddament/php-language-extensions#friend), it should not be their job to figure out all the rules needed for a comprehensive coverage of these restrictions.

Take methods for example: Everyone would implement a rule for a traditional method call like `$foo->doBar(1, 2, 3)`, but only a few would remember that [first-class callables](https://wiki.php.net/rfc/first_class_callable_syntax) like `$foo->doBar(...)` should be covered as well.

Or class names. The new extension currently [covers 26 locations](https://github.com/phpstan/phpstan-src/blob/ecfc5417bb2bd2f9f478f0c005b10ab1937cf149/src/Rules/ClassNameUsageLocation.php#L18-L43) where a class name can occur in PHP code or [PHPDocs](https://phpstan.org/writing-php-code/phpdocs-basics). The great advantage of the extension interface is that it's future-proof and we can call it as new places with class names appear in the PHP language or as we add new PHPDoc features.

Besides taking advantage of the new extensions to implement the [`@internal` tag rules](https://github.com/phpstan/phpstan-src/tree/__BRANCH__/src/Rules/InternalTag), I went back to phpstan-deprecation-rules and [refactored](https://github.com/phpstan/phpstan-deprecation-rules/compare/96f93574dd20a293df14700e84502123103178d7...9d8e7d4e32711715ad78a1fb6ec368df9af01fdf) it with these new capabilities. It fixed small inconsistencies like missing class name check for static property fetch, wrong class names in some error messages, or reported line numbers in multi-line function signatures. With [bleeding edge](/blog/what-is-bleeding-edge) enabled, it will report deprecated class names in more places.

The Restricted Usage Extensions were released in [PHPStan 2.1.13](https://github.com/phpstan/phpstan/releases/tag/2.1.13). Head over to the [developer guide](/developing-extensions/restricted-usage-extensions) to learn how to use them!

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan**](/sponsor). I’d really appreciate it!
