<?php

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExpiredLoginSessionTest extends TestCase
{
    public function test_expired_login_session_redirects_to_login_instead_of_showing_page_expired(): void
    {
        $request = Request::create('/login', 'POST');
        $request->setRouteResolver(fn () => Route::getRoutes()->match($request));
        $this->app->instance('request', $request);
        $this->app['url']->setRequest($request);

        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->headers->get('Location'));
        $this->assertTrue(session()->has('login_session_expired'));
    }

    public function test_login_page_is_not_cached_by_mobile_browsers_or_proxies(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }
}
