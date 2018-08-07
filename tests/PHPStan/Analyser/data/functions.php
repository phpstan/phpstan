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

$mbCheckEncodingWithoutEncoding = mb_check_encoding('');
$mbCheckEncodingWithValidEncoding = mb_check_encoding('', 'UTF-8');
$mbCheckEncodingWithInvalidEncoding = mb_check_encoding('', 'Foo');
$mbCheckEncodingWithValidAndInvalidEncoding = mb_check_encoding('', doFoo() ? 'UTF-8' : 'Foo');
$mbCheckEncodingWithUnknownEncoding = mb_check_encoding('', doFoo());

$mbChrWithoutEncoding = mb_chr(65);
$mbChrWithValidEncoding = mb_chr(65, 'UTF-8');
$mbChrWithInvalidEncoding = mb_chr(65, 'Foo');
$mbChrWithValidAndInvalidEncoding = mb_chr(65, doFoo() ? 'UTF-8' : 'Foo');
$mbChrWithUnknownEncoding = mb_chr(65, doFoo());

$mbConvertCaseWithoutEncoding = mb_convert_case('', MB_CASE_LOWER);
$mbConvertCaseWithValidEncoding = mb_convert_case('', MB_CASE_LOWER, 'UTF-8');
$mbConvertCaseWithInvalidEncoding = mb_convert_case('', MB_CASE_LOWER, 'Foo');
$mbConvertCaseWithValidAndInvalidEncoding = mb_convert_case('', MB_CASE_LOWER, doFoo() ? 'UTF-8' : 'Foo');
$mbConvertCaseWithUnknownEncoding = mb_convert_case('', MB_CASE_LOWER, doFoo());

$mbConvertEncodingWithValidFirstEncoding = mb_convert_encoding('', 'UTF-8');
$mbConvertEncodingWithInvalidFirstEncoding = mb_convert_encoding('', 'Foo');
$mbConvertEncodingWithValidEncodings = mb_convert_encoding('', 'UTF-8', 'UTF-8');
$mbConvertEncodingWithValidAndInvalidFirstEncoding = mb_convert_encoding('', doFoo() ? 'UTF-8' : 'Foo');
$mbConvertEncodingWithValidFirstAndInvalidSecondEncoding = mb_convert_encoding('', 'UTF-8', 'Foo');
$mbConvertEncodingWithUnknownEncodings = mb_convert_encoding('', doFoo(), doFoo());

$mbConvertKanaWithoutEncoding = mb_convert_kana('', 'KV');
$mbConvertKanaWithValidEncoding = mb_convert_kana('', 'KV', 'UTF-8');
$mbConvertKanaWithInvalidEncoding = mb_convert_kana('', 'KV', 'Foo');
$mbConvertKanaWithValidAndInvalidEncoding = mb_convert_kana('', 'KV', doFoo() ? 'UTF-8' : 'Foo');
$mbConvertKanaWithUnknownEncoding = mb_convert_kana('', 'KV', doFoo());

$mbDecodeNumericEntityWithoutEncoding = mb_decode_numericentity('', [0x0]);
$mbDecodeNumericEntityWithValidEncoding = mb_decode_numericentity('', [0x0], 'UTF-8');
$mbDecodeNumericEntityWithInvalidEncoding = mb_decode_numericentity('', [0x0], 'Foo');
$mbDecodeNumericEntityWithValidAndInvalidEncoding = mb_decode_numericentity('', [0x0], doFoo());
$mbDecodeNumericEntityWithUnknownEncoding = mb_decode_numericentity('', [0x0], doFoo());

$mbEncodeNumericEntityWithoutEncoding = mb_encode_numericentity('&', [0x0]);
$mbEncodeNumericEntityWithValidEncoding = mb_encode_numericentity('&', [0x0], 'UTF-8');
$mbEncodeNumericEntityWithInvalidEncoding = mb_encode_numericentity('&', [0x0], 'Foo');
$mbEncodeNumericEntityWithValidAndInvalidEncoding = mb_encode_numericentity('&', [0x0], doFoo() ? 'UTF-8' : 'Foo');
$mbEncodeNumericEntityWithUnknownEncoding = mb_encode_numericentity('&', [0x0], doFoo());

$mbEncodingAliasesWithValidEncoding = mb_encoding_aliases('UTF-8');
$mbEncodingAliasesWithInvalidEncoding = mb_encoding_aliases('Foo');
$mbEncodingAliasesWithValidAndInvalidEncoding = mb_encoding_aliases(doFoo() ? 'UTF-8' : 'Foo');
$mbEncodingAliasesWithUnknownEncoding = mb_encoding_aliases(doFoo());

