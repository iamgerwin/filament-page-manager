<?php

declare(strict_types=1);

namespace IamGerwin\FilamentPageManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateTemplateCommand extends Command
{
    protected $signature = 'filament-page-manager:make-template {name} {--type=page} {--force}';

    protected $description = 'Create a new page or region template';

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $type = (string) $this->option('type');
        $force = (bool) $this->option('force');

        if (! in_array($type, ['page', 'region'])) {
            $this->error('Invalid template type. Must be "page" or "region".');

            return self::FAILURE;
        }

        $className = Str::studly($name);
        if (! str_ends_with($className, 'Template')) {
            $className .= 'Template';
        }

        $namespace = $type === 'page'
            ? 'App\\PageTemplates'
            : 'App\\RegionTemplates';

        $directory = $type === 'page'
            ? app_path('PageTemplates')
            : app_path('RegionTemplates');

        $filePath = "{$directory}/{$className}.php";

        if (File::exists($filePath) && ! $force) {
            $this->error("Template {$className} already exists!");

            return self::FAILURE;
        }

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $stub = $this->getStub($type);
        $content = str_replace(
            ['{{namespace}}', '{{className}}', '{{name}}'],
            [$namespace, $className, Str::headline(str_replace('Template', '', $className))],
            $stub
        );

        File::put($filePath, $content);

        $this->info("Template {$className} created successfully at {$filePath}");
        $this->info("Don't forget to register it in config/filament-page-manager.php");

        return self::SUCCESS;
    }

    protected function getStub(string $type): string
    {
        $stubPath = __DIR__.'/../../../stubs/'.($type === 'page' ? 'page-template.stub' : 'region-template.stub');

        if (File::exists($stubPath)) {
            return File::get($stubPath);
        }

        if ($type === 'page') {
            return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use IamGerwin\FilamentPageManager\Templates\AbstractPageTemplate;

class {{className}} extends AbstractPageTemplate
{
    public function name(): string
    {
        return '{{name}}';
    }

    public function fields(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255),

                    RichEditor::make('content')
                        ->label('Content')
                        ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }
}
PHP;
        }

        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use IamGerwin\FilamentPageManager\Templates\AbstractRegionTemplate;

class {{className}} extends AbstractRegionTemplate
{
    public function name(): string
    {
        return '{{name}}';
    }

    public function fields(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255),

                    RichEditor::make('content')
                        ->label('Content')
                        ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }
}
PHP;
    }
}
