<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

final class ModularGraphCommand extends Command
{
    protected $signature = 'modular:graph {--format=ascii : Output format [ascii,json,dot]}';

    protected $description = 'Display the module dependency graph';

    public function handle(ModuleRegistry $registry): int
    {
        $modules = $registry->getModules();
        $edges = [];

        foreach ($modules as $name => $module) {
            foreach ($module['requires'] ?? [] as $dependency) {
                if (! is_string($dependency) || trim($dependency) === '') {
                    continue;
                }

                $edges[] = [
                    'from' => $name,
                    'to' => explode(':', $dependency)[0],
                    'constraint' => explode(':', $dependency)[1] ?? null,
                ];
            }
        }

        return match ($this->option('format')) {
            'json' => $this->renderJson($modules, $edges),
            'dot' => $this->renderDot($modules, $edges),
            default => $this->renderAscii($modules, $edges),
        };
    }

    /**
     * @param array<string, array<string, mixed>> $modules
     * @param array<int, array{from: string, to: string, constraint: string|null}> $edges
     */
    private function renderJson(array $modules, array $edges): int
    {
        $this->line((string) json_encode([
            'modules' => array_keys($modules),
            'edges' => $edges,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    /**
     * @param array<string, array<string, mixed>> $modules
     * @param array<int, array{from: string, to: string, constraint: string|null}> $edges
     */
    private function renderDot(array $modules, array $edges): int
    {
        $this->line('digraph Modular {');

        foreach (array_keys($modules) as $module) {
            $this->line("    \"{$module}\";");
        }

        foreach ($edges as $edge) {
            $label = $edge['constraint'] ? " [label=\"{$edge['constraint']}\"]" : '';
            $this->line("    \"{$edge['from']}\" -> \"{$edge['to']}\"{$label};");
        }

        $this->line('}');

        return self::SUCCESS;
    }

    /**
     * @param array<string, array<string, mixed>> $modules
     * @param array<int, array{from: string, to: string, constraint: string|null}> $edges
     */
    private function renderAscii(array $modules, array $edges): int
    {
        $this->components->info('Module Dependency Graph');

        if ($edges === []) {
            foreach (array_keys($modules) as $module) {
                $this->line("  {$module}");
            }

            return self::SUCCESS;
        }

        foreach ($edges as $edge) {
            $constraint = $edge['constraint'] ? " ({$edge['constraint']})" : '';
            $this->line("  {$edge['from']} -> {$edge['to']}{$constraint}");
        }

        return self::SUCCESS;
    }
}