$mbOrdWithoutEncoding = mb_ord('');
$mbOrdWithValidEncoding = mb_ord('', 'UTF-8');
$mbOrdWithInvalidEncoding = mb_ord('', 'Foo');
$mbOrdWithValidAndInvalidEncoding = mb_ord('', doFoo() ? 'UTF-8' : 'Foo');
$mbOrdWithUnknownEncoding = mb_ord('', doFoo());

$mbPreferredMimeNameWithValidEncoding = mb_preferred_mime_name('UTF-8');
$mbPreferredMimeNameWithInvalidEncoding = mb_preferred_mime_name('Foo');
$mbPreferredMimeNameWithValidAndInvalidEncoding = mb_preferred_mime_name(doFoo() ? 'UTF-8' : 'Foo');
$mbPreferredMimeNameWithUnknownEncoding = mb_preferred_mime_name(doFoo());

$mbScrubWithoutEncoding = mb_scrub('');
$mbScrubWithValidEncoding = mb_scrub('', 'UTF-8');
$mbScrubWithInvalidEncoding = mb_scrub('', 'Foo');
$mbScrubWithValidAndInvalidEncoding = mb_scrub('', doFoo() ? 'UTF-8' : 'Foo');
$mbScrubWithUnkownEncoding = mb_scrub('', doFoo());

$mbStrcutWithoutEncoding = mb_strcut('', 1);
$mbStrcutWithValidEncoding = mb_strcut('', 1, null, 'UTF-8');
$mbStrcutWithInvalidEncoding = mb_strcut('', 1, null, 'Foo');
$mbStrcutWithValidAndInvalidEncoding = mb_strcut('', 1, null, doFoo() ? 'UTF-8' : 'Foo');
$mbStrcutWithUnknownEncoding = mb_strcut('', 1, null, doFoo());

$mbStrimwidthWithoutEncoding = mb_strimwidth('', 1, 1);
$mbStrimwidthWithValidEncoding = mb_strimwidth('', 1, 1, '', 'UTF-8');
$mbStrimwidthWithInvalidEncoding = mb_strimwidth('', 1, 1, '', 'Foo');
$mbStrimwidthWithValidAndInvalidEncoding = mb_strimwidth('', 1, 1, '', doFoo() ? 'UTF-8' : 'Foo');
$mbStrimwidthWithUnknownEncoding = mb_strimwidth('', 1, 1, '', doFoo());

$mbStriposWithoutEncoding = mb_stripos('', 'f', 0);
$mbStriposWithValidEncoding = mb_stripos('', 'f', 0, 'UTF-8');
$mbStriposWithInvalidEncoding = mb_stripos('', 'f', 0, 'Foo');
$mbStriposWithValidAndInvalidEncoding = mb_stripos('', 'f', 0, doFoo() ? 'UTF-8' : 'Foo');
$mbStriposWithUnknownEncoding = mb_stripos('', 'f', 0, doFoo());

$mbStristrWithoutEncoding = mb_stristr('', 'f', false);
$mbStristrWithValidEncoding = mb_stristr('', 'f', false, 'UTF-8');
$mbStristrWithInvalidEncoding = mb_stristr('', 'f', false, 'Foo');
$mbStristrWithValidAndInvalidEncoding = mb_stristr('', 'f', false, doFoo() ? 'UTF-8' : 'Foo');
$mbStristrWithUnknownEncoding = mb_stristr('', 'f', false, doFoo());

$mbStrlenWithoutEncoding = mb_strlen('');
$mbStrlenWithValidEncoding = mb_strlen('', 'utf-8');
$mbStrlenWithInvalidEncoding = mb_strlen('', 'foo');
$mbStrlenWithValidAndInvalidEncoding = mb_strlen('', doFoo() ? 'utf-8' : 'foo');
$mbStrlenWithUnknownEncoding = mb_strlen('', doFoo());

$mbStrposWithoutEncoding = mb_strpos('', '', 0);
$mbStrposWithValidEncoding = mb_strpos('', '', 0, 'UTF-8');
$mbStrposWithInvalidEncoding = mb_strpos('', '', 0, 'Foo');
$mbStrposWithValidAndInvalidEncoding = mb_strpos('', '', 0, doFoo() ? 'UTF-8' : 'Foo');
$mbStrposWithUnkownEncoding = mb_strpos('', '', 0, doFoo());

