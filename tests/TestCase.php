<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // The app mixes API + web auth flows; disable CSRF in feature tests to
        // keep framework auth tests deterministic.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        // Prevent stale permission cache between database refreshes.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
