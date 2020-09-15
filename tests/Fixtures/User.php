<?php

namespace CultureGr\CustomRelation\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use CultureGr\CustomRelation\HasCustomRelation;

class User extends Model
{
    use HasCustomRelation;

    protected $guarded = [];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions(): UserPermissionRelation
    {
        return $this->relatesTo(Permission::class, UserPermissionRelation::class);
    }
}
