<?php

namespace CultureGr\CustomRelation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class CustomRelation extends Relation
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
     * Create a new custom relation instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  The related query builder instance
     * @param  Model  $parent  The parent model instance
     */
    public function __construct(Builder $query, Model $parent)
    {
        parent::__construct($query, $parent);
    }


    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    abstract public function addConstraints();


    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models  An array of parent models
     * @return void
     */
    abstract public function addEagerConstraints(array $models);


    /**
     * Initialize the relation on a set of models.
     *
     * It Initialises the empty `users` relationship on every
     * parent model, so that it can be filled afterwards.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation(
                $relation,
                $this->related->newCollection()
            );
        }

        return $models;
    }


    /**
     * Get the results of the relationship.
     *
     * Used when we access the relationship via dynamic property
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query
            ->get($this->related->getTable().'.*');
    }


    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }

        return $this->query->get($columns);
    }


    abstract public function match(array $apps, Collection $results, $relation);
}