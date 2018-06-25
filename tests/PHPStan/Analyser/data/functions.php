<?php

$microtimeStringWithoutArg = microtime();
$microtimeString = microtime(false);
$microtimeFloat = microtime(true);
$microtimeDefault = microtime(null);
$microtimeBenevolent = microtime($undefined);

$strtotimeNow = strtotime('now');
$strtotimeInvalid = strtotime('4 qm');
$strtotimeUnknown = strtotime(doFoo() ? 'now': '4 qm');
$strtotimeUnknown2 = strtotime($undefined);
$strtotimeCrash = strtotime();

$versionCompare1 = version_compare('7.0.0', '7.0.1');
$versionCompare2 = version_compare('7.0.0', doFoo() ? '7.0.1' : '6.0.0');
$versionCompare3 = version_compare(doFoo() ? '7.0.0' : '6.0.5', doBar() ? '7.0.1' : '6.0.0');
$versionCompare4 = version_compare('7.0.0', doFoo());
$versionCompare5 = version_compare('7.0.0', '7.0.1', '<');
$versionCompare6 = version_compare('7.0.0', doFoo() ? '7.0.1' : '6.0.0', '<');
$versionCompare7 = version_compare(doFoo() ? '7.0.0' : '6.0.5', doBar() ? '7.0.1' : '6.0.0', '<');
$versionCompare8 = version_compare('7.0.0', doFoo(), '<');

die;
