---
title: "PHPStan 2.3: Leap in Performance, Generics Improvements, Detecting Unused Variables, and More!"
date: 2026-10-06
tags: releases
socialImage: /images/phpstan-2-3-performance.png
---

It's been over four months and [16 patch releases](https://github.com/phpstan/phpstan/releases) since [the last minor PHPStan release](/blog/phpstan-2-2-unsealed-array-shapes-safer-array-keys). Which means it's about time for the next one! [PHPStan 2.3.0](https://github.com/phpstan/phpstan/releases/tag/2.3.0) is here.




Performance improvements
---------------

Since the beginning of this year we've been hard at work improving PHPStan's performance and we've been very successful at it. Nothing shows it better than the following chart. On my computer (a 5-year-old MacBook Pro with an M1 Pro CPU), in December 2025 it used to take 60 seconds to analyse WordPress source code. And today it only takes 8 seconds:

<div class="line-chart">

<script type="application/json">
{
	"x": "Version",
	"y": {"suffix": " s", "decimals": 1},
	"tooltip": {"title": "PHPStan {Version}", "subtitle": "{Released}", "description": "{Note}"},
	"series": [
		{"id": "off", "column": "Turbo off (s)", "label": "Turbo off", "color": "text-blue-600", "labels": ["first", "last"]},
		{"id": "on", "column": "Turbo on (s)", "label": "Turbo on", "color": "text-amber-500"}
	],
	"comparisons": [{"from": "off", "to": "on", "text": "Turbo is {percent}% faster"}],
	"description": "Time to analyse WordPress core across PHPStan releases, with and without Turbo."
}
</script>

| Version | Released | Turbo off (s) | Turbo on (s) | Note |
|---|---|--:|--:|---|
| 2.1.33 | December 5th 2025 | 60.51 |  | Last release before this year's performance improvements |
| 2.1.34 | January 19th 2026 | 46.51 |  | Caching reflection objects and PHPDocs |
| 2.1.38 | January 30th 2026 | 44.55 |  | Minor performance improvements |
| 2.1.49 | April 16th 2026 | 34.49 |  | More performance improvements |
| 2.2.6 | July 26th 2026 | 32.02 | 24.68 | First release with Turbo |
| 2.2.10 | August 30th 2026 | 29.94 | 22.20 | Forking workers instead of spawning |
| 2.2.13 | September 3rd 2026 | 23.06 | 14.12 | Always running with OPcache, stripping runtime checks |
| 2.3.0 | October 1st 2026 | 19.47 | 7.93 | A lot more classes shadowed by Turbo |

</div>




The first data point is PHPStan 2.1.33 which is the last release before all the contemporary performance work took place. Since then we have been chopping away at work that PHPStan doesn't have to do at all, doesn't have to do repeatedly, or that can be done faster. You can see it in the blue line getting steadily lower.

But PHPStan 2.2.6 at the end of July brought something new into the mix: PHPStan Turbo, an optional extension that makes parts of PHPStan faster by reimplementing them in C++. It also enables other optimization techniques: forking child processes instead of spawning them, which normally isn't possible when running from a PHAR file, and registering an OPcache optimizer pass that strips runtime type checks from PHPStan's calls to itself. It deserves a separate article which I will publish later this week.

Optional means PHPStan still runs fine without the Turbo extension. The twist is that the majority of PHPStan users have it enabled automatically and thus are already running PHPStan at the highest speed, without having to manually manage the extension on their system. The extension's .so/.dll files are bundled inside the `phpstan/phpstan` Composer package, PHPStan selects the correct one for the current PHP runtime, and restarts itself with the extension enabled through `php -d` command line options.

You can see the effect in the orange line: the initial release made PHPStan 23% faster against the blue baseline. But today Turbo makes PHPStan almost 60% faster.

To summarize: PHPStan today is up to 7.5× faster than PHPStan as of ten months ago!


Result cache improvements
---------------

But that's not even the whole story. That's only about the "cold" runs when the [result cache](/user-guide/result-cache) does not exist or is too old. PHPStan doesn't always run analysis on all files in a project. Since [March 2020](/blog/from-minutes-to-seconds-massive-performance-gains-in-phpstan) [^covid] it remembers what errors it reported in which files during previous runs. It checks which files have changed since then, and only reanalyses those, plus files dependent on the changed files.

[^covid]: So for about 18 years.

The day-to-day experience of people running PHPStan isn't waiting for the full run to finish every time. It depends on how many files were changed since the last run. The following chart shows runs with a warm result cache on Drupal, a much larger project (a full run takes 22 seconds, compared to 8 seconds for WordPress):

<div class="line-chart">

<script type="application/json">
{
	"x": "Files",
	"xDetail": "Detail",
	"y": {"suffix": " s", "decimals": 1},
	"tooltip": {"title": "{Files} files reanalysed", "subtitle": "{Share} of 11,194 files", "description": "{Note}"},
	"series": [
		{"column": "Time (s)", "label": "Time", "color": "text-amber-500", "labels": ["first", "last"]}
	],
	"description": "Time to analyse Drupal core with a warm result cache, depending on how many files have to be reanalysed.",
	"height": {"ratio": 0.42, "min": 280, "max": 300}
}
</script>

| Files | Detail | Share | Time (s) | Note |
|--:|---|--:|--:|---|
| 0 | no change | 0% | 1.58 | Nothing changed since the last run |
| 10 | 0.1% | 0.1% | 2.84 |  |
| 1,124 | 10% | 10.0% | 6.21 |  |
| 2,771 | 25% | 24.8% | 9.89 |  |
| 5,574 | 50% | 49.8% | 16.10 |  |
| 7,204 | 64% | 64.4% | 19.03 |  |
| 8,956 | 80% | 80.0% | 21.14 |  |
| 11,194 | cache cleared | 100% | 22.08 | Result cache cleared |

</div>

Re-running PHPStan without changing anything takes just 1.6 seconds to print the results. When PHPStan decides 10 files need to be reanalysed, it takes 2.8 seconds. When 10% of the project is reanalysed, it takes 6.2 seconds.

The recent result cache improvements are about making sure the whole result cache is invalidated and thrown away only when PHPStan is updated, or when the project's PHPStan configuration changes. Previously, the project was fully reanalysed when any Composer dependency was updated, or when the Symfony DI container changed in any way. We've started tracking the dependencies between analysed files and installed packages so only the precise minimum is reanalysed.

We're also introducing new [extension interfaces and APIs](/developing-extensions/result-cache-meta-extensions#tracking-dependencies-on-files) that custom extensions can take advantage of to describe which files need to be invalidated and when, instead of throwing the entire result cache away when any minor detail changes.

The result cache now also behaves properly in monorepo setups that take advantage of `"type": "path"` [repositories](https://getcomposer.org/doc/04-schema.md#repositories) in `composer.json`, or [scanDirectories & scanFiles](/user-guide/discovering-symbols#third-party-code-outside-of-composer-dependencies) of often-changed files.

Result cache is made to be saved and restored in CI for vastly faster pipelines. Make sure to [configure that](/user-guide/result-cache#setup-in-continuous-integration)!



Generics: no more scalar generalization,<br>multi-pass inference for `new Foo()` from usages
-------------------

PHPStan has had generics support [for almost seven years](/blog/phpstan-0-12-released). We had to make a bunch of tough choices during development, and a few times we had to guess "how this should work" purely on gut feeling. Spoiler alert: some of those choices were wrong!

For instance, take the following function that returns the same type it accepts in its parameter:

```php
/**
 * @template T
 * @param T $a
 * @return T
 */
function doFoo($a)
{
    // ...
}
```

What return type should be inferred when calling `doFoo(1)`? Is it `int`? Is it `1`? There's no correct answer; each comes with different trade-offs.

We opted for generalizing passed scalars so `1` becomes `int`. In hindsight, it was the wrong choice, and users reported many, many issues over the years because they wanted to preserve the precise scalar type.

But we couldn't fix it without breaking one important use case.

Here's another question for you: What's the type of `$c = new Collection([1, 2, 3])`? If it's `Collection<1|2|3>`, you can't call `$c->add(4)` because it wouldn't be accepted. If it's `Collection<int>`, you can't pass it to a parameter expecting `Collection<positive-int>`, even though that should be valid.

If we take an empty collection `new Collection([])`, the resolved type of `@template T` is the `never` type (the empty [bottom type](https://en.wikipedia.org/wiki/Bottom_type)). Strictly speaking, you shouldn't be able to add anything to it, because it's supposed to be empty! Correct, but not really useful in the real world.

Yeah, tough choices.

Fortunately, Arnaud Le Blanc, who helped me tremendously with over 50 pull requests for the initial generics implementation back in 2019, [came to the rescue again on March 8th 2022](https://github.com/phpstan/phpstan/issues/6732#issuecomment-1062029088). He described a solution to this problem in an issue titled "Bidirectional type narrowing" so that's what we've been calling the idea ever since.

It took me over four years to gather up courage and funding to implement it correctly and efficiently.

The solution is to analyse the code around `new Collection(...)` multiple times, observe how it's used, where it's passed to, and clamp the final inferred `@template T` type based on that information.

You can play with this interactive widget to understand what PHPStan does with different code snippets:

<div class="type-inference-demo" data-hint="Switch the lines on and off: PHPStan infers the type of the new object from how it is used further down.">

<script type="application/json">
[{"id": "first-usage", "label": "Passed to a function", "toggles": [10, 11], "defaults": [true, false], "probes": [{"line": 9, "variable": "ints", "showName": false}], "results": [{"types": ["Collection<*NEVER*>"], "errors": []}, {"types": ["Collection<int>"], "errors": []}, {"types": ["Collection<string>"], "errors": []}, {"types": ["Collection<int>"], "errors": [{"line": 11, "message": "Parameter #1 $strings of function takeStrings expects Collection<string>, Collection<int> given.", "identifier": "argument.type"}]}]}, {"id": "precise-scalars", "label": "Precise scalars", "toggles": [7, 8, 9], "defaults": [true, false, false], "probes": [{"line": 6, "variable": "ids", "showName": false}], "results": [{"types": ["Collection<1|2|3>"], "errors": []}, {"types": ["Collection<1|2|3|4>"], "errors": []}, {"types": ["Collection<-1|1|2|3>"], "errors": []}, {"types": ["Collection<-1|1|2|3|4>"], "errors": []}, {"types": ["Collection<int<1, max>>"], "errors": []}, {"types": ["Collection<int<1, max>>"], "errors": []}, {"types": ["Collection<int<1, max>>"], "errors": [{"line": 8, "message": "Parameter #1 $item of method Collection<int<1, max>>::add() expects int<1, max>, -1 given.", "identifier": "argument.type"}]}, {"types": ["Collection<int<1, max>>"], "errors": [{"line": 8, "message": "Parameter #1 $item of method Collection<int<1, max>>::add() expects int<1, max>, -1 given.", "identifier": "argument.type"}]}]}, {"id": "method-calls", "label": "Method calls", "toggles": [7, 8, 9], "defaults": [true, true, false], "probes": [{"line": 6, "variable": "items", "showName": false}], "results": [{"types": ["Collection<*NEVER*>"], "errors": []}, {"types": ["Collection<1>"], "errors": []}, {"types": ["Collection<'one'>"], "errors": []}, {"types": ["Collection<1|'one'>"], "errors": []}, {"types": ["Collection<int>"], "errors": []}, {"types": ["Collection<int>"], "errors": []}, {"types": ["Collection<int>"], "errors": [{"line": 8, "message": "Parameter #1 $item of method Collection<int>::add() expects int, string given.", "identifier": "argument.type"}]}, {"types": ["Collection<int>"], "errors": [{"line": 8, "message": "Parameter #1 $item of method Collection<int>::add() expects int, string given.", "identifier": "argument.type"}]}]}, {"id": "joint-inference", "label": "Inferred together", "toggles": [12], "defaults": [false], "probes": [{"line": 10, "variable": "a", "showName": false}, {"line": 11, "variable": "b", "showName": false}], "results": [{"types": ["Box<1>", "Box<'one'>"], "errors": []}, {"types": ["Box<1|'one'>", "Box<1|'one'>"], "errors": []}]}]
</script>

<div data-scenario="first-usage">

**Passed to a function**

```php
/** @param Collection<int> $ints */
function takeInts(Collection $ints): void {}

/** @param Collection<string> $strings */
function takeStrings(Collection $strings): void {}

function example(): void
{
	$ints = new Collection([]);
	takeInts($ints);
	takeStrings($ints);
}
```

PHPStan infers that `$ints` is `Collection<int>` on line 9.

* Line 11: Parameter #1 $strings of function takeStrings expects Collection&lt;string&gt;, Collection&lt;int&gt; given.

</div>

<div data-scenario="precise-scalars">

**Precise scalars**

```php
/** @param Collection<positive-int> $ids */
function saveIds(Collection $ids): void {}

function example(): void
{
	$ids = new Collection([1, 2, 3]);
	$ids->add(4);
	$ids->add(-1);
	saveIds($ids);
}
```

PHPStan infers that `$ids` is `Collection<int<1, max>>` on line 6.

* Line 8: Parameter #1 $item of method Collection&lt;int&lt;1, max&gt;&gt;::add() expects int&lt;1, max&gt;, -1 given.

</div>

<div data-scenario="method-calls">

**Method calls**

```php
/** @param Collection<int> $ints */
function takeInts(Collection $ints): void {}

function example(): void
{
	$items = new Collection();
	$items->add(1);
	$items->add('one');
	takeInts($items);
}
```

PHPStan infers that `$items` is `Collection<int>` on line 6.

* Line 8: Parameter #1 $item of method Collection&lt;int&gt;::add() expects int, string given.

</div>

<div data-scenario="joint-inference">

**Inferred together**

```php
/**
 * @template T
 * @param Box<T> $a
 * @param Box<T> $b
 */
function swap(Box $a, Box $b): void {}

function example(): void
{
	$a = new Box(1);
	$b = new Box('one');
	swap($a, $b);
}
```

PHPStan infers that `$a` is `Box<1|'one'>` on line 10, `$b` is `Box<1|'one'>` on line 11.

</div>

</div>

The first naive prototype made PHPStan around 30% slower. Going back to the drawing board made me realize PHPStan doesn't have to reanalyse everything, only the lines directly impacted by the generic objects with unresolved template types. The performance impact of this feature is between 1 and 3%, depending on how heavily the analysed code uses generics, which is easily offset by the performance improvements made in the same release.

Thanks to this work, PHPStan no longer needs to generalize scalar types in the context of generics. The implementation closed 17 related issues, plus countless duplicates reported over the years.

Make sure to enable [bleeding edge](/blog/what-is-bleeding-edge) to take advantage of these improvements.

Bidirectional type narrowing and related work **have been commissioned and made possible by the [Sovereign Tech Fund](https://www.sovereign.tech/)**.





Detecting unused variables
---------------------

Unused variables are a class of errors that has escaped PHPStan's attention. Until now. They can be pretty severe: a value that has been assigned but not used later means someone made a typo or forgot to send the value somewhere. Like computing a TTL but never passing it to the `Cache::save()` method.

PHPStan distinguishes five scenarios of unused variables:

* Variable is assigned but never read: [`variable.unused`](/error-identifiers/variable.unused).
* The assigned value is never read before the end of the function body (the variable may still be read earlier, above the assignment): [`assign.unused`](/error-identifiers/assign.unused).
* The assigned value is overwritten by another assignment before being read: [`assign.overwritten`](/error-identifiers/assign.overwritten).
* Variable is assigned the value it already has: [`assign.redundant`](/error-identifiers/assign.redundant).
* Variable is assigned and seemingly used, but the value doesn't end up anywhere: [`assign.unusedFlow`](/error-identifiers/assign.unusedFlow). Inspired by [Psalm's data-flow graph](https://psalm.dev/articles/better-unused-variable-detection).

This list isn't exhaustive. Besides plain assignments like `$foo = ...`, PHPStan also checks array keys that are never read or immediately overwritten, results of `++` and `--`, foreach keys and values, catch variables, closure `use` variables, and parameters of functions and private methods.

Explore all the different errors PHPStan can now report in this interactive widget:

<div class="identifier-explorer">

<div data-identifier="variable.unused">

`variable.unused`

```php
function sendWelcomeEmail(Mailer $mailer, User $user): void
{
	$subject = 'Welcome to Acme!';
	$mailer->send($user->getEmail(), 'Welcome!', 'Glad to have you.');
}
```

* Line 3: Variable $subject is never read. `variable.unused`

</div>

<div data-identifier="assign.unused">

`assign.unused`

```php
function cacheReport(Cache $cache, Report $report): void
{
	$ttl = 3600;
	$cache->save($report->getId(), $report, $ttl);

	$ttl = 600;
	$cache->save($report->getId() . '-summary', $report->getSummary());
}
```

* Line 6: Value assigned to variable $ttl is never read. `assign.unused`

</div>

<div data-identifier="assign.overwritten">

`assign.overwritten`

```php
function formatPrice(float $amount, string $currency): string
{
	$label = number_format($amount, 2);
	$label = sprintf('%.2f %s', $amount, $currency);

	return $label;
}
```

* Line 3: Value assigned to variable $label is never read before being overwritten. `assign.overwritten`

</div>

<div data-identifier="assign.unusedFlow">

`assign.unusedFlow`

```php
function importRows(Importer $importer, array $rows): void
{
	$imported = 0;
	foreach ($rows as $row) {
		$importer->import($row);
		$imported = $imported + 1;
	}
}
```

* Line 3: Value assigned to variable $imported only flows into values that are never used. `assign.unusedFlow`
* Line 6: Value assigned to variable $imported only flows into values that are never used. `assign.unusedFlow`

</div>

<div data-identifier="assign.redundant">

`assign.redundant`

```php
function activate(User $user): void
{
	$status = 'active';
	if ($user->isVerified()) {
		$status = 'active';
	}

	$user->setStatus($status);
}
```

* Line 5: Variable $status is assigned value &#39;active&#39; but it already has that value. `assign.redundant`

</div>

<div data-identifier="array.unusedOffset">

`array.unusedOffset`

```php
function orderSummary(Order $order): string
{
	$summary = [
		'id' => $order->getId(),
		'total' => $order->getTotal(),
	];

	return sprintf('Order #%d', $summary['id']);
}
```

* Line 5: Offset &#39;total&#39; of array assigned to variable $summary is never read. `array.unusedOffset`

</div>

<div data-identifier="array.offsetOverwritten">

`array.offsetOverwritten`

```php
function httpOptions(bool $slowNetwork): array
{
	$options = [
		'timeout' => 5,
		'retries' => 3,
	];
	$options['timeout'] = $slowNetwork ? 30 : 10;

	return $options;
}
```

* Line 4: Offset &#39;timeout&#39; of array assigned to variable $options is never read before being overwritten. `array.offsetOverwritten`

</div>

<div data-identifier="array.unusedOffsetFlow">

`array.unusedOffsetFlow`

```php
function countWords(array $words): int
{
	$stats = ['words' => 0, 'letters' => 0];
	foreach ($words as $word) {
		$stats['words'] = $stats['words'] + 1;
		$stats['letters'] = $stats['letters'] + strlen($word);
	}

	return $stats['words'];
}
```

* Line 3: Offset &#39;letters&#39; of array assigned to variable $stats only flows into values that are never used. `array.unusedOffsetFlow`
* Line 6: Value assigned to $stats[&#39;letters&#39;] only flows into values that are never used. `assign.unusedFlow`

</div>

<div data-identifier="foreach.unusedValue" data-where="foreach value">

`foreach.unusedValue`

```php
function notifyAll(Mailer $mailer, array $users, string $admin): void
{
	foreach ($users as $user) {
		$mailer->send($admin, 'New signup', 'Someone joined.');
	}
}
```

* Line 3: Foreach value variable $user is never read. `foreach.unusedValue`

</div>

<div data-identifier="foreach.valueOverwritten" data-where="foreach value">

`foreach.valueOverwritten`

```php
function printLines(array $lines): void
{
	foreach ($lines as $line) {
		$line = 'Line';
		echo $line, PHP_EOL;
	}
}
```

* Line 3: Foreach value variable $line is never read before being overwritten. `foreach.valueOverwritten`

</div>

<div data-identifier="foreach.unusedValueFlow" data-where="foreach value">

`foreach.unusedValueFlow`

```php
function retryFailed(Queue $queue, array $failedJobs): void
{
	foreach ($failedJobs as $job => $attempts) {
		$queue->push($job);
		while ($queue->isFull()) {
			$queue->flush();
			$attempts = $attempts + 1;
		}
	}
}
```

* Line 3: Foreach value variable $attempts only flows into values that are never used. `foreach.unusedValueFlow`
* Line 7: Value assigned to variable $attempts only flows into values that are never used. `assign.unusedFlow`

</div>

<div data-identifier="foreach.unusedKey" data-where="foreach key">

`foreach.unusedKey`

```php
function totalPrice(array $prices): float
{
	$total = 0.0;
	foreach ($prices as $sku => $price) {
		$total += $price;
	}

	return $total;
}
```

* Line 4: Foreach key variable $sku is never read. `foreach.unusedKey`

</div>

<div data-identifier="foreach.keyOverwritten" data-where="foreach key">

`foreach.keyOverwritten`

```php
function labels(array $options): array
{
	$labels = [];
	foreach ($options as $key => $label) {
		$key = strtolower($label);
		$labels[] = $key;
	}

	return $labels;
}
```

* Line 4: Foreach key variable $key is never read before being overwritten. `foreach.keyOverwritten`

</div>

<div data-identifier="foreach.unusedKeyFlow" data-where="foreach key">

`foreach.unusedKeyFlow`

```php
function enqueue(Queue $queue, array $jobs): void
{
	foreach ($jobs as $position => $job) {
		$queue->push($job);
		while ($queue->isFull()) {
			$queue->flush();
			$position = $position + 1;
		}
	}
}
```

* Line 3: Foreach key variable $position only flows into values that are never used. `foreach.unusedKeyFlow`
* Line 7: Value assigned to variable $position only flows into values that are never used. `assign.unusedFlow`

</div>

<div data-identifier="preInc.unused">

`preInc.unused`

```php
function nextInvoiceNumber(int $last, Logger $logger): int
{
	$next = $last;
	++$next;
	$logger->info(sprintf('Issuing invoice %d', $next));
	++$next;

	return $last + 1;
}
```

* Line 6: Value of variable $next after ++ is never read. `preInc.unused`

</div>

<div data-identifier="preInc.overwritten">

`preInc.overwritten`

```php
function pageNumber(int $page, bool $reset): int
{
	++$page;
	$page = $reset ? 1 : $page;
	++$page;
	$page = 1;

	return $page;
}
```

* Line 5: Value of variable $page after ++ is never read before being overwritten. `preInc.overwritten`

</div>

<div data-identifier="preInc.unusedFlow">

`preInc.unusedFlow`

```php
function waitForJob(callable $isDone): void
{
	$attempts = 0;
	while (!$isDone()) {
		++$attempts;
		sleep(1);
	}
}
```

* Line 3: Value assigned to variable $attempts only flows into values that are never used. `assign.unusedFlow`
* Line 5: Value of variable $attempts after ++ only flows into values that are never used. `preInc.unusedFlow`

</div>

<div data-identifier="postInc.unused">

`postInc.unused`

```php
function placeholders(array $values): string
{
	$i = 0;
	$sql = sprintf('(%s)', implode(', ', array_fill(0, count($values), '?')));
	$i++;

	return $sql;
}
```

* Line 5: Value of variable $i after ++ is never read. `postInc.unused`

</div>

<div data-identifier="postInc.overwritten">

`postInc.overwritten`

```php
function rowNumber(array $rows): int
{
	$line = count($rows);
	$line++;
	$line = 1;

	return $line;
}
```

* Line 4: Value of variable $line after ++ is never read before being overwritten. `postInc.overwritten`

</div>

<div data-identifier="postInc.unusedFlow">

`postInc.unusedFlow`

```php
function sendAll(Mailer $mailer, array $users): void
{
	$sent = 0;
	foreach ($users as $user) {
		$mailer->send($user->getEmail(), 'News', 'Hello!');
		$sent++;
	}
}
```

* Line 3: Value assigned to variable $sent only flows into values that are never used. `assign.unusedFlow`
* Line 6: Value of variable $sent after ++ only flows into values that are never used. `postInc.unusedFlow`

</div>

<div data-identifier="preDec.unused">

`preDec.unused`

```php
function remainingSlots(int $capacity, Logger $logger): int
{
	$left = $capacity;
	--$left;
	$logger->info(sprintf('%d slots left', $left));
	--$left;

	return $capacity - 1;
}
```

* Line 6: Value of variable $left after -- is never read. `preDec.unused`

</div>

<div data-identifier="preDec.overwritten">

`preDec.overwritten`

```php
function countdown(int $from): int
{
	--$from;
	$from = 10;

	return $from;
}
```

* Line 3: Value of variable $from after -- is never read before being overwritten. `preDec.overwritten`

</div>

<div data-identifier="preDec.unusedFlow">

`preDec.unusedFlow`

```php
function drainQueue(SplQueue $queue): void
{
	$budget = 100;
	while (!$queue->isEmpty()) {
		$queue->dequeue();
		--$budget;
	}
}
```

* Line 3: Value assigned to variable $budget only flows into values that are never used. `assign.unusedFlow`
* Line 6: Value of variable $budget after -- only flows into values that are never used. `preDec.unusedFlow`

</div>

<div data-identifier="postDec.unused">

`postDec.unused`

```php
function lastIndex(array $items): int
{
	$index = count($items);
	$index--;
	$index--;

	return count($items) - 1;
}
```

* Line 5: Value of variable $index after -- is never read. `postDec.unused`

</div>

<div data-identifier="postDec.overwritten">

`postDec.overwritten`

```php
function retriesLeft(int $retries): int
{
	$retries--;
	$retries = 3;

	return $retries;
}
```

* Line 3: Value of variable $retries after -- is never read before being overwritten. `postDec.overwritten`

</div>

<div data-identifier="postDec.unusedFlow">

`postDec.unusedFlow`

```php
function consume(SplStack $stack): void
{
	$remaining = $stack->count();
	while (!$stack->isEmpty()) {
		$stack->pop();
		$remaining--;
	}
}
```

* Line 3: Value assigned to variable $remaining only flows into values that are never used. `assign.unusedFlow`
* Line 6: Value of variable $remaining after -- only flows into values that are never used. `postDec.unusedFlow`

</div>

<div data-identifier="catch.unusedVariableFlow">

`catch.unusedVariableFlow`

```php
function fetchStatus(Http $http, Logger $logger, int $retries): string
{
	try {
		return $http->get('https://example.com/status');
	} catch (TransientException $error) {
		for ($i = 0; $i < $retries; $i++) {
			$logger->warning('Status check failed');
			$error = $i === $retries - 1 ? null : $error;
		}

		return 'unknown';
	}
}
```

* Line 5: Catch variable $error only flows into values that are never used. `catch.unusedVariableFlow`
* Line 8: Value assigned to variable $error only flows into values that are never used. `assign.unusedFlow`

</div>

<div data-identifier="closure.unusedUse">

`closure.unusedUse`

```php
function greeter(Logger $logger, string $greeting): Closure
{
	return function (string $name) use ($logger, $greeting): string {
		return $greeting . ', ' . $name;
	};
}
```

* Line 3: Anonymous function has an unused use $logger. `closure.unusedUse`

</div>

<div data-identifier="closure.unusedUseFlow">

`closure.unusedUseFlow`

```php
function eachWithIndex(callable $callback, int $index): Closure
{
	return function (array $items) use ($callback, $index): void {
		foreach ($items as $item) {
			$callback($item);
			$index = $index + 1;
		}
	};
}
```

* Line 3: Anonymous function has a use $index that only flows into values that are never used. `closure.unusedUseFlow`
* Line 6: Value assigned to variable $index only flows into values that are never used. `assign.unusedFlow`

</div>

<div data-identifier="function.unusedParameter">

`function.unusedParameter`

```php
function welcomeMessage(User $user, string $locale): string
{
	return sprintf('Welcome, %s!', $user->getName());
}
```

* Line 1: Function welcomeMessage() has an unused parameter $locale. `function.unusedParameter`

</div>

<div data-identifier="function.unusedParameterFlow">

`function.unusedParameterFlow`

```php
function retryDelay(int $attempt, int $delay): int
{
	for ($i = 0; $i < $attempt; $i++) {
		$delay = $delay * 2;
	}

	return $attempt * 100;
}
```

* Line 1: Function retryDelay() has a parameter $delay that only flows into values that are never used. `function.unusedParameterFlow`
* Line 4: Value assigned to variable $delay only flows into values that are never used. `assign.unusedFlow`

</div>

<div data-identifier="method.unusedParameter">

`method.unusedParameter`

```php
final class InvoiceMailer
{
	public function __construct(private Mailer $mailer) {}

	public function send(Order $order, string $email): void
	{
		$this->mailer->send($email, 'Invoice', $this->body($order, $email));
	}

	private function body(Order $order, string $email): string
	{
		return sprintf('Invoice for order #%d', $order->getId());
	}
}
```

* Line 10: Method InvoiceMailer::body() has an unused parameter $email. `method.unusedParameter`

</div>

<div data-identifier="method.unusedParameterFlow">

`method.unusedParameterFlow`

```php
final class CsvExport
{
	public function run(array $rows): int
	{
		return $this->write($rows, 0);
	}

	private function write(array $rows, int $bytes): int
	{
		foreach ($rows as $row) {
			$bytes = $bytes + strlen($row);
		}

		return count($rows);
	}
}
```

* Line 8: Method CsvExport::write() has a parameter $bytes that only flows into values that are never used. `method.unusedParameterFlow`
* Line 11: Value assigned to variable $bytes only flows into values that are never used. `assign.unusedFlow`

</div>

</div>

Make sure to enable [bleeding edge](/blog/what-is-bleeding-edge) to take advantage of these new rules.




Inferring closure types from usages
---------------------

This builds on the same engine that powers bidirectional type narrowing for generics. It lets PHPStan observe how a closure stored in a variable is used after it's assigned, and feed that information back into the final analysis pass.

Closures never need to be annotated with `@param` and `@return` PHPDocs. Even without any typehints, PHPStan is able to precisely infer the involved types, including variables the closure takes by reference:

<div class="type-inference-demo" data-hint="Switch the lines on and off.">

<script type="application/json">
[{"id": "closure-usages", "label": "From usages", "hint": "Switch the lines on and off: PHPStan infers the parameter types of the closure from where it is called and passed.", "toggles": [10, 11], "defaults": [false, false], "probes": [{"line": 7, "variable": "id", "showName": true}, {"line": 9, "variable": "title", "showName": false}, {"line": 10, "variable": "labels", "showName": false}, {"line": 11, "variable": "fallback", "showName": false}], "results": [{"types": ["42", "'Order #42'", null, null], "errors": []}, {"types": ["int", "'Order #42'", "list<non-falsy-string>", null], "errors": []}, {"types": ["42|'n/a'", "'Order #42'", null, "'Order #n/a'"], "errors": []}, {"types": ["'n/a'|int", "'Order #42'", "list<non-falsy-string>", "'Order #n/a'"], "errors": []}]}, {"id": "closure-callable", "label": "Passed as a callback", "hint": "Switch the lines on and off: PHPStan infers the parameter types of the closure from the callable parameters it is passed to, and checks the body with them.", "toggles": [17], "defaults": [false], "probes": [{"line": 10, "variable": "event", "showName": true}], "results": [{"types": ["Order"], "errors": []}, {"types": ["Order|Refund"], "errors": [{"line": 11, "message": "Call to an undefined method Order|Refund::getEmail().", "identifier": "method.notFound"}]}]}, {"id": "closure-by-ref", "label": "By-reference variables", "hint": "Switch the lines on and off: PHPStan analyses the closure where it is called, with what the variables it takes by reference hold at that moment.", "toggles": [8, 9, 10], "defaults": [false, false, true], "probes": [{"line": 5, "variable": "messages", "showName": true}, {"line": 11, "variable": "messages", "showName": true}], "results": [{"types": ["array{}", "array{'Started'}"], "errors": []}, {"types": ["array{}|array{'Started'}", "array{'Started', 'Processing'}"], "errors": []}, {"types": ["array{}", "array{}"], "errors": []}, {"types": ["array{}|array{'Started'}", "array{}"], "errors": []}, {"types": ["array{}|array{'Started'}", "array{'Started', 'Finished'}"], "errors": []}, {"types": ["array{}|array{0: 'Started', 1?: 'Processing'}", "array{'Started', 'Processing', 'Finished'}"], "errors": []}, {"types": ["array{}", "array{'Finished'}"], "errors": []}, {"types": ["array{}|array{'Started'}", "array{'Finished'}"], "errors": []}]}]
</script>

<div data-scenario="closure-usages">

**From usages**

```php
/** @return list<int> */
function findOrderIds(): array { return [1, 2, 3]; }

function printOrders(): void
{
	$format = function ($id) {
		return 'Order #' . $id;
	};
	$title = $format(42);
	$labels = array_map($format, findOrderIds());
	$fallback = $format('n/a');
}
```

PHPStan infers that `$id` is `'n/a'|int` on line 7, `$title` is `'Order #42'` on line 9, `$labels` is `list<non-falsy-string>` on line 10, `$fallback` is `'Order #n/a'` on line 11.

</div>

<div data-scenario="closure-callable">

**Passed as a callback**

```php
/** @param callable(Order): void $listener */
function onOrderPlaced(callable $listener): void {}

/** @param callable(Refund): void $listener */
function onRefundIssued(callable $listener): void {}

function registerListeners(Mailer $mailer): void
{
	$notify = function ($event) use ($mailer): void {
		$mailer->send(
			$event->getEmail(),
			'Thank you',
			'We have received it.',
		);
	};
	onOrderPlaced($notify);
	onRefundIssued($notify);
}
```

PHPStan infers that `$event` is `Order|Refund` on line 10.

* Line 11: Call to an undefined method Order|Refund::getEmail().

</div>

<div data-scenario="closure-by-ref">

**By-reference variables**

```php
function process(): void
{
	$messages = [];
	$log = function (string $message) use (&$messages): void {
		$messages[] = $message;
	};
	$log('Started');
	$log('Processing');
	$messages = [];
	$log('Finished');
	echo implode("\n", $messages);
}
```

PHPStan infers that `$messages` is `array{}|array{'Started'}` on line 5, `$messages` is `array{'Finished'}` on line 11.

</div>

</div>

Make sure to enable [bleeding edge](/blog/what-is-bleeding-edge) to take advantage of these improvements.



Inferring static variable types from assignments
------------------

By now you've probably noticed the pattern 😅 Besides generic objects and closures, PHPStan can inspect assignments from the whole function body to infer types of `static` variables. Previously, they had to be annotated with `@var`, otherwise you'd end up with an implicit `mixed` type.

Switch the assignments off to see what happens:

<div class="type-inference-demo" data-hint="Switch the assignments on and off: PHPStan infers the type of the static variable from all of them.">

<script type="application/json">
[{"id": "static-cache", "label": "Static variable", "toggles": [6, 8], "defaults": [true, true], "probes": [{"line": 3, "variable": "cache", "showName": true}], "results": [{"types": ["null"], "errors": [{"line": 4, "message": "Strict comparison using === between null and null will always evaluate to true.", "identifier": "identical.alwaysTrue"}, {"line": 12, "message": "Function getCache() should return Cache but returns null.", "identifier": "return.type"}]}, {"types": ["ApcuCache|null"], "errors": [{"line": 12, "message": "Function getCache() should return Cache but returns ApcuCache|null.", "identifier": "return.type"}]}, {"types": ["FileCache|null"], "errors": [{"line": 12, "message": "Function getCache() should return Cache but returns FileCache|null.", "identifier": "return.type"}]}, {"types": ["ApcuCache|FileCache|null"], "errors": []}]}]
</script>

<div data-scenario="static-cache">

**Static variable**

```php
function getCache(): Cache
{
	static $cache = null;
	if ($cache === null) {
		if (extension_loaded('apcu')) {
			$cache = new ApcuCache();
		} else {
			$cache = new FileCache('/tmp/cache');
		}
	}

	return $cache;
}
```

PHPStan infers that `$cache` is `ApcuCache|FileCache|null` on line 3.

</div>

</div>


Again, you can enjoy this if you enable [bleeding edge](/blog/what-is-bleeding-edge) in your PHPStan config file.





Under the hood: single-pass inside-out engine rewrite
-------------------------

This is the [preliminary work](https://github.com/phpstan/phpstan-src/pull/5857) that enabled all of these improvements. From the very beginning until 2.2.x, PHPStan had three "god" classes that each traversed the [AST](/developing-extensions/abstract-syntax-tree) on their own:

* `NodeScopeResolver::processNodes()`: updates [Scope](/developing-extensions/scope) based on assignments, entering classes, functions etc.
* `MutatingScope::resolveType(Expr $expr): Type`: resolves [type](/developing-extensions/type-system) of an expression by checking types of child expressions it consists of
* `TypeSpecifier`: narrows types of expressions, for example decides what happens to `$foo` after `$foo instanceof Bar` for both truthy and falsey contexts

The problem with this approach was that it could either be slow or imprecise. If an expression also contains an assignment or modifies Scope some other way, the type resolution in MutatingScope will not know about it, unless it processes the node again. PHPStan used to process some nodes repeatedly to get rid of a certain class of bugs. So you could say it was both slow and imprecise 😅

Take this code as an example:

```php
$a = [
	$b = 1,
	$b + 1,
	$c = $b,
	$c + 2,
	$c++,
	$c,
];
\PHPStan\dumpType($a);
```

In PHPStan 2.2.x and before, the type of the array was inferred as `array{1, (float|int), *ERROR*, (float|int), *ERROR*, *ERROR*}`. PHPStan had no clue about the correct type of the array, because later array items referenced variables assigned or changed in the earlier ones.

In PHPStan 2.3.0 and onward, the type of the array from the code snippet above is correctly inferred as `array{1, 2, 1, 3, 1, 2}`.

The rewrite changes where and when expression types are inferred. Everything now happens when a node is processed in `NodeScopeResolver`. Once it's finished, the returned result object contains everything: the updated Scope, the expression type, and how the expression narrows other nested expressions. We took extra care not to break any extension APIs, so `$scope->getType($expr)` continues to work as before.

Before the rewrite, some code, like deeply nested `&&` / `||` expressions, could lead to real slowdowns because they'd be processed many times. It wouldn't have been possible to introduce the awesome new features described in this article with the old internals because of the multiplicative nature of multiple passes needed for these features. You won't notice going from 1 to 2 if 1 takes very little time, but you'd certainly notice if something was analysed 20 times instead of 10 (which was already pretty slow).


Update extensions today too!
-------------------------

A bunch of official PHPStan extensions have new releases today related to changes and enhancements in PHPStan 2.3.0. So don't forget to update them alongside PHPStan.

* [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) 2.1.0 makes [`foreach.valueOverwrite`](/error-identifiers/foreach.valueOverwrite) less annoying and no longer reports it in harmless scenarios. [More info »](https://github.com/phpstan/phpstan-strict-rules/issues/332)
* [phpstan-symfony](https://github.com/phpstan/phpstan-symfony) 2.1.0 no longer invalidates the entire result cache when the DI container changes. It uses [new extensions and APIs](/developing-extensions/result-cache-meta-extensions#tracking-dependencies-on-files) to reanalyse only files referencing the changed parameters and services while keeping the rest of the result cache intact.
* [phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) 2.1.0 makes `DataProviderDataRule` hook onto multiple node types at once using the new [MultipleNodeTypesRule](https://apiref.phpstan.org/__BRANCH__/PHPStan.Rules.MultipleNodeTypesRule.html) interface, mostly for performance reasons.


---

Do you like PHPStan and use it every day? [**Consider sponsoring** further development of PHPStan on GitHub Sponsors and also **subscribe to PHPStan Pro**](/sponsor)! I'd really appreciate it!
