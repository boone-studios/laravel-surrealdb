<?php

namespace BooneStudios\Surreal\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class Model extends BaseModel
{
    /**
     * Custom accessor for the model's id.
     *
     * @param mixed $value
     * @return mixed
     */
    public function getIdAttribute($value = null)
    {
        // If we don't have a value for 'id', we will use the Mongo '_id' value.
        // This allows us to work with models in a more sql-like way.
        if (! $value && array_key_exists('id', $this->attributes)) {
            $value = $this->attributes['id'];
        }

        // Convert ObjectID to string.
        if ($value instanceof ObjectID) {
            return (string) $value;
        } elseif ($value instanceof Binary) {
            return (string) $value->getData();
        }

        return $value;
    }
}
