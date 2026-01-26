<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModularIdeHelperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:ide-helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate IDE helper file for module configs and names';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $modules = $registry->getModules();

        $content = "<?php\n\n/**\n * @method static mixed module(string \$name = null)\n */\nclass Modular {}\n\n";

        // Improve config() inference if possible?
        // A truly powerful IDE helper for config() requires deeper integration with barryvdh/laravel-ide-helper
        // or generating a 'config/modules_ide.php' that Laravel IDE plugins can scan.
        // Let's create a meta file that exposes all module config keys purely for reference
        // or a PHPDoc for the `module()` helper.

        $lines = [];
        $lines[] = 'namespace {';
        $lines[] = '    // Available Modules:';

        foreach ($modules as $name => $config) {
            $lines[] = "    // - {$name} (v{$config['version']})";
        }
        $lines[] = '}';

        $output = implode("\n", $lines);

        File::put(base_path('_ide_helper_modular.php'), $output);

        $this->info('_ide_helper_modular.php generated.');

        return self::SUCCESS;
    }
}
