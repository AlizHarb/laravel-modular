<?php

use AlizHarb\Modular\Activators\FileActivator;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->statusFile = base_path('bootstrap/cache/modules_statuses.json');
    if (File::exists($this->statusFile)) {
        File::delete($this->statusFile);
    }
});

it('can enable a module', function () {
    $activator = new FileActivator();

    $activator->setStatus('Blog', true);

    expect($activator->isEnabled('Blog'))->toBeTrue();
    expect(File::exists($this->statusFile))->toBeTrue();
});

it('can disable a module', function () {
    $activator = new FileActivator();

    $activator->setStatus('Blog', false);

    expect($activator->isEnabled('Blog'))->toBeFalse();

    $content = json_decode(File::get($this->statusFile), true);
    expect($content['Blog'])->toBeFalse();
});

it('defaults to enabled if no status exists', function () {
    $activator = new FileActivator();

    expect($activator->isEnabled('NonExistent'))->toBeTrue();
});

it('can delete a module status', function () {
    $activator = new FileActivator();
    $activator->setStatus('Blog', false);

    $activator->delete('Blog');

    expect($activator->isEnabled('Blog'))->toBeTrue();
});

it('can reset all statuses', function () {
    $activator = new FileActivator();
    $activator->setStatus('Blog', false);
    $activator->setStatus('Shop', false);

    $activator->reset();

    expect($activator->isEnabled('Blog'))->toBeTrue();
    expect($activator->isEnabled('Shop'))->toBeTrue();
});
