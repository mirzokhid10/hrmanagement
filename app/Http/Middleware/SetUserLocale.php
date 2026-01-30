<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale'); // Default fallback

        if (Auth::check() && Auth::user()->locale) {
            // Priority 1: Logged in user's DB setting
            $locale = Auth::user()->locale;
        } elseif (Session::has('locale')) {
            // Priority 2: Guest session setting
            $locale = Session::get('locale');
        }

        // Enforce supported languages
        if (!in_array($locale, ['uz', 'ru', 'en'])) {
            $locale = 'uz';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
