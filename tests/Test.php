<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\Tenants\EventProvider;
use Hyn\Tenancy\Providers\WebserverProvider;
use Hyn\Tenancy\Tests\Traits\InteractsWithTenancy;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;
use Throwable;

/**
 * Class Test
 * @package Hyn\Tenancy\Tests
 */
class Test extends TestCase
{
    use InteractsWithTenancy;
    /**
     * Service providers to load during this test.
     *
     * @var array
     */
    protected $loadProviders = [
        TenancyProvider::class,
        WebserverProvider::class
    ];

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $appPaths = [];
        if (getenv('CI_PROJECT_DIR')) {
            $appPaths[] = realpath(getenv('CI_PROJECT_DIR') . '/vendor/laravel/laravel');
        }
        $appPaths[] = realpath(__DIR__ . '/..');
        $appPaths[] = realpath(__DIR__ . '/../vendor/laravel/laravel');

        foreach ($appPaths as $path) {
            $path = "$path/bootstrap/app.php";
            if (file_exists($path)) {
                /** @var Application $app */
                $app = require $path;
                break;
            }
        }

        if (!$app) {
            throw new \RuntimeException("No bootstrap file found, make sure laravel/laravel is installed");
        }

        $app->make(Kernel::class)->bootstrap();

        foreach ($this->loadProviders as $provider) {
            if (!$app->register($provider)) {
                throw new \RuntimeException("Failed registering $provider");
            }
        }

        \Schema::defaultStringLength(191);

        touch(database_path('database.sqlite'));

        $this->duringSetUp($app);

        return $app;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // Rebinds event listeners to new dispatcher.
        $this->app->call([
            new EventProvider($this->app),
            'boot'
        ]);
    }

    /**
     * Allows implementation in a test.
     *
     * @param Application $app
     */
    protected function duringSetUp(Application $app)
    {
        // ..
    }

    /**
     * {@inheritdoc}
     */
    protected function onNotSuccessfulTest(Throwable $t)
    {
        $this->cleanupTenancy();
        parent::onNotSuccessfulTest($t);
    }

    protected function tearDown()
    {
        $this->cleanupTenancy();
        parent::tearDown();
    }
}