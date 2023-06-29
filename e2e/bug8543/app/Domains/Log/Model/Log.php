<?php declare(strict_types=1);

namespace App\Domains\Log\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Log\Model\Builder\Log as Builder;
use App\Domains\Log\Model\Traits\Payload as PayloadTrait;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use PayloadTrait;

    /**
     * @var string
     */
    protected $table = 'log';

    /**
     * @const string
     */
    public const TABLE = 'log';

    /**
     * @const string
     */
    public const FOREIGN = 'log_id';

    /**
     * @param \Illuminate\Database\Query\Builder $q
     *
     * @return \App\Domains\Log\Model\Builder\Log
     */
    public function newEloquentBuilder($q): Builder
    {
        return new Builder($q);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function related(): HasMany
    {
        return $this->hasMany(Log::class, static::FOREIGN);
    }
}