$mbStrrchrWithoutEncoding = mb_strrchr('', '', false);
$mbStrrchrWithValidEncoding = mb_strrchr('', '', false, 'UTF-8');
$mbStrrchrWithInvalidEncoding = mb_strrchr('', '', false, 'Foo');
$mbStrrchrWithValidAndInvalidEncoding = mb_strrchr('', '', false, doFoo() ? 'UTF-8' : 'Foo');
$mbStrrchrWithUnknownEncoding = mb_strrchr('', '', false, doFoo());

$mbStrrichrWithoutEncoding = mb_strrichr('', '', false);
$mbStrrichrWithValidEncoding = mb_strrichr('', '', false, 'UTF8');
$mbStrrichrWithInvalidEncoding = mb_strrichr('', '', false, 'Foo');
$mbStrrichrWithValidAndInvalidEncoding = mb_strrichr('', '', false, doFoo() ? 'UTF-8' : 'Foo');
$mbStrrichrWithUnknownEncoding = mb_strrichr('', '', false, doFoo());

$mbStrriposWithoutEncoding = mb_strripos('', '', 0);
$mbStrriposWithValidEncoding = mb_strripos('', '', 0, 'UTF-8');
$mbStrriposWithInvalidEncoding = mb_strripos('', '', 0, 'Foo');
$mbStrriposWithValidAndInvalidEncoding = mb_strripos('', '', 0, doFoo() ? 'UTF-8' : 'Foo');
$mbStrriposWithUnknownEncoding = mb_strripos('', '', 0, doFoo());

$mbStrrposWithoutEncoding = mb_strrpos('', 'f', 0);
$mbStrrposWithValidEncoding = mb_strrpos('', 'f', 0, 'UTF-8');
$mbStrrposWithInvalidEncoding = mb_strrpos('', 'f', 0, 'Foo');
$mbStrrposWithValidAndInvalidEncoding = mb_strrpos('', 'f', 0, doFoo() ? 'UTF-8' : 'Foo');
$mbStrrposWithUnknownEncoding = mb_strrpos('', 'f', 0, doFoo());

$mbStrstrWithoutEncoding = mb_strstr('', 'f', false);
$mbStrstrWithValidEncoding = mb_strstr('', 'f', false, 'UTF-8');
$mbStrstrWithInvalidEncoding = mb_strstr('', 'f', false, 'Foo');
$mbStrstrWithValidAndInvalidEncoding = mb_strstr('', 'f', false, doFoo() ? 'UTF-8' : 'Foo');
$mbStrstrWithUnknownEncoding = mb_strstr('', 'f', false, doFoo());

$mbStrtolowerWithoutEncoding = mb_strtolower('');
$mbStrtolowerWithValidEncoding = mb_strtolower('', 'UTF-8');
$mbStrtolowerWithInvalidEncoding = mb_strtolower('', 'Foo');
$mbStrtolowerWithValidAndInvalidEncoding = mb_strtolower('', doFoo() ? 'UTF-8' : 'Foo');
$mbStrtolowerWithUnknownEncoding = mb_strtolower('', doFoo());

$mbStrtoupperWithoutEncoding = mb_strtoupper('');
$mbStrtoupperWithValidEncoding = mb_strtoupper('', 'UTF-8');
$mbStrtoupperWithInvalidEncoding = mb_strtoupper('', 'Foo');
$mbStrtoupperWithValidAndInvalidEncoding = mb_strtoupper('', doFoo() ? 'UTF-8' : 'Foo');
$mbStrtoupperWithUnknownEncoding = mb_strtoupper('', doFoo());

$mbStrwidthWithoutEncoding = mb_strwidth('');
$mbStrwidthWithValidEncoding = mb_strwidth('', 'UTF-8');
$mbStrwidthWithInvalidEncoding = mb_strwidth('', 'Foo');
$mbStrwidthWithValidAndInvalidEncoding = mb_strwidth('', doFoo() ? 'UTF-8' : 'Foo');
$mbStrwidthWithUnknownEncoding = mb_strwidth('', doFoo());

$mbSubstrCountWithoutEncoding = mb_substr_count('', 0);
$mbSubstrCountWithValidEncoding = mb_substr_count('', 0, 'UTF-8');
$mbSubstrCountWithInvalidEncoding = mb_substr_count('', 0, 'Foo');
$mbSubstrCountWithValidAndInvalidEncoding = mb_substr_count('', 0, doFoo() ? 'UTF-8' : 'Foo');
$mbSubstrCountWithUnknownEncoding = mb_substr_count('', 0, doFoo());

