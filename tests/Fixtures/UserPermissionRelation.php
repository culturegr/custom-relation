<?php

namespace CultureGr\CustomRelation\Tests\Fixtures;

use CultureGr\CustomRelation\CustomRelation;
use Illuminate\Database\Eloquent\Collection;

class UserPermissionRelation extends CustomRelation
{
    /**
     * The Eloquent query builder instance.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;


    /**
     * The parent model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model | User
     */
    protected $parent;


    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $this->query
            ->join('permission_role', 'permission_role.permission_id', '=', 'permissions.id')
            ->join('roles', 'permission_role.role_id', '=', 'roles.id')
            ->join('role_user', 'role_user.role_id', '=', 'roles.id');

        // If relation is not eager loaded
        if (!is_null($this->parent->getAttribute('id'))) {
            $this->query->where('role_user.user_id', '=', $this->parent->getAttribute('id'));
        }
    }


    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $users  An array of parent models
     * @return void
     */
    public function addEagerConstraints(array $users)
    {
        $this->query
            ->whereIn('role_user.user_id', collect($users)->pluck('id'))
            ->with('roles.users'); // To avoid N+1 problem when eager loading
    }


    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $users  An array of parent models
     * @param  \Illuminate\Database\Eloquent\Collection  $results  The result of the query executed by our relation class.
     * @param  string  $relation
     * @return array
     */
    public function match(array $users, Collection $results, $relation)
    {
        if ($results->isEmpty()) {
            return $users;
        }

        foreach ($users as $user) {
            $user->setRelation(
                $relation,
                $results->unique()->filter(function (Permission $permission) use ($user) {
                    return in_array($user->id, $permission->roles->pluck('users.*.id')->flatten()->toArray());
                })->values()
            );
        }

        return $users;
    }
}