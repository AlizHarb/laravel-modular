<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

final class ModularWhyCommand extends Command
{
    protected $signature = 'modular:why {module : The module to explain} {--json : Output explanation as JSON}';

    protected $description = 'Explain module status, dependencies, and dependents';

    public function handle(ModuleRegistry $registry): int
    {
        $name = (string) $this->argument('module');

        if (! $registry->moduleExists($name)) {
            $this->components->error("Module [{$name}] not found.");

            return self::FAILURE;
        }

        $module = $registry->getModule($name);

        if ($module === null) {
            $this->components->error("Module [{$name}] not found.");

            return self::FAILURE;
        }

        $requiredBy = [];

        foreach ($registry->getModules() as $candidateName => $candidate) {
            foreach ($candidate['requires'] ?? [] as $dependency) {
                if (is_string($dependency) && explode(':', $dependency)[0] === $name) {
                    $requiredBy[] = $candidateName;
                }
            }
        }

        $payload = [
            'name' => $name,
            'enabled' => $registry->isEnabled($name),
            'version' => $module['version'],
            'path' => $module['path'],
            'requires' => $module['requires'],
            'conflicts' => $module['conflicts'],
            'provides' => $module['provides'],
            'required_by' => array_values(array_unique($requiredBy)),
            'dependencies' => $registry->checkDependencies($name),
            'disableable' => $module['disableable'],
            'removable' => $module['removable'],
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info("Why module [{$name}]?");
        $this->components->twoColumnDetail('Status', $payload['enabled'] ? 'Enabled' : 'Disabled');
        $this->components->twoColumnDetail('Version', (string) $payload['version']);
        $this->components->twoColumnDetail('Path', (string) $payload['path']);
        $this->components->twoColumnDetail('Requires', $payload['requires'] === [] ? 'None' : implode(', ', $payload['requires']));
        $this->components->twoColumnDetail('Conflicts', $payload['conflicts'] === [] ? 'None' : implode(', ', $payload['conflicts']));
        $this->components->twoColumnDetail('Provides', $payload['provides'] === [] ? 'None' : implode(', ', $payload['provides']));
        $this->components->twoColumnDetail('Required by', $payload['required_by'] === [] ? 'None' : implode(', ', $payload['required_by']));
        $this->components->twoColumnDetail('Dependencies', $payload['dependencies']['satisfied'] ? 'Satisfied' : 'Missing: '.implode(', ', $payload['dependencies']['missing']));

        return self::SUCCESS;
    }
}
