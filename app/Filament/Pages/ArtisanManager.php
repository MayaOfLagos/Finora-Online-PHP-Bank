<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use UnitEnum;

class ArtisanManager extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCommandLine;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static ?int $navigationGroupSort = 998;

    protected static ?string $title = 'Artisan Manager';

    protected static ?string $slug = 'artisan-manager';

    protected static ?string $navigationLabel = 'Artisan Commands';

    protected string $view = 'filament.pages.artisan-manager';

    public string $commandOutput = '';

    public string $selectedCommand = '';

    public string $customCommand = '';

    public bool $showOutput = false;

    /**
     * Get all available artisan commands grouped by category
     */
    public function getCommandGroups(): array
    {
        return [
            'Cache & Config' => [
                [
                    'name' => 'cache:clear',
                    'description' => 'Clear the application cache',
                    'icon' => 'heroicon-o-trash',
                    'color' => 'warning',
                ],
                [
                    'name' => 'config:clear',
                    'description' => 'Clear the configuration cache',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'color' => 'warning',
                ],
                [
                    'name' => 'config:cache',
                    'description' => 'Create a configuration cache file',
                    'icon' => 'heroicon-o-bolt',
                    'color' => 'success',
                ],
                [
                    'name' => 'route:clear',
                    'description' => 'Clear the route cache',
                    'icon' => 'heroicon-o-map',
                    'color' => 'warning',
                ],
                [
                    'name' => 'route:cache',
                    'description' => 'Create a route cache file',
                    'icon' => 'heroicon-o-map',
                    'color' => 'success',
                ],
                [
                    'name' => 'view:clear',
                    'description' => 'Clear all compiled view files',
                    'icon' => 'heroicon-o-eye',
                    'color' => 'warning',
                ],
                [
                    'name' => 'view:cache',
                    'description' => 'Compile all Blade templates',
                    'icon' => 'heroicon-o-eye',
                    'color' => 'success',
                ],
                [
                    'name' => 'event:clear',
                    'description' => 'Clear cached events and listeners',
                    'icon' => 'heroicon-o-bell',
                    'color' => 'warning',
                ],
                [
                    'name' => 'event:cache',
                    'description' => 'Cache the application events',
                    'icon' => 'heroicon-o-bell',
                    'color' => 'success',
                ],
            ],
            'Optimization' => [
                [
                    'name' => 'optimize',
                    'description' => 'Cache bootstrap files for better performance',
                    'icon' => 'heroicon-o-rocket-launch',
                    'color' => 'success',
                ],
                [
                    'name' => 'optimize:clear',
                    'description' => 'Clear all cached bootstrap files',
                    'icon' => 'heroicon-o-arrow-path',
                    'color' => 'warning',
                ],
                [
                    'name' => 'icons:cache',
                    'description' => 'Cache Blade icons for better performance',
                    'icon' => 'heroicon-o-sparkles',
                    'color' => 'success',
                ],
                [
                    'name' => 'icons:clear',
                    'description' => 'Clear the Blade icons cache',
                    'icon' => 'heroicon-o-sparkles',
                    'color' => 'warning',
                ],
                [
                    'name' => 'filament:cache-components',
                    'description' => 'Cache Filament components',
                    'icon' => 'heroicon-o-squares-2x2',
                    'color' => 'success',
                ],
                [
                    'name' => 'filament:clear-cached-components',
                    'description' => 'Clear Filament cached components',
                    'icon' => 'heroicon-o-squares-2x2',
                    'color' => 'warning',
                ],
            ],
            'Database' => [
                [
                    'name' => 'migrate',
                    'description' => 'Run database migrations',
                    'icon' => 'heroicon-o-circle-stack',
                    'color' => 'primary',
                    'dangerous' => true,
                ],
                [
                    'name' => 'migrate:status',
                    'description' => 'Show the status of each migration',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'color' => 'info',
                ],
                [
                    'name' => 'db:seed',
                    'description' => 'Seed the database with records',
                    'icon' => 'heroicon-o-server-stack',
                    'color' => 'primary',
                    'dangerous' => true,
                ],
            ],
            'Storage' => [
                [
                    'name' => 'storage:link',
                    'description' => 'Create a symbolic link from public/storage',
                    'icon' => 'heroicon-o-link',
                    'color' => 'primary',
                ],
            ],
            'Queue' => [
                [
                    'name' => 'queue:work --stop-when-empty',
                    'description' => 'Process queued jobs (stops when empty)',
                    'icon' => 'heroicon-o-queue-list',
                    'color' => 'primary',
                ],
                [
                    'name' => 'queue:retry all',
                    'description' => 'Retry all failed queue jobs',
                    'icon' => 'heroicon-o-arrow-path',
                    'color' => 'warning',
                ],
                [
                    'name' => 'queue:clear',
                    'description' => 'Clear all jobs from the queue',
                    'icon' => 'heroicon-o-trash',
                    'color' => 'danger',
                    'dangerous' => true,
                ],
                [
                    'name' => 'queue:failed',
                    'description' => 'List all failed queue jobs',
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'color' => 'danger',
                ],
                [
                    'name' => 'queue:flush',
                    'description' => 'Flush all failed queue jobs',
                    'icon' => 'heroicon-o-trash',
                    'color' => 'danger',
                    'dangerous' => true,
                ],
            ],
            'Maintenance' => [
                [
                    'name' => 'down',
                    'description' => 'Put the application into maintenance mode',
                    'icon' => 'heroicon-o-wrench-screwdriver',
                    'color' => 'danger',
                    'dangerous' => true,
                ],
                [
                    'name' => 'up',
                    'description' => 'Bring the application out of maintenance mode',
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'success',
                ],
            ],
            'Scheduler' => [
                [
                    'name' => 'schedule:list',
                    'description' => 'List all scheduled tasks',
                    'icon' => 'heroicon-o-clock',
                    'color' => 'info',
                ],
                [
                    'name' => 'schedule:run',
                    'description' => 'Run the scheduled commands',
                    'icon' => 'heroicon-o-play',
                    'color' => 'primary',
                ],
            ],
            'Information' => [
                [
                    'name' => 'about',
                    'description' => 'Display basic application information',
                    'icon' => 'heroicon-o-information-circle',
                    'color' => 'info',
                ],
                [
                    'name' => 'route:list',
                    'description' => 'List all registered routes',
                    'icon' => 'heroicon-o-map',
                    'color' => 'info',
                ],
                [
                    'name' => 'env',
                    'description' => 'Display the current environment',
                    'icon' => 'heroicon-o-globe-alt',
                    'color' => 'info',
                ],
            ],
        ];
    }

    /**
     * Execute an artisan command
     */
    public function runCommand(string $command): void
    {
        try {
            $output = new BufferedOutput();
            
            // Parse command and arguments
            $parts = explode(' ', $command);
            $commandName = array_shift($parts);
            
            // Build arguments array
            $arguments = [];
            foreach ($parts as $part) {
                if (str_starts_with($part, '--')) {
                    // Handle flags like --stop-when-empty
                    $flag = ltrim($part, '-');
                    if (str_contains($flag, '=')) {
                        [$key, $value] = explode('=', $flag, 2);
                        $arguments["--{$key}"] = $value;
                    } else {
                        $arguments["--{$flag}"] = true;
                    }
                } else {
                    // Handle positional arguments
                    $arguments[] = $part;
                }
            }
            
            // Add --no-interaction flag to prevent prompts
            $arguments['--no-interaction'] = true;
            
            $exitCode = Artisan::call($commandName, $arguments, $output);
            
            $this->commandOutput = $output->fetch();
            $this->selectedCommand = $command;
            $this->showOutput = true;
            
            if ($exitCode === 0) {
                Notification::make()
                    ->title('Command executed successfully')
                    ->body("Command: {$command}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Command completed with warnings')
                    ->body("Exit code: {$exitCode}")
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            $this->commandOutput = "Error: " . $e->getMessage();
            $this->selectedCommand = $command;
            $this->showOutput = true;
            
            Notification::make()
                ->title('Command failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Run a custom artisan command
     */
    public function runCustomCommand(): void
    {
        if (empty($this->customCommand)) {
            Notification::make()
                ->title('No command entered')
                ->body('Please enter a command to run')
                ->warning()
                ->send();
            return;
        }

        // Security: Block dangerous commands
        $blockedCommands = [
            'migrate:fresh',
            'migrate:reset',
            'migrate:rollback',
            'db:wipe',
            'key:generate',
            'env:encrypt',
            'env:decrypt',
            'tinker',
            'serve',
        ];

        $commandName = explode(' ', trim($this->customCommand))[0];
        
        if (in_array($commandName, $blockedCommands)) {
            Notification::make()
                ->title('Command blocked')
                ->body("The command '{$commandName}' is blocked for security reasons.")
                ->danger()
                ->send();
            return;
        }

        $this->runCommand($this->customCommand);
        $this->customCommand = '';
    }

    /**
     * Clear the output
     */
    public function clearOutput(): void
    {
        $this->commandOutput = '';
        $this->selectedCommand = '';
        $this->showOutput = false;
    }

    /**
     * Run quick optimization commands
     */
    public function quickOptimize(): void
    {
        try {
            $output = new BufferedOutput();
            $commands = [
                'config:cache',
                'route:cache',
                'view:cache',
                'event:cache',
                'icons:cache',
            ];

            $results = [];
            foreach ($commands as $command) {
                Artisan::call($command, ['--no-interaction' => true], $output);
                $results[] = "✓ {$command}";
            }

            $this->commandOutput = implode("\n", $results) . "\n\n" . $output->fetch();
            $this->selectedCommand = 'Quick Optimize (All Caches)';
            $this->showOutput = true;

            Notification::make()
                ->title('Optimization complete')
                ->body('All caches have been generated')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Optimization failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Clear all caches
     */
    public function clearAllCaches(): void
    {
        try {
            $output = new BufferedOutput();
            $commands = [
                'cache:clear',
                'config:clear',
                'route:clear',
                'view:clear',
                'event:clear',
            ];

            $results = [];
            foreach ($commands as $command) {
                Artisan::call($command, ['--no-interaction' => true], $output);
                $results[] = "✓ {$command}";
            }

            $this->commandOutput = implode("\n", $results) . "\n\n" . $output->fetch();
            $this->selectedCommand = 'Clear All Caches';
            $this->showOutput = true;

            Notification::make()
                ->title('All caches cleared')
                ->body('Application caches have been cleared')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to clear caches')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quickOptimize')
                ->label('Quick Optimize')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->action(fn () => $this->quickOptimize())
                ->requiresConfirmation()
                ->modalHeading('Run Quick Optimization?')
                ->modalDescription('This will cache config, routes, views, events, and icons for better performance.')
                ->modalSubmitActionLabel('Optimize'),

            Action::make('clearAll')
                ->label('Clear All Caches')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(fn () => $this->clearAllCaches())
                ->requiresConfirmation()
                ->modalHeading('Clear All Caches?')
                ->modalDescription('This will clear all application caches. You may need to re-cache for optimal performance.')
                ->modalSubmitActionLabel('Clear All'),
        ];
    }

    public static function canAccess(): bool
    {
        // Only super admins can access this page
        $user = auth()->user();
        return $user && ($user->is_admin || $user->role === 'admin' || $user->role === 'super_admin');
    }
}
