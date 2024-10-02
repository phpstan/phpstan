---
title: "Detecting Unused Private Properties, Methods, and Constants"
date: 2020-07-20
tags: releases
---

In December 2015, a year before the first version of PHPStan saw the light of day, I released the [Slevomat Coding Standard](https://github.com/slevomat/coding-standard), a set of rules for PHP_CodeSniffer. [^kukulich]

[^kukulich]: Since then I transferred the maintenance duties to [Jarda "Kukulich" Hanslík](https://twitter.com/kukulich/) because my open-source energy has been fully spent working on PHPStan.

One of the first rules I added is about detecting unused and write-only properties:

```php
class Foo
{

    private Bar $bar; // only written - never read!

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }

}
```

The main motivation behind this rule was to prevent useless circular references between services in the dependency injection container. If the service `A` asks for service `B` in the constructor, service `B` asks for service `C`, and service `C` asks for `A` again, the whole object graph cannot be constructed because of this cycle. The solution is usually to somehow break up the objects into smaller ones, and create a tree instead of a cycle.

But what I sometimes found out that this cycle was a product of actually unused dependencies. So by cleaning up the dead code, we've had them happened far less often. And of course it's nice not having to maintain dead code. That's why the rule also recognizes unused methods and constants.

But PHP_CodeSniffer is limiting. You're only working with the [token stream](https://www.php.net/manual/en/function.token-get-all.php), the analysis is constrained to a single file at a time, and you don't have any type inference available. So writing custom rules for PHP_CodeSniffer fits best when you're checking code formatting only and not any advanced semantics.

This rule I'm talking about is on the edge of what's possible in PHP_CodeSniffer. I wrote it for it because in 2015 PHPStan wasn't a thing yet.

Let's consider this example:

```php
class Test
{

    public static function foo(): Generator
    {
        yield static fn (self $test) => $test->bar();
    }

    private function bar()
    {
    }
}

$test = new Test();
foreach ($test->foo() as $callback) {
    $callback($test);
}
```

The method `Test::bar()` is used in the callback, but PHP_CodeSniffer can't know that. It'd need a lot of legwork  to know that `$test` is of type `Test`. So the rule marks `Test::bar()` as unused.

Because bug reports about false-positives like these were piling up, we decided it'd be best to let PHPStan do this work, and deprecate the rule in Slevomat Coding Standard. PHPStan knows much more about the analysed code, and it's much easier to write rules for it in case you're interested in semantics and not code formatting. That's because it uses [AST](https://en.wikipedia.org/wiki/Abstract_syntax_tree) instead of the token stream, it has access to reflection so you're able to query all the information about classes and functions, and it knows the type of every variable thanks to the type inference engine.

The [latest PHPStan release](https://github.com/phpstan/phpstan/releases/tag/0.12.33) can now detect this. Because I don't want to add brand new rules in minor versions [^buildfail], you need to enable the *bleeding edge* setting by including `bleedingEdge.neon` in your `phpstan.neon`:

[^buildfail]: It'd certainly make a lot of build pipelines fail.

```yaml
includes:
	- vendor/phpstan/phpstan/conf/bleedingEdge.neon
```

It's for people who are eager to try out new features early and want all the available PHPStan capabilities instead of keeping things super stable (and let others work out the kinks).

What about never-written (read-only) properties?
-----------------------

The original Slevomat Coding Standard rule only detected completely untouched and write-only (never read) properties. The PHPStan implementation also detects properties that are read but never written, like this one:

```php
class Foo
{

    private Bar $bar; // only read - never written!

    public function getBar(Bar $bar): Bar
    {
        return $this->bar;
    }

}
```

A missing constructor or setter injection is usually a sign of a serious bug or dead code.

What if my code is "special"?
-----------------------

There are some examples where static analysis might think the code is wrong but it's actually fine:

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
```

This is a [Doctrine](https://www.doctrine-project.org/projects/orm.html) entity. The property is never assigned because the value will be automatically filled by the framework when the entity is saved to the database. So we don't need to set it in our code, it's taken care of.

For cases like this I added extension capability. Implement this [simple interface](https://apiref.phpstan.org/2.0.x/PHPStan.Rules.Properties.ReadWritePropertiesExtension.html) to tell PHPStan your properties are always read or written even if it might not seem like it in the code:

```php
interface ReadWritePropertiesExtension
{

	public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool;

	public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool;

	public function isInitialized(PropertyReflection $property, string $propertyName): bool;

}
```

It also needs to be registered in your phpstan.neon:

```yaml
services:
	-
		class: MyApp\PHPStan\PropertiesExtension
		tags:
			- phpstan.properties.readWriteExtension
```

For [Doctrine the logic](https://github.com/phpstan/phpstan-doctrine/blob/ecc4aecaaf34871a2961c4c7a046bc2e092b0300/src/Rules/Doctrine/ORM/PropertiesExtension.php) is following:

* Property is always read (it doesn't need a getter) if it's a persisted field or association. This is because we might save some data into the database without reading them in the code, only referencing the fields in DQL queries, or maybe having them read directly from the database by another company department.
* Property is always written (it doesn't need assigning in code) if it's a primary key with a generated value, or if it's a [read-only entity](https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/reference/attributes-reference.html#attrref_entity) without a constructor. Records for read-only entities are usually inserted into the database through some other means and are for tables where data doesn't change often.

Always-used class constants
-----------------------

A similar interface ([`AlwaysUsedClassConstantsExtension`](https://apiref.phpstan.org/2.0.x/PHPStan.Rules.Constants.AlwaysUsedClassConstantsExtension.html)) is available to mark private class constants as always-used. This is useful for custom implementations of enums where the only way these constants are read is through reflection.

```php
use PHPStan\Reflection\ConstantReflection;

interface AlwaysUsedClassConstantsExtension
{

	public function isAlwaysUsed(ConstantReflection $constant): bool;

}
```

Register your own implementation of this interface in phpstan.neon:

```yaml
services:
	-
		class: MyApp\PHPStan\ConstantsExtension
		tags:
			- phpstan.constants.alwaysUsedClassConstantsExtension
```

One more thing
-----------------------

PHP 7.4 brought native typed properties:

```php
private int $count;
```

These properties aren't null after the object is created, but are uninitialized. If you access it before assigning a value, you end up with an error:

> Typed property `Foo::$count` must not be accessed before initialization

In practice you'll encounter this error if you call the getter before the setter:

```php
class Foo
{

    private int $count;

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getCount(): int
    {
        return $this->count;
    }

}
```

The only way to reliably prevent this error is to always assign typed properties in the constructor. But there's a catch: PHP doesn't require this. The only language requirement is that you assign the property before you access it. You can also ask about uninitialized state using `isset($this->count)` - that doesn't trigger the error.

So it doesn't feel right to require the property assignment in the constructor in every case. PHPStan doesn't know the object's lifecycle, it's possible it's used correctly from the outside and that the code isn't actually broken. But for those who want to enforce typed properties being assigned in the constructor, there's a setting:

```yaml
parameters:
    checkUninitializedProperties: true
```

And the extension's `isInitialized` method is there to tell PHPStan that a property is always initialized even if it's not being assigned in the constructor. The [Doctrine extension](https://github.com/phpstan/phpstan-doctrine/blob/f855ebad8c685c6ab6430ae5825f1f3af3ec7b4b/src/Rules/Doctrine/ORM/PropertiesExtension.php) does it for all persisted properties in read-only entities without a constructor, because they're most likely inserted directly into the database without ORM's involvement.

Also, the [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) will consider the TestCase's `setUp()` method as a constructor and will mark properties assigned there as initialized.

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!

