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

$mbStrlenWithoutEncoding = mb_strlen('');
$mbStrlenWithValidEncoding = mb_strlen('', 'utf-8');
$mbStrlenWithInvalidEncoding = mb_strlen('', 'foo');
$mbStrlenWithValidAndInvalidEncoding = mb_strlen('', doFoo() ? 'utf-8' : 'foo');
$mbStrlenWithUnknownEncoding = mb_strlen('', doFoo());

$mbHttpOutputWithoutEncoding = mb_http_output();
$mbHttpOutputWithValidEncoding = mb_http_output('utf-8');
$mbHttpOutputWithInvalidEncoding = mb_http_output('foo');
$mbHttpOutputWithValidAndInvalidEncoding = mb_http_output(doFoo() ? 'utf-8' : 'foo');
$mbHttpOutputWithUnknownEncoding = mb_http_output(doFoo());

$mbRegexEncodingWithoutEncoding = mb_regex_encoding();
$mbRegexEncodingWithValidEncoding = mb_regex_encoding('utf-8');
$mbRegexEncodingWithInvalidEncoding = mb_regex_encoding('foo');
$mbRegexEncodingWithValidAndInvalidEncoding = mb_regex_encoding(doFoo() ? 'utf-8' : 'foo');
$mbRegexEncodingWithUnknownEncoding = mb_regex_encoding(doFoo());

$mbInternalEncodingWithoutEncoding = mb_internal_encoding();
$mbInternalEncodingWithValidEncoding = mb_internal_encoding('utf-8');
$mbInternalEncodingWithInvalidEncoding = mb_internal_encoding('foo');
$mbInternalEncodingWithValidAndInvalidEncoding = mb_internal_encoding(doFoo() ? 'utf-8' : 'foo');
$mbInternalEncodingWithUnknownEncoding = mb_internal_encoding(doFoo());

$mbEncodingAliasesWithValidEncoding = mb_encoding_aliases('utf-8');
$mbEncodingAliasesWithInvalidEncoding = mb_encoding_aliases('foo');
$mbEncodingAliasesWithValidAndInvalidEncoding = mb_encoding_aliases(doFoo() ? 'utf-8' : 'foo');
$mbEncodingAliasesWithUnknownEncoding = mb_encoding_aliases(doFoo());

$mbChrWithoutEncoding = mb_chr(68);
$mbChrWithValidEncoding = mb_chr(68, 'utf-8');
$mbChrWithInvalidEncoding = mb_chr(68, 'foo');
$mbChrWithValidAndInvalidEncoding = mb_chr(68, doFoo() ? 'utf-8' : 'foo');
$mbChrWithUnknownEncoding = mb_chr(68, doFoo());

$mbOrdWithoutEncoding = mb_ord('');
$mbOrdWithValidEncoding = mb_ord('', 'utf-8');
$mbOrdWithInvalidEncoding = mb_ord('', 'foo');
$mbOrdWithValidAndInvalidEncoding = mb_ord('', doFoo() ? 'utf-8' : 'foo');
$mbOrdWithUnknownEncoding = mb_ord('', doFoo());

$gettimeofdayArrayWithoutArg = gettimeofday();
$gettimeofdayArray = gettimeofday(false);
$gettimeofdayFloat = gettimeofday(true);
$gettimeofdayDefault = gettimeofday(null);
$gettimeofdayBenevolent = gettimeofday($undefined);

// str_split
/** @var string $string */
$string = doFoo();
$strSplitConstantStringWithoutDefinedParameters = str_split();
$strSplitConstantStringWithoutDefinedSplitLength = str_split('abcdef');
$strSplitStringWithoutDefinedSplitLength = str_split($string);
$strSplitConstantStringWithOneSplitLength = str_split('abcdef', 1);
$strSplitConstantStringWithGreaterSplitLengthThanStringLength = str_split('abcdef', 999);
$strSplitConstantStringWithFailureSplitLength = str_split('abcdef', 0);
$strSplitConstantStringWithInvalidSplitLengthType = str_split('abcdef', []);
$strSplitConstantStringWithVariableStringAndConstantSplitLength = str_split(doFoo() ? 'abcdef' : 'ghijkl', 1);
$strSplitConstantStringWithVariableStringAndVariableSplitLength = str_split(doFoo() ? 'abcdef' : 'ghijkl', doFoo() ? 1 : 2);

// parse_url
/** @var int $integer */
$integer = doFoo();
$parseUrlWithoutParameters = parse_url();
$parseUrlConstantUrlWithoutComponent1 = parse_url('http://abc.def');
$parseUrlConstantUrlWithoutComponent2 = parse_url('http://def.abc', -1);
$parseUrlConstantUrlUnknownComponent = parse_url('http://def.abc', $integer);
$parseUrlConstantUrlWithComponentNull = parse_url('http://def.abc', PHP_URL_FRAGMENT);
$parseUrlConstantUrlWithComponentSet = parse_url('http://def.abc#this-is-fragment', PHP_URL_FRAGMENT);
$parseUrlConstantUrlWithComponentInvalid = parse_url('http://def.abc#this-is-fragment', 9999);
$parseUrlStringUrlWithComponentInvalid = parse_url($string, 9999);
$parseUrlStringUrlWithComponentPort = parse_url($string, PHP_URL_PORT);
$parseUrlStringUrlWithoutComponent = parse_url($string);

/** @var resource $resource */
$resource = doFoo();
$stat = stat(__FILE__);
$lstat = lstat(__FILE__);
$fstat = fstat($resource);

die;
