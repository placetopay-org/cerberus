<?php

namespace Placetopay\Cerberus\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AppScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('app', config('multitenancy.identifier'));
    }
}
