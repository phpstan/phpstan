---
title: "PHPStan 2.0 Released With Level 10 and Elephpants!"
date: 2024-11-11
tags: releases
---

<img src="/images/phpstan-2-0.jpg" alt="PHPStan 2.0" class="rounded-lg mb-8">

PHPStan 1.0 was released a little over [three years ago](/blog/phpstan-1-0-released). I'm happy to report the project is thriving! We did about 176 new releases since then, implementing new features, fixing bugs, and laying the groundwork for 2.0. Yeah, we didn't catch a break and we didn't rest on our laurels.

I've been looking forward to 2.0 for a long time. Everyone will finally be able to enjoy new features we've been working on. Some of them have already been enjoyed by [early adopters](/blog/what-is-bleeding-edge) for more than two years.

But code and analysis changes are not the only things being released today! PHPStan joins [the family of elephpants](https://elephpant.me/) with its own take on the legendary PHP mascot.

[![PHPStan Elephpant](/images/elephpant-trio.png)](/merch) {.mt-4 .rounded-lg .mb-8 .border .border-blue-500 .p-4 .hover:border-blue-400}

You can also order PHPStan T-shirts again, in both blue and white, and straight/fitted cut:

<a href="/merch" class="flex w-full justify-center mt-4 rounded-lg mb-8 border border-blue-500 py-2 hover:border-blue-400">
	<img class="aspect-[1610/913] max-w-xs w-1/2" src="/images/tshirt-2024-blue-straight.jpg">
	<img class="aspect-[1610/913] max-w-xs w-1/2" src="/images/tshirt-2024-white-fitted.jpg">
</a>

We're [accepting orders](/merch) for the next four weeks (until Sunday, December 8th), so don't miss the opportunity and order the elephpant and T-shirt today! I can't wait to see them in the wild.

---

And now to the code part. The [comprehensive release notes](https://github.com/phpstan/phpstan/releases/tag/2.0.0) are massive, consisting of over 180 items. That's why, for the first time ever, PHPStan 2.0 also comes with easy-to-follow [upgrading guide](/blog/upgrading-from-phpstan-1-to-2), for both end users and extension developers.

It's really hard to pick and choose my favourite features and changes from 2.0, but I'll try. Here we go:

Level 10
-----------------------------

New challenge for the level max enthusiasts! Previously added level 9 acknowledges using `mixed` in your code isn’t actually safe at all, and that you should really do something about it. But it has some blind spots and still lets some errors through. Internally it's named `checkExplicitMixed`. Meaning it will report errors for explicitly-typed `mixed` values in your code.

Level 6 forces you to add missing types. If you manage to skip that with the help of <del>cheating</del>, ahem, [the baseline](/user-guide/baseline), or if you call a third party code that doesn't use a lot of return types, unknown types may be present in your code during analysis. These are implicitly-typed `mixed`, and they will be picked up by level 10

List type
-----------------------------

PHP arrays are really powerful, but they represent several computer science concepts in a single data structure, and sometimes it's difficult to work with that. That's why it's useful to narrow it down when we're sure we only want a single concept like a list.

[List](https://en.wikipedia.org/wiki/List_(abstract_data_type)) in PHPStan is an array with sequential integer keys starting at 0 and with no gaps. It joins [many other advanced types expressible in PHPDocs](/writing-php-code/phpdoc-types):

```php
/** @param list<int> $listOfIntegers */
public function doFoo(array $listOfIntegers): void
{
}
```

Lower memory consumption leads to faster performance
-----------------------------

PHPStan used to be a really hungry beast. To the point of being killed by CI runners because it consumed not just all the memory up to the `memory_limit` in php.ini, but also all the memory assigned to the runner hardware.

Now it's a less hungry beast. How did I make it happen? It's useful to realize what's going on inside a running program. It's fine to consume memory as useful things are being achieved, but in order to consume less memory in total, it has to be freed afterwards so that it can be used again by different data needed when analysing the next file in line.

To debug memory leaks, I use the [`php-meminfo`](https://github.com/BitOne/php-meminfo) extension. I quickly realized that most of the memory is occupied by [AST](/developing-extensions/abstract-syntax-tree) nodes. PHP frees the memory occupied by an object when there are no more references to it [^refcount]. It didn't work in case of AST nodes because they kept pointing at each other:

[^refcount]: That's called reference counting.

{% mermaid %}
flowchart LR;
TryCatch== stmts ==>array
array== 0 ==>Stmt1
array== 1 ==>Stmt2
array== 2 ==>Stmt3
Stmt1== parent ==>TryCatch
Stmt2== parent ==>TryCatch
Stmt3== parent ==>TryCatch
Stmt1== next ==>Stmt2
Stmt2== next ==>Stmt3
Stmt2== prev ==>Stmt1
Stmt3== prev ==>Stmt2
{% endmermaid %}

Getting rid of `parent`/`previous`/`next` node attributes is a backward compatibility break for custom rules that read them. I've written [an article on how to make these rules work again](/blog/preprocessing-ast-for-custom-rules) even without those memory-consuming references.

The object graph is now obviously cleaner and with no reference cycles:

{% mermaid %}
flowchart LR;
TryCatch== stmts ==>array
array== 0 ==>Stmt1
array== 1 ==>Stmt2
array== 2 ==>Stmt3
{% endmermaid %}

In my testing PHPStan now consumes around 50–70 % less memory on huge projects with thousands of files. Analysing PrestaShop 8.0 codebase now takes 3 minutes instead of 9 minutes in GitHub Actions with 2 CPU cores.


Validate inline PHPDoc `@var` tag type
----------------

There are multiple problems with inline `@var` PHPDoc tag. PHP developers use it for two main reasons:

* To fix wrong 3rd party PHPDocs. A dependency [^not-use-sa] might have `@return string` in a PHPDoc but in reality can return `null` as well.
* To narrow down the returned type. When a function returns `string|null` but we know that in this case it can only return `string`.

[^not-use-sa]: That probably doesn't use static analysis.

```php
/** @var Something $a */
$a = makeSomething();
```

By looking at the analysed code we can't really tell which scenario it is. That's why PHPStan always trusted the type in `@var` and didn't report any possible mistakes. Obviously that's dangerous because the type in `@var` can get out of sync and be wrong really easily. But I came up with an idea what we could report without any false positives, keeping existing use-cases in mind.

PHPStan 2.0 validates the inline `@var` tag type against the native type of the assigned expression. It finds the lies spread around in `@var` tags:

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

Checking truthiness of `@phpstan-pure` with impure points
----------------

Pure functions always return the same value for the same input (arguments and current object state). They also don't have any side effects like depending on current time, random number generator, or IO like reading file contents or accessing resources over the network.

It's useful to mark functions and methods with `@phpstan-pure` if their logic should be pure. PHPStan 2.0 now enforces this annotation, meaning it will report any impure code inside as an error.

We achieved that with the help of impure points. For every statement and expression type PHP supports PHPStan decides if it's pure or not.

Knowing impure points for any code also helps PHPStan to report more dead code. There's no point in calling a pure method on a separate line without using its result. If it does not have any side effect, and we're not using the returned value, then we can safely delete this line:

```php
// Call to method Foo::pureMethod() on a separate line has no effect.
$this->pureMethod();
```

Also: if a `void` method does not have any impure points, it doesn't have to exist:

```php
// Method Foo::add() returns void but does not have any side effects.
private function add(int $a, int $b): void
{
    $c = $a + $b;
}
```

Less caching, disk space cleanup
----------------

Getting rid of a cache without slowing down the analysis is a clear win. Less stuff to invalidate, less stuff to worry about, less disk space to occupy.

PHPStan used to cache information whether a function is variadic because of [`func_get_args()`](https://www.php.net/manual/en/function.func-get-args.php) call or similar in its body. I realized we have this information freshly available in each run anyway, so we don't need to cache it. This saves literally tens of thousands of files that PHPStan previously needed to save to disk and then read.

PHPStan now solely relies on the [result cache](https://phpstan.org/user-guide/result-cache) to speed up the analysis.

We took this opportunity to clean up old cache items on disk, so on the first PHPStan 2.0 run you might see some previously occupied disk space free up. On a proprietary project I test PHPStan on it freed up about 1 GB of space.

-----------

The future
-------------

I said it [three years ago](/blog/phpstan-1-0-released#a-bright-future), and I'm saying it again. PHPStan's future is bright. It's my full-time job thanks to [PHPStan Pro](https://phpstan.org/blog/introducing-phpstan-pro) and [GitHub Sponsors](/sponsor), splitting the revenue roughly 50-50 between them.

I love my job, I love PHP and I don't plan to stop. Contributors are [very active](https://github.com/phpstan/phpstan-src/graphs/contributors?from=11.+11.+2023) and indispensable. PHPStan has a thriving [ecosystem of extensions](/user-guide/extension-library) as well.

I have a lot of ambitious and even crazy ideas I'd like to try out. But the first thing I will address once the dust settles on 2.0 is adding support for PHP 8.4. You can expect it to be added before the end of 2024.


---

Do you like PHPStan and use it every day? [**Consider sponsoring** further development of PHPStan on GitHub Sponsors and also **subscribe to PHPStan Pro**](/sponsor)! I’d really appreciate it!
