<?php

use AlizHarb\Modular\Facades\Modular;

it('can register the facade', function () {
    expect(app('modular.registry'))->toBeInstanceOf(\AlizHarb\Modular\ModuleRegistry::class);
});

it('can resolve a module path', function () {
    // Mock the registry or setup a real module in TestCase setup
    // For now, testing the resolvePath behavior if module doesn't exist (fallback)

    $path = Modular::resolvePath('TestModule', 'app/Http/Controllers');
    expect($path)->toContain('modules/TestModule/app/Http/Controllers');
});
