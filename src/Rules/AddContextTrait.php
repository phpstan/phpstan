<?php declare(strict_types = 1);

namespace PHPStan\Rules;

trait AddContextTrait
{
    /**
     * Add the specific context to the error output.
     *
     * @param array       $errors  The error input
     * @param string|null $context The current parsing context
     *
     * @return array The (possibly) modified error array.
     */
    public function addContext(array $errors, $context)
    {
        if ($context && is_string($context)) {
            $errors = array_map(
                function ($error) use ($context) {
                    return is_string($error)
                        ? sprintf('%s in trait context %s.', rtrim($error, '.!'), $context)
                        : $error;
                },
                $errors
            );
        }

        return $errors;
    }
}