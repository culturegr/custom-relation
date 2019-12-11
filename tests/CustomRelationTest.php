<?php

namespace CultureGr\CustomRelation\Tests;

use CultureGr\CustomRelation\Tests\Fixtures\User;
use CultureGr\CustomRelation\Tests\Fixtures\Role;
use CultureGr\CustomRelation\Tests\Fixtures\Permission;

class CustomRelationTest extends TestCase
{
    /** @test */
    public function it_provides_a_relates_to_method_to_eloquent_models_using_the_trait()
    {
        $user = factory(User::class)->create();

        $this->assertTrue(method_exists($user, 'relatesTo'));
    }


    /** @test */
    public function it_provides_a_make_relation_artisan_command()
    {
        $this->artisan('make:relation MyCustomRelation')
            ->assertExitCode(0);
    }


    /** @test */
    public function it_allows_to_get_user_permissions_using_a_custom_relation()
    {
        [$userA, $permissionsA] = $this->seedDatabase();
        [$userB, $permissionsB] = $this->seedDatabase();

        $userPermissions = $userA->permissions()->get();

        $this->assertEquals($permissionsA->toArray(), $userPermissions->toArray());

    }


    /** @test */
    public function it_allows_to_get_user_permissions_using_a_custom_relation_as_dynamic_property()
    {
        [$userA, $permissionsA] = $this->seedDatabase();
        [$userB, $permissionsB] = $this->seedDatabase();

        $userPermissions = $userA->permissions;

        $this->assertEquals($permissionsA->toArray(), $userPermissions->toArray());
    }


    /** @test */
    public function it_allows_to_eager_load_users_with_permissions_using_a_custom_relation()
    {
        [$userA, $permissionsA] = $this->seedDatabase();
        [$userB, $permissionsB] = $this->seedDatabase();

        $userA = User::with('permissions')->find($userA->id);
        $userB = User::with('permissions')->find($userB->id);

        $this->assertEquals($permissionsA->pluck('id'), $userA['permissions']->pluck('id'));
        $this->assertEquals($permissionsB->pluck('id'), $userB['permissions']->pluck('id'));
    }


    /** @test */
    public function it_allows_to_lazily_eager_load_users_with_permissions_using_a_custom_relation()
    {
        [$userA, $permissionsA] = $this->seedDatabase();
        [$userB, $permissionsB] = $this->seedDatabase();

        $userA->load('permissions');
        $userB->load('permissions');

        $this->assertEquals($permissionsA->pluck('id'), $userA['permissions']->pluck('id'));
        $this->assertEquals($permissionsB->pluck('id'), $userB['permissions']->pluck('id'));
    }


    protected function seedDatabase()
    {
        $user = factory(User::class)->create();
        $roles = factory(Role::class, 2)->create();
        $permissions = factory(Permission::class, 3)->create();
        $user->roles()->attach($roles);
        $roles[0]->permissions()->attach($permissions[0]);
        $roles[0]->permissions()->attach($permissions[1]);
        $roles[1]->permissions()->attach($permissions[2]);

        return [$user, $permissions];
    }
}