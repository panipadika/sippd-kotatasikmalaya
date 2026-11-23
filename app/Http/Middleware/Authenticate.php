<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AppSetting;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        $user = $request->user();
        if ($user && !$user->is_active) {
            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun dinonaktifkan.'], 403);
            }

            return redirect($this->redirectTo($request) ?: url('/login'))
                ->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.']);
        }

        return $next($request);
    }

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
