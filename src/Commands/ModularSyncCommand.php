<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Console command to sync module dependencies into the root composer.json.
 */
final class ModularSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:sync {--dry-run : Only show what would be synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync module dependencies into the root composer.json';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Syncing module dependencies...');

        $composerJsonPath = base_path('composer.json');
        if (! File::exists($composerJsonPath)) {
            $this->components->error('Root composer.json not found.');

            return self::FAILURE;
        }

        $rootComposer = json_decode((string) File::get($composerJsonPath), true);
        $registry = app(ModuleRegistry::class);
        $modules = $registry->getModules();

        $allRequires = [];
        $allDevRequires = [];

        foreach ($modules as $module) {
            $moduleComposerPath = $module['path'].'/composer.json';
            if (! File::exists($moduleComposerPath)) {
                continue;
            }

            $moduleComposer = json_decode((string) File::get($moduleComposerPath), true);
            
            foreach ($moduleComposer['require'] ?? [] as $package => $version) {
                if ($this->shouldSync($package)) {
                    $allRequires[$package] = $version;
                }
            }

            foreach ($moduleComposer['require-dev'] ?? [] as $package => $version) {
                if ($this->shouldSync($package)) {
                    $allDevRequires[$package] = $version;
                }
            }
        }

        if (empty($allRequires) && empty($allDevRequires)) {
            $this->components->info('No module-specific dependencies found to sync.');

            return self::SUCCESS;
        }

        $this->displaySyncInfo($allRequires, 'Production Dependencies');
        $this->displaySyncInfo($allDevRequires, 'Dev Dependencies');

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        if ($this->confirm('Would you like to sync these dependencies to your root composer.json?', true)) {
            $rootComposer['require'] = array_merge($rootComposer['require'] ?? [], $allRequires);
            $rootComposer['require-dev'] = array_merge($rootComposer['require-dev'] ?? [], $allDevRequires);

            File::put($composerJsonPath, (string) json_encode($rootComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            
            $this->components->info('Dependencies synced successfully! Please run "composer update".');
        }

        return self::SUCCESS;
    }

    /**
     * Determine if a package should be synced.
     */
    private function shouldSync(string $package): bool
    {
        // Skip PHP and Laravel internal packages if they are usually in root
        $skip = ['php', 'ext-json', 'ext-mbstring', 'illuminate/contracts', 'illuminate/support'];
        
        return ! in_array($package, $skip);
    }

    /**
     * Display sync information in a table.
     *
     * @param array<string, string> $dependencies
     */
    private function displaySyncInfo(array $dependencies, string $title): void
    {
        if (empty($dependencies)) {
            return;
        }

        $this->line("\n<info>{$title}</info>");
        $rows = [];
        foreach ($dependencies as $package => $version) {
            $rows[] = [$package, $version];
        }

        $this->table(['Package', 'Version'], $rows);
    }
}
