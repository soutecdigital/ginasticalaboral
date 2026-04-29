<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EmpresaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Se houver uma empresa na sessão, filtra TUDO por ela automaticamente
        if (session()->has('empresa_ativa')) {
            $builder->where('empresa_id', session('empresa_ativa'));
        }
    }
}