<?php

declare(strict_types=1);

namespace App\Validator;

use MyCLabs\Enum\Enum as ClabsEnum;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Enum extends Constraint
{
    public const ENUM = 'f047d07c-c0fc-482f-84ef-ac5bf923f59a';

    /**
     * @var array<string,string>
     */
    protected static $errorNames = [
        self::ENUM => 'ENUM',
    ];

    /**
     * @var class-string<ClabsEnum<mixed>>|class-string<\BackedEnum>
     */
    public string $class;

    /**
     * {@inheritdoc}
     *
     * @param class-string<ClabsEnum<int>>|class-string<ClabsEnum<string>>|class-string<\BackedEnum>|array{class:class-string<ClabsEnum<string>>|class-string<\BackedEnum>} $class
     * @param array<string,mixed>                                                                                                                                           $options
     */
    public function __construct(
        array|string $class,
        public bool $checkKey = false,
        public string $message = 'Invalid enum value: {{ value }}.',
        $groups = null,
        $payload = null,
        array $options = []
    ) {
        if (\is_array($class)) {
            $options = array_merge($class, $options);
        } else { // @codeCoverageIgnore
            $options['class'] = $class; // @codeCoverageIgnore
        }

        parent::__construct($options, $groups, $payload);

        assert(is_subclass_of($this->class, ClabsEnum::class));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): string
    {
        return 'class';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['class'];
    }
}
