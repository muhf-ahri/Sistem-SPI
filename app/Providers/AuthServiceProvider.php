<?php

namespace App\Providers;

use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\ActionPlan;
use App\Models\Inspection;
use App\Models\User;
use App\Models\Division;
use App\Policies\AuditPlanPolicy;
use App\Policies\FindingPolicy;
use App\Policies\ActionPlanPolicy;
use App\Policies\InspectionPolicy;
use App\Policies\UserPolicy;
use App\Policies\DivisionPolicy;
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
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}