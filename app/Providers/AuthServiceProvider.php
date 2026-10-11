<?php

namespace App\Providers;

use App\Models\Reimbursement;
use App\Policies\ReimbursementPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Reimbursement::class => ReimbursementPolicy::class,
        \App\Models\Transaction::class => \App\Policies\TransactionPolicy::class,
        \App\Models\Budget::class => \App\Policies\BudgetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('access-finance', fn (\App\Models\User $user) => $user->isFinance());
        \Illuminate\Support\Facades\Gate::define('access-manager', fn (\App\Models\User $user) => $user->isManager() || $user->isDirector());
        \Illuminate\Support\Facades\Gate::define('access-director', fn (\App\Models\User $user) => $user->isDirector());
        \Illuminate\Support\Facades\Gate::define('access-admin', fn (\App\Models\User $user) => $user->isAdmin());
    }
}
