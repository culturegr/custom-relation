# 🏺 Custom Relation

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
![Github Actions](https://github.com/culturegr/test-actions/actions/workflows/run-tests.yml/badge.svg)


This package provides an easy way to implement custom relationships between Eloquent models

## Installation

Via [Composer](https://getcomposer.org):

``` bash
$ composer require culturegr/custom-relation
```

In Laravel 5.5+, the package's service provider should be [auto-discovered](https://laravel.com/docs/5.7/packages#package-discovery), so you won't need to register it. If for some reason you need to register it manually you can do so by adding it to the providers array in `config/app.php`:

```php
'providers' => [
    // ...
    CultureGr\CustomRelation\CustomRelationServiceProvider::class,
],
```

## Usage

Suppose we have a Laravel application that implements a simple [ACL](https://en.wikipedia.org/wiki/Access-control_list) (Access Control List) layer: there are users that are assigned some roles, each of them consists of many permissions. A simplified version of the database structure could be the following:

![Alt text](https://i.stack.imgur.com/TXux1.png "ACL ER Diagram")

There is a `User` model that has a many-to-many relationship with a `Role` model, which in turn has a many-to-many relationship with a `Permission` model.

Now suppose that at some point, we need to access all permissions assigned to a specific user. Let's make this possible by creating a `CustomRelation` class that will be used to define the relationship between the `User` and the `Permission` models.

### Creating a Custom Relation Class

A CustomRelation class should facilitate all required logic needed to join `users` and `permissions` tables, as well as to support relationship [eager-loading](https://laravel.com/docs/6.x/eloquent-relationships#eager-loading). It can be created by running the `make:relation` Artisan command:

```bash
$ php artisan make:relation UserPermissionRelation
```
 
This will generate a new `CustomRelation` class named `UserPermissionRelation` inside `app/Eloquent/CustomRelations` directory with all required boilerplate:

 ```php
<?php

namespace App\Eloquent\CustomRelations;

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
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parent;


    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        // ...
    }


    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $apps  An array of parent models
     * @return void
     */
    public function addEagerConstraints(array $apps)
    {
        // ...
    }


    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $apps  An array of parent models
     * @param  \Illuminate\Database\Eloquent\Collection  $results  The result of the query executed by our relation class.
     * @param  string  $relation  The name of the relation
     * @return array
     */
    public function match(array $apps, Collection $results, $relation)
    {
        // ...
    }
}
 ```

### Implementing the Custom Relation Class

The `UserPermissionRelation` class initializes two properties:
- `$this->query` provides access to the related `Permission` model's query builder instance
- `$this->parent` provides access to the parent `User` model 

In order to define the users/permissions relationship, the following methods should be implemented:

#### addConstraints

Sets the base constraints on the relation query. In our example:

```php
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
```

#### addEagerConstraints

Sets the constraints for an eager load of the relation. In our example:

```php
public function addEagerConstraints(array $users)
{
    $this->query
        ->whereIn('role_user.user_id', collect($users)->pluck('id'))
        ->with('roles.users'); // To avoid N+1 problem when eager loading
}
```

#### match

Matches the eagerly loaded results to their parents. In our example:

```php
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
```

### Using the Custom Relation Class

Once the `UserPermissionRelation` class has been implemented, it can be used to define a new custom relationship between the `User` and the `Permission` model via `relatesTo` method which is available to the model through `HasCustomRelation` trait:

```php
use CultureGr\CustomRelation\HasCustomRelation;

class User extends Model
{
    use HasCustomRelation;

    // ...

    public function permissions(): UserPermissionRelation
    {
        return $this->relatesTo(Permission::class, UserPermissionRelation::class);
    }
}
```

That's it 🔥! Now we can use our new custom `permissions` relationship like any usual [Eloquent relationship](https://laravel.com/docs/6.x/eloquent-relationships):

```php
// Use relationship as a method
$userPermissions = User::find('id')->permissions()->get();

// Use relationship as a dynamic property
$userPermissions = User::find('id')->permissions;

// Eager loading
$user = User::with('permissions')->where(/* ... */)->get();

// Lazy eager loading
$user = User::find('id');
$user->load('permissions');
``` 
## Testing

``` bash
composer test
```

## License

Please see the [license file](LICENSE.md) for more information.

## Credits

- Awesome Laravel/PHP community

[ico-version]: https://img.shields.io/packagist/v/culturegr/custom-relation.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/culturegr/custom-relation.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/culturegr/custom-relation
[link-downloads]: https://packagist.org/packages/culturegr/custom-relation

