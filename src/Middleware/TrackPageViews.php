<?php

namespace Dittofeed\Laravel\Middleware;

use Closure;
use Dittofeed\Laravel\Facades\Dittofeed;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackPageViews
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // Only track successful GET requests for HTML pages
        if ($this->shouldTrackPageView($request, $response)) {
            try {
                $this->trackPageView($request);
            } catch (\Exception $e) {
                // Fail silently to not break the application
                if (config('dittofeed.debug')) {
                    logger()->error('Failed to track page view', [
                        'url' => $request->fullUrl(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $response;
    }

    /**
     * Determine if the page view should be tracked.
     */
    protected function shouldTrackPageView(Request $request, $response): bool
    {
        // Only track GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }

        // Only track successful responses
        if (method_exists($response, 'status') && $response->status() >= 400) {
            return false;
        }

        // Don't track AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        // Check if route is excluded
        if ($this->isRouteExcluded($request)) {
            return false;
        }

        // Check if URL is excluded
        if ($this->isUrlExcluded($request)) {
            return false;
        }

        return true;
    }

    /**
     * Track the page view in Dittofeed.
     */
    protected function trackPageView(Request $request): void
    {
        $pageName = $this->getPageName($request);

        Dittofeed::page($pageName, [
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'referrer' => $request->header('referer'),
            'title' => $pageName,
            'search' => $request->getQueryString(),
            'method' => $request->method(),
        ]);
    }

    /**
     * Get a descriptive name for the page.
     */
    protected function getPageName(Request $request): string
    {
        // Try to get route name
        if ($request->route() && $request->route()->getName()) {
            return $this->formatRouteName($request->route()->getName());
        }

        // Fall back to path
        $path = $request->path();

        if ($path === '/') {
            return 'Home';
        }

        return Str::title(str_replace(['/', '-', '_'], ' ', $path));
    }

    /**
     * Format route name to be more readable.
     */
    protected function formatRouteName(string $routeName): string
    {
        return Str::title(str_replace(['.', '-', '_'], ' ', $routeName));
    }

    /**
     * Check if the current route is excluded from tracking.
     */
    protected function isRouteExcluded(Request $request): bool
    {
        $excludedRoutes = config('dittofeed.excluded_routes', []);

        if (empty($excludedRoutes)) {
            return false;
        }

        $currentPath = $request->path();

        foreach ($excludedRoutes as $pattern) {
            if (Str::is($pattern, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current URL is excluded from tracking.
     */
    protected function isUrlExcluded(Request $request): bool
    {
        $excludedUrls = config('dittofeed.excluded_urls', []);

        if (empty($excludedUrls)) {
            return false;
        }

        $currentUrl = $request->fullUrl();

        foreach ($excludedUrls as $pattern) {
            if (Str::is($pattern, $currentUrl)) {
                return true;
            }
        }

        return false;
    }
}
