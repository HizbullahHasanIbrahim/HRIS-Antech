<?php

namespace App\Providers;

use App\Models\AcademicRankHistory;
use App\Models\BpjsMembership;
use App\Models\PerformanceTarget;
use App\Models\Retirement;
use App\Models\StaffRankHistory;
use App\Policies\AcademicRankPolicy;
use App\Policies\BpjsPolicy;
use App\Policies\RetirementPolicy;
use App\Policies\SkpPolicy;
use App\Policies\StaffRankPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(Retirement::class, RetirementPolicy::class);

        // Rate limiters
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('search', function ($request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('export', function ($request) {
            return Limit::perMinute(config('app.env') === 'testing' ? 100 : 5)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
