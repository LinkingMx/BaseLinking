import { route } from 'ziggy-js';

/**
 * Safe route helper that handles disabled routes
 * Returns '#' for disabled routes or the actual route URL
 * 
 * @param routeName - The name of the route
 * @param params - Route parameters
 * @param absolute - Whether to generate absolute URL
 * @returns The route URL or '#' if route is disabled
 */
export function safeRoute(routeName: string, params?: any, absolute?: boolean): string {
    try {
        // Check if route exists before calling it
        if (route().has(routeName)) {
            return route(routeName, params, absolute);
        } else {
            console.warn(`Route '${routeName}' is disabled or does not exist. Returning placeholder.`);
            return '#';
        }
    } catch (error) {
        console.warn(`Error generating route '${routeName}':`, error);
        return '#';
    }
}

/**
 * Check if a route is available/enabled
 * 
 * @param routeName - The name of the route to check
 * @returns True if route exists and is enabled
 */
export function isRouteEnabled(routeName: string): boolean {
    return route().has(routeName);
}

/**
 * Route helper with fallback URL
 * 
 * @param routeName - The name of the route
 * @param fallbackUrl - URL to use if route is disabled
 * @param params - Route parameters
 * @param absolute - Whether to generate absolute URL
 * @returns The route URL or fallback URL if route is disabled
 */
export function routeWithFallback(
    routeName: string, 
    fallbackUrl: string, 
    params?: any, 
    absolute?: boolean
): string {
    try {
        if (route().has(routeName)) {
            return route(routeName, params, absolute);
        } else {
            return fallbackUrl;
        }
    } catch (error) {
        console.warn(`Error generating route '${routeName}':`, error);
        return fallbackUrl;
    }
}
