<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class NotCircularReference extends Constraint
{
    public const NOT_CIRCULAR_REFERENCE = '267fbd90-6224-476b-8208-351a33492c54';

    /**
     * @var array<string,string>
     */
    protected static $errorNames = [
        self::NOT_CIRCULAR_REFERENCE => 'NOT_CIRCULAR_REFERENCE',
    ];

    public string $message = 'Circular reference.';

    public string $propertyName;

    /**
     * {@inheritdoc}
     *
     * @param string|array<string,mixed>|null $propertyName
     * @param array<string,mixed>             $options
     */
    public function __construct(
        $propertyName = null,
        string $message = null,
        $groups = null,
        $payload = null,
        array $options = []
    ) {
        if (\is_array($propertyName)) {
            $options = array_merge($propertyName, $options);
        } elseif (null !== $propertyName) { // @codeCoverageIgnore
            $options['propertyName'] = $propertyName; // @codeCoverageIgnore
        }

        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): string
    {
        return 'propertyName';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['propertyName'];
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
