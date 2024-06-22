<?php
// app/Scopes/IsDeleteScope.php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IsDeleteScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function (Builder $query) {
            $query->where('is_delete', '=', 0)
                ->orWhereNull('is_delete');
        });
    }
}
