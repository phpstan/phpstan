<?php

namespace Z\Module\Article\DataProvider;
/**
 * @extends AbstractProvider<array{opts?: array{'access'?: bool, 'asked_code'?: bool, 'asked_access'?: bool}, blocks?: list<RegOwnerSteps::BLOCK_*>, subject?: RegOwnerSteps::SUBJECT_*, step?: RegOwnerSteps::STEP_*, preview_mode?: (0|1|2|3|4|5|6|7|null), is_preview?: bool}>
 */
final class RegOwnerSteps extends AbstractProvider {
    public const BLOCK_A = 'a';
    public const SUBJECT_A = 'a';
    public const STEP_A = 'a';
}
