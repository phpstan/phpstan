<?php

declare(strict_types=1);

use PHPStan\Testing\AnalysisBased\Utils;

Utils::assertErrorOnNextLine('Parameter array() of print cannot be converted to string.');
print [];

Utils::assertErrorOnNextLine('Parameter stdClass of print cannot be converted to string.');
print new stdClass();

Utils::assertErrorOnNextLine('Parameter Closure(): mixed of print cannot be converted to string.');
print function () {};

print 13132;

Utils::assertErrorOnNextLine('Parameter array() of print cannot be converted to string.');
print([]);

Utils::assertErrorOnNextLine('Parameter stdClass of print cannot be converted to string.');
print(new stdClass());

Utils::assertErrorOnNextLine('Parameter Closure(): mixed of print cannot be converted to string.');
print(function () {});

print('string');

Utils::assertErrorOnNextLine('Parameter \'string\'|array(\'string\') of print cannot be converted to string.');
print random_int(0, 1) ? ['string'] : 'string';
