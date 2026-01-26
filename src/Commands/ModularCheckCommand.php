<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

class ModularCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for circular dependencies and architectural violations';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $modules = $registry->getModules();
        $graph = [];
        $status = self::SUCCESS;
        $violations = [];

        $this->components->info('Checking '.count($modules).' modules for dependencies and architectural integrity...');

        foreach ($modules as $name => $config) {
            $requires = $config['requires'] ?? [];
            $graph[$name] = [];

            foreach ($requires as $requirement) {
                // Support "Module:^1.0" or "Module"
                $parts = explode(':', $requirement);
                $requiredModule = $parts[0];
                $constraint = $parts[1] ?? null;

                $graph[$name][] = $requiredModule;

                // 1. Check Existence
                if (! $registry->moduleExists($requiredModule)) {
                    $violations[] = "<fg=red>{$name}</> requires missing module <fg=yellow>{$requiredModule}</>";
                    $status = self::FAILURE;

                    continue;
                }

                // 2. Check Versions (Simple semver comparison if both have versions)
                if ($constraint) {
                    $metadata = $registry->getModule($requiredModule);
                    $installedVersion = $metadata['version'] ?? 'unknown';

                    // Note: Full semver validation usually requires composer/semver.
                    // For now, we perform a basic "starts with" or exact check to avoid adding dependencies.
                    if (! $this->satisfies($installedVersion, $constraint)) {
                        $violations[] = "<fg=red>{$name}</> requires <fg=yellow>{$requiredModule}:{$constraint}</>, but <fg=green>{$installedVersion}</> is installed.";
                        $status = self::FAILURE;
                    }
                }
            }
        }

        // 3. Circular Dependency Check
        $cycles = $this->detectCycles($graph);

        if (! empty($cycles)) {
            $this->components->error('Circular dependencies detected!');
            foreach ($cycles as $cycle) {
                $this->line('  - '.implode(' -> ', $cycle));
            }
            $status = self::FAILURE;
        }

        if (! empty($violations)) {
            $this->components->error('Dependency violations found!');
            foreach ($violations as $violation) {
                $this->line("  - {$violation}");
            }
        }

        if ($status === self::SUCCESS) {
            $this->components->info('All modules passed dependency checks.');
        }

        return $status;
    }

    /**
     * Determine if a version satisfies a constraint (Basic implementation).
     */
    protected function satisfies(string $version, string $constraint): bool
    {
        $constraint = ltrim($constraint, '^~=');

        return str_starts_with($version, $constraint);
    }

    /**
     * Detect cycles in a dependency graph.
     *
     * @param  array<string, array<int, string>>  $graph
     * @return array<int, array<int, string>>
     */
    protected function detectCycles(array $graph): array
    {
        $cycles = [];
        $visited = [];
        $recursionStack = [];

        foreach (array_keys($graph) as $node) {
            if (! isset($visited[$node])) {
                $this->findCycles($node, $graph, $visited, $recursionStack, $cycles);
            }
        }

        return $cycles;
    }

    /**
     * DFS function to find cycles.
     */
    protected function findCycles(string $node, array $graph, array &$visited, array &$recursionStack, array &$cycles): void
    {
        $visited[$node] = true;
        $recursionStack[$node] = true;

        $neighbors = $graph[$node] ?? [];
        foreach ($neighbors as $neighbor) {
            if (! isset($visited[$neighbor])) {
                $this->findCycles($neighbor, $graph, $visited, $recursionStack, $cycles);
            } elseif (isset($recursionStack[$neighbor]) && $recursionStack[$neighbor]) {
                // Cycle detected
                // Reconstruct the path for display
                $cycle = array_keys(array_filter($recursionStack));
                $cycle[] = $neighbor;
                // Only add if we haven't seen this cycle (simplification)
                $cycles[] = $cycle;
            }
        }

        $recursionStack[$node] = false;
    }
}
