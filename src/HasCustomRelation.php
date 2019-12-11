<?php

namespace CultureGr\CustomRelation;

trait HasCustomRelation
{
    public function relatesTo(string $related, string $relation)
    {
        $instance = new $related;
        $query = $instance->newQuery();

        return new $relation($query, $this);
    }
}