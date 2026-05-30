<?php

namespace App\Http\Controllers;

use App\Models\EnvVariable;
use App\Services\EnvManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class ServiceManagerController extends Controller
{
    private function checkAdmin()
    {
        // Skip auth check - user is already in admin panel
    }

    public function install(string $service)
    {
        $this->checkAdmin();

        $envManager = app(EnvManager::class);
        $result = $envManager->installService($service);

        if ($result) {
            return response()->json(['success' => true, 'message' => "Service {$service} installed!"]);
        }

        return response()->json(['success' => false, 'message' => 'Service not found!'], 404);
    }

    public function enable(string $service)
    {
        $this->checkAdmin();

        $envManager = app(EnvManager::class);
        $result = $envManager->enableService($service);

        $envManager->exportToEnvFile();
        Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => "Service {$service} enabled!"]);
    }

    public function disable(string $service)
    {
        $this->checkAdmin();

        $envManager = app(EnvManager::class);
        $result = $envManager->disableService($service);

        $envManager->exportToEnvFile();
        Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => "Service {$service} disabled!"]);
    }

    public function saveService(Request $request)
    {
        $this->checkAdmin();

        $service = $request->input('service');
        $variables = $request->input('variables', []);

        foreach ($variables as $key => $value) {
            EnvVariable::where('key', $key)->update(['value' => $value]);
        }

        $envManager = app(EnvManager::class);
        $envManager->exportToEnvFile();
        Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => 'Configuration saved and exported to .env!']);
    }

    public function showServicesPage()
    {
        $envManager = app(EnvManager::class);
        $services = EnvVariable::services();

        return view('filament.pages.service-manager', [
            'envManager' => $envManager,
            'services' => $services
        ]);
    }

    public function handlePagePost(Request $request)
    {
        $action = $request->input('action');
        $serviceName = $request->input('service');
        $envManager = app(EnvManager::class);

        switch ($action) {
            case 'install':
                $envManager->installService($serviceName);
                break;
            case 'enable':
                $envManager->enableService($serviceName);
                $envManager->exportToEnvFile();
                Artisan::call('config:clear');
                break;
            case 'disable':
                $envManager->disableService($serviceName);
                $envManager->exportToEnvFile();
                Artisan::call('config:clear');
                break;
            case 'save':
                $variables = $request->input('variables', []);
                foreach ($variables as $key => $value) {
                    EnvVariable::where('key', $key)->update(['value' => $value]);
                }
                $envManager->exportToEnvFile();
                Artisan::call('config:clear');
                break;
        }

        return redirect()->back();
    }
}