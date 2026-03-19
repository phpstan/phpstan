---
title: "Why Array String Keys Are Not Type-Safe in PHP"
date: 2026-03-19
tags: guides
---

PHP silently casts array keys. This is one of the oldest and most well-known quirks in the language. But I don't think its consequences for type safety have been fully appreciated — until now.

The casting rules
------------------------

The [PHP manual](https://www.php.net/manual/en/language.types.array.php) lists the following automatic key casts:

- **Strings** containing valid decimal integers are cast to `int`. The key `"8"` is stored as `8`. But `"08"` stays as a string because it's not a valid decimal integer.
- **Floats** are truncated to `int`. The key `8.7` becomes `8`. Deprecated since PHP 8.1.
- **Bools** are cast to `int`. `true` becomes `1`, `false` becomes `0`.
- **Null** becomes the empty string `""`. Deprecated since PHP 8.5.

The classic demonstration:

```php
$array = [
    1    => 'a',
    '1'  => 'b',
    1.5  => 'c',
    true => 'd',
];

var_dump(count($array)); // int(1)
```

Four entries, four different literal types — but PHP treats them all as the same key `1`. Only the last value survives.

This isn't just a quirky language feature. It breaks type safety in ways that static analysis has historically been unable to catch.

The strict_types trap
------------------------

Consider this innocent-looking code:

```php
<?php declare(strict_types = 1);

function processKey(string $key): void
{
    // ...
}

/** @param array<string, mixed> $data */
function processData(array $data): void
{
    foreach ($data as $key => $value) {
        processKey($key);
    }
}
```

PHPStan sees `$data` typed as `array<string, mixed>`, so `$key` must be `string`. No errors reported. The code looks perfectly safe.

But at runtime, if `$data` was built with a key like `'123'`, PHP stored it as `int(123)`. When the `foreach` reaches that entry, `$key` is `int`, not `string`. With `strict_types` enabled, `processKey($key)` throws a `TypeError`.

PHPStan told you everything was fine. PHP disagrees. The type annotation `array<string, mixed>` promises something that PHP arrays simply cannot guarantee.

array_merge re-indexes your "string" keys
------------------------

`array_merge()` has different behaviour for integer keys and string keys: integer keys get re-indexed starting from `0`, while string keys are preserved. When your string keys get silently cast to integers, you get re-indexing you didn't ask for:

```php
$a = ['100' => 'foo'];  // Stored as [100 => 'foo']
$b = ['200' => 'bar'];  // Stored as [200 => 'bar']

$merged = array_merge($a, $b);
// Result: [0 => 'foo', 1 => 'bar']
// Not ['100' => 'foo', '200' => 'bar']
```

The developer wrote string keys. They got integer keys. And `array_merge` silently re-indexed them.

Strict key lookups fail
------------------------

If you use `array_keys()` and then search with strict comparison, you'll be surprised:

```php
$data = ['123' => 'value'];
$keys = array_keys($data);
// $keys is [123] — an integer!

in_array('123', $keys, true);  // false!
// Strict comparison: '123' !== 123
```

You put in `'123'` as a string key, you search for `'123'` as a string — and it's not found. Because PHP silently changed the key's type behind your back.

A real-world example
------------------------

Imagine working with postal codes:

```php
/** @return array{'1234aa', '2345bb'} */
function getPostals()
{
    return ['1234aa', '2345bb'];
}

$pc4Set = [];
foreach (getPostals() as $postal) {
    $pc4Set[substr($postal, 0, 4)] = true;
}

$pc4 = array_keys($pc4Set);
\PHPStan\dumpType($pc4[0]); // 1234|2345 (int, not string!)
```

The developer extracted the first 4 characters of each postal code using `substr()` — a function that returns a `string`. But because those strings happen to be `'1234'` and `'2345'`, PHP casts them to integers when they're used as array keys. The resulting `array_keys()` returns integers, not strings.

What PHPStan already does
------------------------

I've been laying the groundwork for solving this problem.

The [online playground](https://phpstan.org/try) already notifies you about key casts. When you write a literal array or access an array with a key that will be cast, PHPStan tells you:

> Key '1234' (string) will be cast to 1234 (int) in the array access.

[See this example in the playground »](https://phpstan.org/r/8d89c4bb-9966-42fb-8e5d-1ebb12fe31cc)

Additionally, the [`reportNonIntStringArrayKey`](/config-reference#reportNonIntStringArrayKey) option (available since PHPStan 2.1.39) can report when non-int/non-string types like booleans, floats, or `null` are used as array keys:

```yaml
parameters:
    reportNonIntStringArrayKey: true
```

```php
$array = [];
$array[true] = 'value';  // Reported: Invalid array key type true.
$array[4.5] = 'value';   // Reported: Invalid array key type float.
$array[null] = 'value';  // Reported: Invalid array key type null.
```

These features catch explicit misuses of non-int/non-string types as keys. But they don't solve the fundamental problem: any `string` might be a decimal integer string, and once it's used as an array key, it silently becomes an `int`. PHPStan couldn't do anything about this — until now.

PHPStan 2.2: non-decimal-int-string
------------------------

PHPStan 2.2, coming soon at some point in spring 2026, will introduce a new type: `non-decimal-int-string`.

A *decimal integer string* is a string that PHP considers a valid decimal integer and would cast to `int` when used as an array key — like `'0'`, `'123'`, or `'42'`.

A `non-decimal-int-string` is any string that is *not* one of these — like `'hello'`, `'08'`, `'+1'`, or `'1.5'`.

The key insight: if your array keys are `non-decimal-int-string`, PHP will never cast them. They will always remain strings. This means `array<non-decimal-int-string, mixed>` is genuinely type-safe:

```php
<?php declare(strict_types = 1);

function processKey(string $key): void
{
    // ...
}

/** @param array<non-decimal-int-string, mixed> $data */
function processData(array $data): void
{
    foreach ($data as $key => $value) {
        processKey($key); // Always safe — $key is guaranteed to be string
    }
}
```

No more `TypeError` at runtime. No more surprise re-indexing by `array_merge`. No more failed strict key lookups. The type system finally tells the truth about what your array keys actually are.

Additionally, `array<string, mixed>` will be optionally interpreted as `array<non-decimal-int-string, mixed>`. When this is enabled, PHPStan will make sure that only safe non-decimal-int strings are used as keys in string-keyed arrays. This will usher in a new era of array type safety in PHP.

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan**](/sponsor). I'd really appreciate it!
