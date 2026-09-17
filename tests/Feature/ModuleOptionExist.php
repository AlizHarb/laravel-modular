<?php

use Illuminate\Support\Facades\Artisan;

it('has the module option', function () {
    $artisan = Artisan::getFacadeRoot();

    $command = $artisan->all()['make:model'];

    expect($command->getDefinition()->hasOption('module'))->toBeTrue();
});