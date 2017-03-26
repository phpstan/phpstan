<?php

/** @var bool|null $boolOrNull */
$boolOrNull = doFoo();
$bool = $boolOrNull !== null ? $boolOrNull : false;

die;
