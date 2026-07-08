<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::deleteDirectory(base_path('Modules'));
    File::deleteDirectory(base_path('NwidartModules'));
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    app()->forgetInstance(ModuleRegistry::class);
});

afterEach(function () {
    File::deleteDirectory(base_path('Modules'));
    File::deleteDirectory(base_path('NwidartModules'));
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    app()->forgetInstance(ModuleRegistry::class);
});

it('previews importing nwidart modules', function () {
    File::ensureDirectoryExists(base_path('NwidartModules/Blog'));
    File::put(base_path('NwidartModules/Blog/module.json'), json_encode([
        'name' => 'Blog',
        'providers' => ['Modules\\Blog\\Providers\\BlogServiceProvider'],
    ]));

    $this->artisan('modular:import-nwidart --from=NwidartModules --dry-run')
        ->expectsOutputToContain('nwidart import plan')
        ->expectsOutputToContain('Blog')
        ->assertExitCode(0);

    expect(File::exists(base_path('modules/Blog')))->toBeFalse();
});

it('imports a nwidart module into the modular path', function () {
    File::ensureDirectoryExists(base_path('NwidartModules/Blog/Routes'));
    File::put(base_path('NwidartModules/Blog/module.json'), json_encode([
        'name' => 'Blog',
        'providers' => ['Modules\\Blog\\Providers\\BlogServiceProvider'],
        'requires' => [],
    ]));
    File::put(base_path('NwidartModules/Blog/Routes/web.php'), '<?php');

    $this->artisan('modular:import-nwidart Blog --from=NwidartModules')
        ->assertExitCode(0);

    expect(File::exists(base_path('modules/Blog/module.json')))->toBeTrue();
    expect(File::exists(base_path('modules/Blog/Routes/web.php')))->toBeTrue();

    $manifest = json_decode(File::get(base_path('modules/Blog/module.json')), true);

    expect($manifest['name'])->toBe('Blog');
    expect($manifest['providers'])->toContain('Modules\\Blog\\Providers\\BlogServiceProvider');
});
