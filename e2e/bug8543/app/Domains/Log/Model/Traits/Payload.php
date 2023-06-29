<?php declare(strict_types=1);

namespace App\Domains\Log\Model\Traits;

use App\Domains\Log\Model\Log as Model;

trait Payload
{
    /**
     * @var ?array
     */
    protected ?array $payloadRow;

    /**
     * @var ?array
     */
    protected ?array $payloadRowOther;

    /**
     * @return ?array
     */
    public function payloadRow(): ?array
    {
        return $this->payloadRow ??= $this->related->first(
            fn ($value) => $this->payloadRowIsSame($value)
        )->payload['row'] ?? null;
    }

    /**
     * @param \App\Domains\Log\Model\Log $related
     *
     * @return bool
     */
    protected function payloadRowIsSame(Model $related): bool
    {
        return ($related->related_table === $this->related_table)
            && ($related->related_id === $this->related_id);
    }

    /**
     * @return ?array
     */
    public function payloadRowOther(): ?array
    {
        return $this->payloadRowOther ??= $this->related->first(
            fn ($value) => $this->payloadRowIsDifferent($value)
        )->payload['row'] ?? null;
    }

    /**
     * @param \App\Domains\Log\Model\Log $related
     *
     * @return bool
     */
    protected function payloadRowIsDifferent(Model $related): bool
    {
        return ($related->related_table !== $this->related_table)
            || ($related->related_id !== $this->related_id);
    }
}
