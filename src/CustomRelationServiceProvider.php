<?php

namespace CultureGr\CustomRelation;

use Illuminate\Support\ServiceProvider;
use CultureGr\CustomRelation\Commands\MakeCustomRelationCommand;

class CustomRelationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([MakeCustomRelationCommand::class]);
    }
}
