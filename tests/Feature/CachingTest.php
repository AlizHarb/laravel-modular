<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->cachePath = base_path('bootstrap/cache/modular.php');
    if (File::exists($this->cachePath)) {
        File::delete($this->cachePath);
    }
});

it('can cache the module registry', function () {
    // Manually create a module directory to discover
    $modulePath = base_path('modules/Blog');
    if (!is_dir($modulePath)) {
        mkdir($modulePath, 0755, true);
    }
    file_put_contents($modulePath.'/module.json', json_encode(['name' => 'Blog', 'active' => true]));

    $registry = new ModuleRegistry();
    $registry->cache();

    expect(File::exists($this->cachePath))->toBeTrue();
    
    $cachedData = require $this->cachePath;
    expect($cachedData)->toHaveKey('Blog');
    
    // Cleanup
    File::deleteDirectory(base_path('modules/Blog'));
});

it('uses the cache if it exists', function () {
    $cacheData = [
        'CachedModule' => [
            'name' => 'CachedModule',
            'path' => '/fake/path',
            'namespace' => 'Modules\\CachedModule\\',
            'provider' => null,
            'requires' => [],
        ]
    ];
    
    if (!is_dir(dirname($this->cachePath))) {
        mkdir(dirname($this->cachePath), 0755, true);
    }
    
    file_put_contents($this->cachePath, '<?php return '.var_export($cacheData, true).';');

    $registry = new ModuleRegistry();
    
    expect($registry->moduleExists('CachedModule'))->toBeTrue();
    expect($registry->getModule('CachedModule')['path'])->toBe('/fake/path');
});

it('can clear the modular cache', function () {
    if (!is_dir(dirname($this->cachePath))) {
        mkdir(dirname($this->cachePath), 0755, true);
    }
    file_put_contents($this->cachePath, '<?php return [];');

    $registry = new ModuleRegistry();
    $registry->clearCache();

    expect(File::exists($this->cachePath))->toBeFalse();
});
