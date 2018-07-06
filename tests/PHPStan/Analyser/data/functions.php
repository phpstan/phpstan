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


// str_split
$strSplitConstantStringWithoutDefinedParameters = str_split();
$strSplitConstantStringWithoutDefinedSplitLength = str_split('abcdef');
$strSplitConstantStringWithSplitLengthStringType = str_split('abcdef', '1');
$strSplitConstantStringWithOneSplitLength = str_split('abcdef', 1);
$strSplitConstantStringWithGreaterSplitLengthThanStringLength = str_split('abcdef', 999);
$strSplitConstantStringWithFailureSplitLength = str_split('abcdef', 0);
$strSplitConstantStringWithInvalidSplitLengthType = str_split('abcdef', []);
$strSplitConstantStringWithVariableStringAndConstantSplitLength = str_split(doFoo() ? 'abcdef' : 'ghijkl', 1);
$strSplitConstantStringWithVariableStringAndVariableSplitLength = str_split(doFoo() ? 'abcdef' : 'ghijkl', doFoo() ? 1 : 2);
die;
