<?php

namespace AlizHarb\Modular\Tests;

use AlizHarb\Modular\ModularServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @var \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Testing\TestResponse|null
     */
    protected static $latestResponse;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic module structure for testing
        $basePath = __DIR__.'/../build/test-app';
        $this->app->setBasePath($basePath);
        $this->app['config']->set('modular.paths.modules', $basePath.'/modules');

        // Ensure the directory exists
        if (! is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        // Create dummy composer.json to satisfy application namespace resolution
        if (! file_exists($basePath.'/composer.json')) {
            file_put_contents($basePath.'/composer.json', json_encode([
                'autoload' => [
                    'psr-4' => [
                        'App\\' => 'app/',
                    ],
                ],
            ]));
        }

        if (! is_dir($basePath.'/bootstrap/cache')) {
            mkdir($basePath.'/bootstrap/cache', 0755, true);
        }

        if (! is_dir(base_path('modules'))) {
            mkdir(base_path('modules'), 0755, true);
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            ModularServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        
        $basePath = __DIR__.'/../build/test-app';
        
        config()->set('modular.activators.file.statuses-file', $basePath.'/bootstrap/cache/modules_statuses.json');
        config()->set('modular.cache.path', $basePath.'/bootstrap/cache/modular.php');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-modular_table.php.stub';
        $migration->up();
        */
    }
}
