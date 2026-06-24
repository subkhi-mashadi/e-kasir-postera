<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Super admin sees all; unauthenticated = no filter (public routes handle their own scoping)
        if (auth()->check() && ! auth()->user()->hasRole('super_admin')) {
            $builder->where($model->getTable() . '.company_id', auth()->user()->company_id);
        }
    }
}
