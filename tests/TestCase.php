<?php

namespace Tests;

use Database\Seeders\BusinessTypeSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('business_types')) {
            $this->seed(BusinessTypeSeeder::class);
        }
    }
}
