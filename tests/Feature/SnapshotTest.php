<?php

use Spatie\Snapshots\MatchesSnapshots;

uses(MatchesSnapshots::class);

it('can verify snapshots work', function () {
    $data = ['foo' => 'bar', 'baz' => 123];
    
    // This is a dummy test to ensure the trait is loadable and working.
    // In a real scenario, this would generate a snapshot file on first run.
    expect($data)->toBe(['foo' => 'bar', 'baz' => 123]);
    
    // Using the trait method
    $this->assertMatchesJsonSnapshot($data);
});
