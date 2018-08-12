<?php

$mbChrWithoutEncoding = mb_chr(65);
$mbChrWithValidEncoding = mb_chr(65, 'UTF-8');
$mbChrWithInvalidEncoding = mb_chr(65, 'Foo');
$mbChrWithValidAndInvalidEncoding = mb_chr(65, doFoo() ? 'UTF-8' : 'Foo');
$mbChrWithUnknownEncoding = mb_chr(65, doFoo());

$mbOrdWithoutEncoding = mb_ord('');
$mbOrdWithValidEncoding = mb_ord('', 'UTF-8');
$mbOrdWithInvalidEncoding = mb_ord('', 'Foo');
$mbOrdWithValidAndInvalidEncoding = mb_ord('', doFoo() ? 'UTF-8' : 'Foo');
$mbOrdWithUnknownEncoding = mb_ord('', doFoo());

$mbScrubWithoutEncoding = mb_scrub('');
$mbScrubWithValidEncoding = mb_scrub('', 'UTF-8');
$mbScrubWithInvalidEncoding = mb_scrub('', 'Foo');
$mbScrubWithValidAndInvalidEncoding = mb_scrub('', doFoo() ? 'UTF-8' : 'Foo');
$mbScrubWithUnkownEncoding = mb_scrub('', doFoo());

die;
