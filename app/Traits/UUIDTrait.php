<?php
namespace App\Traits;

use Illuminate\Support\Str;

trait UUIDTrait
{
    protected static function bootUUIDTrait()
    {
        static::creating(function ($model) {
            $model->keyType = 'string';
            $model->incrementing = false;
            $model->primaryKey = 'id';

            $model->{$model->getKeyName()} = $model->{$model->getKeyName()} ?: (string) Str::orderedUuid();
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
