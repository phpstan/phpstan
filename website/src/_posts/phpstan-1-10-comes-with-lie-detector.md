---
title: "PHPStan 1.10 Comes With a Lie Detector"
date: 2023-02-21
tags: releases
---

I've been looking forward to implementing and releasing the ideas present in [PHPStan 1.10](https://github.com/phpstan/phpstan/releases/tag/1.10.0) for a long time.

Validate inline PHPDoc `@var` tag type
----------------

<blockquote class="twitter-tweet" data-dnt="true"><p lang="en" dir="ltr">My personal mission after PHPStan 1.0 is to eradicate inline @​var tags from existence. Developers reach for it as an immediate remedy for their problems but it&#39;s the worst solution ever.<br><br>With @​var tags you&#39;re giving up all the type safety static analysis offers. <a href="https://t.co/WvRvFooce4">pic.twitter.com/WvRvFooce4</a></p>&mdash; Ondřej Mirtes (@OndrejMirtes) <a href="https://twitter.com/OndrejMirtes/status/1458020442258739206?ref_src=twsrc%5Etfw">November 9, 2021</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

There are multiple problems with inline `@var` PHPDoc tag. PHP developers use it for two main reasons:

* To fix wrong 3rd party PHPDocs. A dependency [^not-use-sa] might have `@return string` in a PHPDoc but in reality can return `null` as well.
* To narrow down the returned type. When a function returns `string` but we know that in this case it can only return `non-empty-string`.

[^not-use-sa]: That probably doesn't use static analysis.

By looking at the analysed code we can't really tell which scenario it is. That's why PHPStan always trusted the type in `@var` and didn't report any possible mistakes. Obviously that's dangerous because the type in `@var` can get out of sync and be wrong really easily. But I came up with an idea what we could report without any false positives, keeping existing use-cases in mind.