$mbSubstrWithoutEncoding = mb_substr('', 0, null);
$mbSubstrWithValidEncoding = mb_substr('', 0, null, 'UTF-8');
$mbSubstrWithInvalidEncoding = mb_substr('', 0, null, 'Foo');
$mbSubstrWithValidAndInvalidEncoding = mb_substr('', 0, null, doFoo() ? 'UTF-8' : 'Foo');
$mbSubstrWithUnknownEncoding = mb_substr('', 0, null, doFoo());

$var = 'foo';
$mbConvertVariablesWithValidFirstAndValidSecondEncodingString = mb_convert_variables('UTF-8', 'UTF-8', $var);
$mbConvertVariablesWithValidFirstAndInvalidSecondEncodingString = mb_convert_variables('UTF-8', 'Foo', $var);
$mbConvertVariablesWithValidFirstAndValidAndInvalidSecondEncodingString = mb_convert_variables('UTF-8', ' Foo, UTF-8   ', $var);
$mbConvertVariablesWithValidFirstEncodingAndEmptyString = mb_convert_variables('UTF-8', '', $var);
$mbConvertVariablesWithInvalidFirstEncoding = mb_convert_variables('Foo', 'UTF-8', $var);
$mbConvertVariablesWithValidFirstEncodingAndEmptyArray = mb_convert_variables('UTF-8', [], $var);
$mbConvertVariablesWithValidFirstEncodingAndValidArray = mb_convert_variables('UTF-8', ['UTF-8'], $var);
$mbConvertVariablesWithValidFirstEncodingAndValidAndInvalidArray = mb_convert_variables('UTF-8', ['Foo', 'UTF-8'], $var);
$mbConvertVariablesWithValidFirstEncodingAndInvalidArray = mb_convert_variables('UTF-8', ['Foo'], $var);
$mbConvertVariablesWithValidAndInvalidFirstEncoding = mb_convert_variables(doFoo() ? 'UTF-8' : 'Foo', ['Foo'], $var);

$mbDetectOrderWithoutEncoding = mb_detect_order();
$mbDetectOrderWithValidStringEncoding = mb_detect_order('UTF-8');
$mbDetectOrderWithInvalidStringEncoding = mb_detect_order('Foo');
$mbDetectOrderWithValidAndInvalidStringEncoding = mb_detect_order('Foo, UTF-8');
$mbDetectOrderWithValidArrayEncoding = mb_detect_order(['UTF-8']);
$mbDetectOrderWithInvalidArrayEncoding = mb_detect_order(['Foo']);
$mbDetectOrderWithValidAndInvalidArrayEncoding = mb_detect_order(['Foo', 'UTF-8']);
$mbDetectOrderWithUnknownEncoding = mb_detect_order(doFoo());
$mbDetectOrderWithValidAndInvalidEncoding = mb_detect_order(doFoo() ? 'UTF-8' : 'Foo');

$mbHttpOutputWithoutEncoding = mb_http_output();
$mbHttpOutputWithValidEncoding = mb_http_output('UTF-8');
$mbHttpOutputWithInvalidEncoding = mb_http_output('Foo');
$mbHttpOutputWithValidAndInvalidEncoding = mb_http_output(doFoo() ? 'UTF-8' : 'Foo');
$mbHttpOutputWithUnknownEncoding = mb_http_output(doFoo());

$mbInternalEncodingWithoutEncoding = mb_internal_encoding();
$mbInternalEncodingWithValidEncoding = mb_internal_encoding('UTF-8');
$mbInternalEncodingWithInvalidEncoding = mb_internal_encoding('Foo');
$mbInternalEncodingWithValidAndInvalidEncoding = mb_internal_encoding(doFoo() ? 'UTF-8' : 'Foo');
$mbInternalEncodingWithUnknownEncoding = mb_internal_encoding(doFoo());

$mbRegexEncodingWithoutEncoding = mb_regex_encoding();
$mbRegexEncodingWithValidEncoding = mb_regex_encoding('UTF-8');
$mbRegexEncodingWithInvalidEncoding = mb_regex_encoding('Foo');
$mbRegexEncodingWithValidAndInvalidEncoding = mb_regex_encoding(doFoo() ? 'UTF-8' : 'Foo');
$mbRegexEncodingWithUnknownEncoding = mb_regex_encoding(doFoo());

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

/** @var resource $resource */
$resource = doFoo();
$stat = stat(__FILE__);
$lstat = lstat(__FILE__);
$fstat = fstat($resource);

die;
