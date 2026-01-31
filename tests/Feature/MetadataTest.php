<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\File;

it('discovers removable and disableable metadata', function () {
    $modulePath = base_path('modules/MetadataModule');
    
    if (File::exists($modulePath)) {
        File::deleteDirectory($modulePath);
    }
    
    File::makeDirectory($modulePath, 0755, true);
    
    $json = json_encode([
        'name' => 'MetadataModule',
        'removable' => false,
        'disableable' => false,
    ]);
    
    File::put($modulePath . '/module.json', $json);
    
    $registry = new ModuleRegistry();
    $module = $registry->getModule('MetadataModule');
    
    expect($module['removable'])->toBeFalse()
        ->and($module['disableable'])->toBeFalse();
        
    // Cleanup
    File::deleteDirectory($modulePath);
});

it('defaults removable and disableable to true', function () {
    $modulePath = base_path('modules/DefaultMetadataModule');
    
    if (File::exists($modulePath)) {
        File::deleteDirectory($modulePath);
    }
    
    File::makeDirectory($modulePath, 0755, true);
    
    $json = json_encode([
        'name' => 'DefaultMetadataModule',
    ]);
    
    File::put($modulePath . '/module.json', $json);
    
    $registry = new ModuleRegistry();
    $module = $registry->getModule('DefaultMetadataModule');
    
    expect($module['removable'])->toBeTrue()
        ->and($module['disableable'])->toBeTrue();
        
    // Cleanup
    File::deleteDirectory($modulePath);
});

it('prevents disabling a non-disableable module', function () {
    $modulePath = base_path('modules/ProtectedModule');
    
    if (File::exists($modulePath)) {
        File::deleteDirectory($modulePath);
    }
    
    File::makeDirectory($modulePath, 0755, true);
    
    $json = json_encode([
        'name' => 'ProtectedModule',
        'disableable' => false,
    ]);
    
    File::put($modulePath . '/module.json', $json);
    
    // Clear any existing cache
    (new ModuleRegistry())->clearCache();
    
    // Ensure registry picks it up
    $registry = new ModuleRegistry();
    // Re-bind singleton to ensure command picks up fresh registry
    app()->instance(ModuleRegistry::class, $registry);
    
    $registry->getActivator()->setStatus('ProtectedModule', true);
    
    $this->artisan('module:disable', ['module' => 'ProtectedModule'])
        ->expectsOutput('Module [ProtectedModule] cannot be disabled.')
        ->assertFailed();
        
    expect($registry->getActivator()->isEnabled('ProtectedModule'))->toBeTrue();
        
    // Cleanup
    File::deleteDirectory($modulePath);
});

it('prevents uninstalling a non-removable module', function () {
    $modulePath = base_path('modules/CriticalModule');
    
    if (File::exists($modulePath)) {
        File::deleteDirectory($modulePath);
    }
    
    File::makeDirectory($modulePath, 0755, true);
    
    $json = json_encode([
        'name' => 'CriticalModule',
        'removable' => false,
    ]);
    
    File::put($modulePath . '/module.json', $json);
    
    // Clear cache & refresh registry
    (new ModuleRegistry())->clearCache();
    $registry = new ModuleRegistry();
    app()->instance(ModuleRegistry::class, $registry);
    
    $this->artisan('module:uninstall', ['module' => 'CriticalModule'])
        ->expectsOutput('Module [CriticalModule] cannot be removed.')
        ->assertFailed();
        
    expect(File::exists($modulePath))->toBeTrue();
        
    // Cleanup
    File::deleteDirectory($modulePath);
});

it('successfully uninstalls a removable module', function () {
    $modulePath = base_path('modules/DisposableModule');
    
    if (File::exists($modulePath)) {
        File::deleteDirectory($modulePath);
    }
    
    File::makeDirectory($modulePath, 0755, true);
    
    $json = json_encode([
        'name' => 'DisposableModule',
        'removable' => true,
    ]);
    
    File::put($modulePath . '/module.json', $json);
    
    // Clear cache & refresh registry
    (new ModuleRegistry())->clearCache();
    $registry = new ModuleRegistry();
    app()->instance(ModuleRegistry::class, $registry);
    
    $this->artisan('module:uninstall', ['module' => 'DisposableModule', '--force' => true])
        ->expectsConfirmation('Are you sure you want to uninstall module [DisposableModule]? This will delete all module files.', 'yes')
        ->expectsOutput('Module [DisposableModule] uninstalled successfully.')
        ->assertSuccessful();
        
    expect(File::exists($modulePath))->toBeFalse();
});

