<?php

namespace App\Console\Commands;

use App\Models\EnvVariable;
use Illuminate\Console\Command;

class InstallAdSense extends Command
{
    protected $signature = 'install:adsense';
    protected $description = 'Install Google AdSense service variables';

    public function handle()
    {
        $services = EnvVariable::services();
        $adsense = $services['google_adsense'] ?? null;

        if (!$adsense) {
            $this->error('AdSense service not found in services config');
            return 1;
        }

        foreach ($adsense['variables'] as $key => $config) {
            EnvVariable::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $config['default'] ?? '',
                    'category' => 'adsense',
                    'description' => $config['help'] ?? '',
                    'service_name' => 'google_adsense',
                    'is_service_enabled' => false,
                    'is_system' => false,
                ]
            );
            $this->info("Created: $key");
        }

        $this->info('AdSense service installed successfully!');
        return 0;
    }
}