<?php

namespace PropertyExists;

/**
 * @property-read \stdClass $getCreator
 */
class Model
{
}

class Defaults
{
	public function defaults(Model $model): void
	{
		$columns = [
			'getCreator',
			'getCreatedByColumn',
			'getUpdatedByColumn',
			'getDeletedByColumn',
			'getCreatedAtColumn',
			'getUpdatedAtColumn',
			'getDeletedAtColumn',
		];

		foreach ($columns as $column) {
            echo $model->{$column};
		}
	}
}
