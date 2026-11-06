<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        try {
            $saSlug = AppSetting::getSuperAdminSlug();
            if ($request->is("{$saSlug}/*") || $request->is($saSlug)) {
                return url("/{$saSlug}/login");
            }

            $adminSlug = AppSetting::getAdminSlug();
            if ($request->is("{$adminSlug}/*") || $request->is($adminSlug)) {
                return url("/{$adminSlug}/login");
            }

            $userSlug = AppSetting::getUserSlug();
            return url("/{$userSlug}/login");
        } catch (\Throwable $e) {
            return url('/portal/login');
        }
    }
}
