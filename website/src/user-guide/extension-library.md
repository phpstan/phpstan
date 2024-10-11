---
title: Extension Library
---

Unique feature of PHPStan is the ability to define and statically check "magic" behaviour of classes - accessing properties that are not defined in the class but are created in `__get` and `__set` and invoking methods using `__call`.

PHPStan also allows writing custom rules for situations that aren't objective bugs or other problems in the code, but allow people avoid tricky situations or enforce the way they want to write their code. These custom rules take advantage of the abstract syntax tree, advanced type inference engine, PHPDoc parser, and class reflection data.

[Learn more about writing custom extensions »](/developing-extensions/extension-types)

Installing extensions
-------------------------

Users can install various PHPStan extensions to enhance the capabilities of the static analyser. Many extensions already support [phpstan/extension-installer](https://github.com/phpstan/extension-installer) Composer plugin, so in order to enable an extension, it's sufficient to require it in your `composer.json`:

```bash
composer require --dev phpstan/extension-installer && \
composer require --dev phpstan/phpstan-beberlei-assert
```

If you can't or don't want to use [phpstan/extension-installer](https://github.com/phpstan/extension-installer), include the extension's configuration file manually in the `includes` section:

```yaml
includes:
	- vendor/phpstan/phpstan-beberlei-assert/extension.neon
```

<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">

**Why wouldn't I want to always use [phpstan/extension-installer](https://github.com/phpstan/extension-installer)?**

It always enables all the functionality that an extension offers. If you for example want to use only some of the rules from [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules), or if you only want to use `extension.neon` (but not `rules.neon`) from [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine), you can't use the extension installer plugin and must include chosen files manually.

</div>

Official extensions
---------------

Check out [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) repository for extra strict and opinionated rules for PHPStan.

Check out as well [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) for rules that detect usage of deprecated classes, methods, properties, constants and traits!

### Framework-specific extensions

* [Doctrine](https://github.com/phpstan/phpstan-doctrine)
* [PHPUnit](https://github.com/phpstan/phpstan-phpunit)
* [Symfony Framework](https://github.com/phpstan/phpstan-symfony)
* [beberlei/assert](https://github.com/phpstan/phpstan-beberlei-assert)
* [webmozart/assert](https://github.com/phpstan/phpstan-webmozart-assert)
* [Mockery](https://github.com/phpstan/phpstan-mockery)
* [azjezz/psl](https://github.com/php-standard-library/phpstan-extension)
* [Nette Framework](https://github.com/phpstan/phpstan-nette)
* [Dibi - Database Abstraction Library](https://github.com/phpstan/phpstan-dibi)

Unofficial extensions
-----------------

* Laravel ([Larastan](https://github.com/larastan/larastan) and [Bladestan](https://github.com/TomasVotruba/bladestan))
* [Drupal](https://github.com/mglaman/phpstan-drupal)
* [WordPress](https://github.com/szepeviktor/phpstan-wordpress)
* [Laminas](https://github.com/Slamdunk/phpstan-laminas-framework)
* [Phony](https://github.com/eloquent/phpstan-phony)
* [Prophecy](https://github.com/Jan0707/phpstan-prophecy)
* [marc-mabe/php-enum](https://github.com/marc-mabe/php-enum-phpstan)
* [myclabs/php-enum](https://github.com/timeweb/phpstan-enum)
* [Yii2](https://github.com/proget-hq/phpstan-yii2)
* [TYPO3](https://github.com/sascha-egerer/phpstan-typo3)
* [Nextras ORM](https://github.com/nextras/orm-phpstan)
* [Sonata](https://github.com/ekino/phpstan-sonata)
* [Magento](https://github.com/bitExpert/phpstan-magento)
* [CakePHP](https://github.com/CakeDC/cakephp-phpstan)
* [Safe PHP](https://github.com/thecodingmachine/phpstan-safe-rule)
* [psr/log](https://github.com/struggle-for-php/sfp-phpstan-psr-log)

3rd party rules
-----------------

* [thecodingmachine / phpstan-strict-rules](https://github.com/thecodingmachine/phpstan-strict-rules)
* [spaze / phpstan-disallowed-calls](https://github.com/spaze/phpstan-disallowed-calls)
* [ergebnis / phpstan-rules](https://github.com/ergebnis/phpstan-rules)
* [Slamdunk / phpstan-extensions](https://github.com/Slamdunk/phpstan-extensions)
* [ekino / phpstan-banned-code](https://github.com/ekino/phpstan-banned-code)
* [voku / phpstan-rules](https://github.com/voku/phpstan-rules)
* [shipmonk / phpstan-rules](https://github.com/shipmonk-rnd/phpstan-rules)
* [shipmonk / dead-code-detector](https://github.com/shipmonk-rnd/dead-code-detector)
* [shipmonk / phpstan-baseline-per-identifier](https://github.com/shipmonk-rnd/phpstan-baseline-per-identifier)
* [jiripudil / phpstan-sealed-classes](https://github.com/jiripudil/phpstan-sealed-classes)
* [sidz / phpstan-rules](https://github.com/sidz/phpstan-rules)
* [staabm / phpstan-todo-by](https://github.com/staabm/phpstan-todo-by)
* [staabm / phpstan-dba](https://github.com/staabm/phpstan-dba)
* [roave / no-floaters](https://github.com/Roave/no-floaters)
* [PHP Language Extensions](https://github.com/DaveLiddament/phpstan-php-language-extensions)

[**Find more on Packagist!**](https://packagist.org/?type=phpstan-extension)
