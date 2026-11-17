<?php

namespace App\Providers;

use App\Models\AcademicRankHistory;
use App\Models\BpjsMembership;
use App\Models\PerformanceTarget;
use App\Models\StaffRankHistory;
use App\Policies\AcademicRankPolicy;
use App\Policies\BpjsPolicy;
use App\Policies\SkpPolicy;
use App\Policies\StaffRankPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AcademicRankHistory::class, AcademicRankPolicy::class);
        Gate::policy(StaffRankHistory::class, StaffRankPolicy::class);
        Gate::policy(BpjsMembership::class, BpjsPolicy::class);
        Gate::policy(PerformanceTarget::class, SkpPolicy::class);
    }
}
