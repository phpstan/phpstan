<?php

/** @var bool|null $boolOrNull */
$boolOrNull = doFoo();
$bool = $boolOrNull !== null ? $boolOrNull : false;

$short = $bool ?: null;

/** @var bool $a */
$a = doBar();
/** @var bool $b */
$b = doBaz();
$c = $a ?: $b;

/** @var string|null $qux */
$qux = doQux();
$isQux = $qux !== null ?: $bool;

die;
