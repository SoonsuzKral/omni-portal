<?php

namespace App\Filament\Resources\EnvVariableResource\Pages;

use App\Models\EnvVariable;
use App\Services\EnvManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ServiceManager extends Page
{
    public $envManager;
    public $services;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $title = 'Service Installer';
    protected static ?string $navigationLabel = 'Services';
    protected static ?string $slug = 'services';

    public function mount(): void
    {
        $this->envManager = app(EnvManager::class);
        $this->services = EnvVariable::services();
    }

    protected function getViewData(): array
    {
        return [
            'envManager' => $this->envManager,
            'services' => $this->services,
        ];
    }

    public function getView(): string
    {
        return 'filament.pages.service-manager';
    }

    public function handleAction(Request $request)
    {
        $action = $request->input('action');
        $serviceName = $request->input('service');
        $envManager = app(EnvManager::class);

        switch ($action) {
            case 'install':
                $envManager->installService($serviceName);
                Notification::make()->title('Service Installed')->body("{$serviceName} added!")->success()->send();
                break;
            case 'enable':
                $envManager->enableService($serviceName);
                $envManager->exportToEnvFile();
                Artisan::call('config:clear');
                Notification::make()->title('Service Enabled')->success()->send();
                break;
            case 'disable':
                $envManager->disableService($serviceName);
                $envManager->exportToEnvFile();
                Artisan::call('config:clear');
                Notification::make()->title('Service Disabled')->warning()->send();
                break;
            case 'save':
                $variables = $request->input('variables', []);
                foreach ($variables as $key => $value) {
                    EnvVariable::where('key', $key)->update(['value' => $value]);
                }
                $envManager->exportToEnvFile();
                Artisan::call('config:clear');
                Notification::make()->title('Saved & Exported')->success()->send();
                break;
        }
    }
}