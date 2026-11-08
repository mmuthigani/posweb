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

        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->headers->get('Location'));
        $this->assertTrue(session()->has('login_session_expired'));
    }
}
