<?php

namespace App\Services;

use App\Models\EnvVariable;
use Illuminate\Support\Facades\File;

class EnvManager
{
    private string $envPath;

    public function __construct()
    {
        $this->envPath = base_path('.env');
    }

    public function loadFromEnvFile(): int
    {
        if (!File::exists($this->envPath)) {
            return 0;
        }

        $content = File::get($this->envPath);
        $lines = explode("\n", $content);
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
                $key = trim($key);
                $value = trim($value);

                $category = $this->detectCategory($key);
                $isSystem = in_array($key, $this->systemKeys());

                EnvVariable::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'category' => $category,
                        'is_system' => $isSystem,
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    public function exportToEnvFile(): bool
    {
        $variables = EnvVariable::all();
        $lines = [];

        $systemKeys = $this->systemKeys();
        $generalKeys = [];
        $categorized = [];

        foreach ($variables as $var) {
            if (in_array($var->key, $systemKeys)) {
                $generalKeys[] = $var;
            } else {
                $categorized[$var->category][] = $var;
            }
        }

        foreach ($generalKeys as $var) {
            $lines[] = "{$var->key}={$var->value}";
        }

        foreach ($categorized as $category => $vars) {
            if (!empty($lines)) {
                $lines[] = "";
            }
            $lines[] = "# === " . strtoupper($category) . " ===";

            foreach ($vars as $var) {
                $value = $var->value ?? '';
                if (str_contains($value, ' ') || str_contains($value, '"')) {
                    $value = '"' . str_replace('"', '\\"', $value) . '"';
                }
                $lines[] = "{$var->key}={$value}";
            }
        }

        File::put($this->envPath, implode("\n", $lines) . "\n");

        return true;
    }

    public function updateVariable(string $key, string $value): bool
    {
        $var = EnvVariable::where('key', $key)->first();
        if ($var) {
            $var->value = $value;
            $var->save();
        }
        return $this->exportToEnvFile();
    }

    public function addVariable(string $key, string $value, string $category = 'general'): bool
    {
        EnvVariable::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'category' => $category, 'is_system' => false]
        );
        return $this->exportToEnvFile();
    }

    public function deleteVariable(string $key): bool
    {
        EnvVariable::where('key', $key)->delete();
        return $this->exportToEnvFile();
    }

    private function detectCategory(string $key): string
    {
        $keyLower = strtolower($key);

        if (str_starts_with($keyLower, 'db_')) return 'database';
        if (str_starts_with($keyLower, 'redis_')) return 'cache';
        if (str_starts_with($keyLower, 'cache_')) return 'cache';
        if (str_starts_with($keyLower, 'queue_')) return 'queue';
        if (str_starts_with($keyLower, 'mail_')) return 'mail';
        if (str_starts_with($keyLower, 'aws_')) return 'storage';
        if (str_starts_with($keyLower, 's3_')) return 'storage';
        if (str_starts_with($keyLower, 'elasticsearch') || str_starts_with($keyLower, 'opensearch')) return 'elasticsearch';
        if (str_starts_with($keyLower, 'cloudflare') || str_starts_with($keyLower, 'cf_')) return 'cloudflare';
        if (str_starts_with($keyLower, 'adsense') || str_starts_with($keyLower, 'ad_')) return 'adsense';
        if (str_starts_with($keyLower, 'google_') && str_contains($keyLower, 'analytics')) return 'analytics';

        return 'general';
    }

    private function systemKeys(): array
    {
        return [
            'APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL',
            'APP_LOCALE', 'APP_FALLBACK_LOCALE', 'APP_FAKER_LOCALE',
            'APP_MAINTENANCE_DRIVER', 'BCRYPT_ROUNDS',
            'LOG_CHANNEL', 'LOG_STACK', 'LOG_DEPRECATIONS_CHANNEL', 'LOG_LEVEL',
            'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
            'SESSION_DRIVER', 'SESSION_LIFETIME', 'SESSION_ENCRYPT', 'SESSION_PATH', 'SESSION_DOMAIN',
            'BROADCAST_CONNECTION', 'FILESYSTEM_DISK', 'QUEUE_CONNECTION',
            'CACHE_STORE', 'MEMCACHED_HOST',
            'REDIS_CLIENT', 'REDIS_HOST', 'REDIS_PASSWORD', 'REDIS_PORT',
        ];
    }

    public function installService(string $serviceName): bool
    {
        $services = EnvVariable::services();
        if (!isset($services[$serviceName])) {
            return false;
        }

        $service = $services[$serviceName];
        $category = $this->getCategoryForService($serviceName);

        foreach ($service['variables'] as $key => $config) {
            $default = $config['default'] ?? '';
            $description = $config['help'] ?? '';

            EnvVariable::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $default,
                    'category' => $category,
                    'description' => $description,
                    'service_name' => $serviceName,
                    'is_service_enabled' => false,
                    'is_system' => false,
                ]
            );
        }

        return true;
    }

    public function enableService(string $serviceName): bool
    {
        EnvVariable::where('service_name', $serviceName)->update(['is_service_enabled' => true]);
        
        $service = EnvVariable::getServiceInfo($serviceName);
        if (!$service) return false;

        $config = config('services.' . $serviceName, []);
        
        foreach ($service['variables'] as $key => $conf) {
            $var = EnvVariable::where('key', $key)->first();
            if ($var && !empty($var->value)) {
                $config[$key] = $var->value;
            }
        }
        
        config(['services.' . $serviceName => $config]);
        return true;
    }

    public function disableService(string $serviceName): bool
    {
        EnvVariable::where('service_name', $serviceName)->update(['is_service_enabled' => false]);
        return true;
    }

    public function getEnabledServices(): array
    {
        return EnvVariable::where('is_service_enabled', true)
            ->distinct()
            ->pluck('service_name')
            ->toArray();
    }

    public function getServiceStatus(string $serviceName): array
    {
        $total = EnvVariable::where('service_name', $serviceName)->count();
        $filled = EnvVariable::where('service_name', $serviceName)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();
        $enabled = EnvVariable::where('service_name', $serviceName)
            ->where('is_service_enabled', true)
            ->exists();

        return [
            'total' => $total,
            'filled' => $filled,
            'complete' => $total > 0 && $filled >= $total,
            'enabled' => $enabled,
        ];
    }

    private function getCategoryForService(string $serviceName): string
    {
        $map = [
            'cloudflare' => 'cloudflare',
            'elasticsearch' => 'elasticsearch',
            'opensearch' => 'elasticsearch',
            'aws' => 'storage',
            'google_adsense' => 'adsense',
            'google_analytics' => 'analytics',
            'redis' => 'cache',
            'mailgun' => 'mail',
            'sendgrid' => 'mail',
            'sentry' => 'api',
            'queue' => 'queue',
        ];

        return $map[$serviceName] ?? 'general';
    }
}