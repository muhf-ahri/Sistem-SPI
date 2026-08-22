<?php

namespace App\Providers;

use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\ActionPlan;
use App\Models\Inspection;
use App\Models\User;
use App\Models\Division;
use App\Models\AuditType;
use App\Models\FindingCategory;
use App\Models\RiskCategory;
use App\Policies\AuditPlanPolicy;
use App\Policies\FindingPolicy;
use App\Policies\ActionPlanPolicy;
use App\Policies\InspectionPolicy;
use App\Policies\UserPolicy;
use App\Policies\DivisionPolicy;
use App\Policies\AuditTypePolicy;
use App\Policies\FindingCategoryPolicy;
use App\Policies\RiskCategoryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AuditPlan::class => AuditPlanPolicy::class,
        Finding::class   => FindingPolicy::class,
        ActionPlan::class=> ActionPlanPolicy::class,
        Inspection::class=> InspectionPolicy::class,
        User::class      => UserPolicy::class,
        Division::class  => DivisionPolicy::class,
        AuditType::class => AuditTypePolicy::class,
        FindingCategory::class => FindingCategoryPolicy::class,
        RiskCategory::class    => RiskCategoryPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        \Illuminate\Support\Facades\Gate::define('manage-master', function ($user) {
            return in_array($user->role, ['super_admin', 'spi']);
        });
    }
}