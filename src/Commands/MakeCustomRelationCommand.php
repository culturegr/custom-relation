<?php

namespace CultureGr\CustomRelation\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeCustomRelationCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:relation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom relation class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'CustomRelation';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/CustomRelation.stub';
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);
        return str_replace('DummyName', class_basename($this->argument('name')), $stub);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Eloquent\CustomRelations';
    }
}