<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationTimezoneTest extends TestCase
{
    public function test_application_stores_times_in_utc_and_uses_east_africa_for_business_dates(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('Africa/Nairobi', config('app.business_timezone'));
        $this->assertSame('UTC', now()->timezoneName);
    }
}