With the latest release and [bleeding edge](https://phpstan.org/blog/what-is-bleeding-edge) enabled, PHPStan validates the inline `@var` tag type against the native type of the assigned expression. It finds the lies spread around in `@var` tags:

```php
function doFoo(): string
{
    // ...
}

/** @var string|null $a */
$a = doFoo();

// PHPDoc tag @var with type string|null is not subtype of native type string.
```

It doesn't make sense to allow `string|null`, because the type can never be `null`. PHPStan says "string|null is not subtype of native type string", implying that only subtypes are allowed. Subtype is the same type or narrower, meaning that `string` or `non-empty-string` would be okay.

By default PHPStan isn't going to report anything about the following code:

```php
/** @return string */
function doFoo()
{
    // ...
}

/** @var string|null $a */
$a = doFoo();
```

Because the `@return` PHPDoc might be wrong and that's what the `@var` tag might be trying to fix. If you want this scenario to be reported too, enable [`reportWrongPhpDocTypeInVarTag`](/config-reference#reportwrongphpdoctypeinvartag), or install [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

I'd like the PHP community to use inline `@var` tags less and less over time. There are many great alternatives that promote good practices and code deduplication: [Conditional return types](/writing-php-code/phpdoc-types#conditional-return-types), [`@phpstan-assert`](/writing-php-code/narrowing-types#custom-type-checking-functions-and-methods), [generics](/blog/generics-in-php-using-phpdocs), [stub files](/user-guide/stub-files) for overriding 3rd party PHPDocs, or [dynamic return type extensions](/developing-extensions/dynamic-return-type-extensions).

Encourage handling of all enum cases
----------------

What's wrong with the following example?

```php
enum Foo
{
	case ONE;
	case TWO;

	public function getLabel(): string
	{
		return match ($this) {
			self::ONE => 'One',
			self::TWO => 'Two',
			default => throw new \Exception('Unexpected case'),
		};
	}
}
```

PHPStan 1.9 didn't complain about anything in that code. But it has problems:

* The `default` arm isn't really needed.
* When we add `case THREE` in the enum, PHPStan will not tell us about an unhandled case, but in runtime the application will throw exceptions in our face.

PHPStan 1.10 reports "Match arm is unreachable because previous comparison is always true." for the line with the `default` case. It encourages removing the `default` case, which solves both problems at once.

When we add `case THREE`, PHPStan will now report: "Match expression does not handle remaining value: Foo::THREE". Which would not happen if the `default` case was still there.


Changes to "always true" expressions and unreachable code
----------------

There's a few more related changes spawned from the previous section. For a long time PHPStan reported inconsistently "always true" and "always false" conditions. But there was some logic to it. I didn't want you to have dead code.

```php
function doFoo(\Exception $o): void
{
	// not reported
	if ($o instanceof \Exception) {
		// code inside always executed
	}
}
```

If there was an `else` branch involved, PHPStan would report:

```php
function doFoo(\Exception $o): void {
	if ($o instanceof \Exception) {
	} else {
		// dead code here
		// reports:
		// Else branch is unreachable because previous condition is always true.
	}
}
```

You could turn on a few [specific options](https://github.com/phpstan/phpstan-strict-rules/blob/66b378f5b242130908b8a2222bf8110f14f4375a/rules.neon#L4-L7), or install [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules), to also have the `instanceof` reported as "always true".

I chose this way because I didn't want to discourage writing "safe" code like this, because some developers prefer it:

```php
// $foo is One|Two
if ($foo instanceof One) {

} elseif ($foo instanceof Two) {
    // PHPStan reports "instanceof always true", wants you to write "else {" instead
}
```

Very similar to the `match` example above, right? I also noticed and realized that people expect PHPStan to report "always true" out of the box more often than not, except for these examples.

With [bleeding edge](https://phpstan.org/blog/what-is-bleeding-edge) enabled and for everyone in the next major version, PHPStan will report "always true" by default, no extra options needed. To support the "last elseif" use-case, "always true" will not be reported for the last elseif condition and for the last arm in a match expression. You can override that with [`reportAlwaysTrueInLastCondition`](/config-reference#reportalwaystrueinlastcondition).

We no longer need unreachable branches reported at all - we'll find out about them thanks to "always true" errors from previous branches. These rules are now completely disabled in [bleeding edge](https://phpstan.org/blog/what-is-bleeding-edge).

Thanks to these changes, the error reported for the `enum` with the `default` case are different with and without bleeding edge on PHPStan 1.10:

```php
enum Foo
{
	case ONE;
	case TWO;

	public function getLabel(): string
	{
		return match ($this) {
			self::ONE => 'One',
			self::TWO => 'Two',
			default => throw new \Exception('Unexpected case'),
		};
	}
}
```

```diff
-13     Match arm is unreachable because previous comparison is always true.
+12     Match arm comparison between $this(aaa\Foo)&aaa\Foo::TWO and aaa\Foo::TWO is always true.
+       💡 Remove remaining cases below this one and this error will disappear too.
```

PHPStan tries to be helpful and shows a tip next to the 💡 emoji on the command line. These tips are now [incorporated into the playground](https://phpstan.org/r/1b9cf4e1-5c2a-4e2f-b56d-b0846a303bd5) as well. If you remove the `default` case, PHPStan will no longer complain about this piece of code. Until you add a new enum case.


Why my type isn't accepted here?
----------------

It's not always obvious what is PHPStan complaining about. Type safety is of the most importance, it really doesn't want to leave any gap for possible runtime errors. The developer might not know what situation it's trying to avoid with certain checks, and might not know why they should fix it and how.

PHPStan 1.10 includes helpful contextual tips in these less intuitive scenarios. My favourites are:

* [About `@template-covariant`](https://phpstan.org/r/61cfbb65-1a04-471a-a5c5-d61f0540ae1d)
* [About callable parameter contravariance](https://phpstan.org/r/24a23b74-af27-4443-986c-04af61427d50)
* [About complex array shapes](https://phpstan.org/r/fed1c275-46d0-434f-b9c4-3212f4df6d1c)


Deprecation of `instanceof *Type`
----------------

PHPStan 1.10 also comes with deprecations of less-than-ideal code patterns in custom rules and other extensions. I've written a separate article about those two weeks ago: [Why Is instanceof *Type Wrong and Getting Deprecated?](/blog/why-is-instanceof-type-wrong-and-getting-deprecated)


---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!
