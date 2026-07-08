<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Events;

final readonly class ModularRefreshed
{
    public function __construct(public string $cachePath) {}
}
