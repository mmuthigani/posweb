<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationRouteTest extends TestCase
{
    public function test_documentation_is_available_without_sign_in(): void
    {
        $this->get(route('documentation'))
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('<title>Kikimo POS Management Dashboard — User Guide</title>', false);
    }
}
