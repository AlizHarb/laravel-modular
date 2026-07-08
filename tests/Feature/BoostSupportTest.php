<?php

use Illuminate\Support\Facades\File;

it('ships laravel boost package guidelines', function () {
    $path = __DIR__.'/../../resources/boost/guidelines/core.blade.php';

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);

    expect($contents)
        ->toContain('Laravel Modular')
        ->toContain('php artisan make:module Blog')
        ->toContain('--module=Blog')
        ->toContain('php artisan modular:doctor')
        ->toContain('php artisan modular:refresh');
});

it('ships a laravel boost development skill', function () {
    $path = __DIR__.'/../../resources/boost/skills/laravel-modular-development/SKILL.md';

    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);

    expect($contents)
        ->toContain('name: laravel-modular-development')
        ->toContain('php artisan modular:list')
        ->toContain('php artisan modular:test ModuleName')
        ->toContain('Completion Checklist');
});
