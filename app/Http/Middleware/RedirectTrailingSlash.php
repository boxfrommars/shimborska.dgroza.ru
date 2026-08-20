<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectTrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $path = $request->getPathInfo();

        if (
            !in_array($request->getMethod(), ['GET', 'HEAD'], true)
            || $path === '/'
            || !str_ends_with($path, '/')
            || $response->getStatusCode() >= Response::HTTP_BAD_REQUEST
        ) {
            return $response;
        }

        $target = rtrim($path, '/') ?: '/';
        $queryString = $request->server->get('QUERY_STRING');

        if (is_string($queryString) && $queryString !== '') {
            $target .= '?' . $queryString;
        }

        return new RedirectResponse($target, Response::HTTP_MOVED_PERMANENTLY);
    }
}
