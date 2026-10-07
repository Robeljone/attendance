<?php

namespace App\Providers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        Schema::defaultStringLength(191);

        View::composer(['layouts.app', 'layouts.guest', 'components.application-logo'], function ($view): void {
            $view->with('companyBranding', $this->resolveCompanyBranding());
        });
    }

    private function resolveCompanyBranding(): ?CompanySetting
    {
        try {
            if (! Schema::hasTable('company_settings')) {
                return null;
            }

            return CompanySetting::current();
        } catch (Throwable) {
            return null;
        }
    }
}
