<?php

use App\Models\EnvVariable;
use App\Services\EnvManager;

if (!isset($envManager)) {
    $envManager = app(EnvManager::class);
}
if (!isset($services)) {
    $services = EnvVariable::services();
}
?>

<div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">🛠️ Service Installer</h2>
                <p class="text-gray-400 text-sm mt-1">Install and configure third-party services</p>
            </div>
            <a href="{{ route('filament.admin.resources.env-variables.index') }}"
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm">
                ← Back to Env Manager
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($services as $key => $service)
                <?php $status = $envManager->getServiceStatus($key); ?>
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 hover:border-gray-600 transition">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ $service['icon'] }}</span>
                            <div>
                                <h3 class="font-semibold">{{ $service['name'] }}</h3>
                                <p class="text-xs text-gray-400">{{ $service['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        @if($status['total'] == 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-600 text-gray-300">Not Installed</span>
                        @elseif($status['complete'])
                            <span class="px-2 py-1 text-xs rounded-full bg-green-600 text-green-200">✓ Ready</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-600 text-yellow-200">{{ $status['filled'] }}/{{ $status['total'] }} filled</span>
                        @endif
                        @if($status['enabled'])
                            <span class="ml-1 px-2 py-1 text-xs rounded-full bg-blue-600 text-blue-200">● Active</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($status['total'] == 0)
                            <form method="POST" action="/admin/env-variables/services">
                                @csrf
                                <input type="hidden" name="action" value="install">
                                <input type="hidden" name="service" value="{{ $key }}">
                                <button type="submit" class="px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-500 text-white rounded">
                                    + Install
                                </button>
                            </form>
                        @else
                            @if($status['complete'] && !$status['enabled'])
                                <form method="POST" action="/admin/env-variables/services">
                                    @csrf
                                    <input type="hidden" name="action" value="enable">
                                    <input type="hidden" name="service" value="{{ $key }}">
                                    <button type="submit" class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-500 text-white rounded">
                                        ✓ Enable
                                    </button>
                                </form>
                            @endif
                            @if($status['enabled'])
                                <form method="POST" action="/admin/env-variables/services">
                                    @csrf
                                    <input type="hidden" name="action" value="disable">
                                    <input type="hidden" name="service" value="{{ $key }}">
                                    <button type="submit" class="px-3 py-1.5 text-xs bg-red-600 hover:bg-red-500 text-white rounded">
                                        ✕ Disable
                                    </button>
                                </form>
                            @endif
                            <button onclick="toggleConfig('{{ $key }}')"
                                    class="px-3 py-1.5 text-xs bg-gray-600 hover:bg-gray-500 text-white rounded">
                                ⚙️ Configure
                            </button>
                        @endif
                        <button onclick="toggleHelp('{{ $key }}')"
                                class="px-3 py-1.5 text-xs bg-gray-700 hover:bg-gray-600 text-gray-300 rounded">
                            📖 Help
                        </button>
                    </div>

                    <div id="config-{{ $key }}" class="hidden mt-4 pt-4 border-t border-gray-600">
                        <h4 class="text-sm font-medium text-gray-300 mb-3">Configuration</h4>
                        <form method="POST" action="/admin/env-variables/services">
                            @csrf
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="service" value="{{ $key }}">
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($service['variables'] as $varKey => $varConfig)
                                    <?php $var = \App\Models\EnvVariable::where('key', $varKey)->first(); ?>
                                    <div>
                                        <label class="text-xs text-gray-400 block mb-1">
                                            {{ $varConfig['label'] }}
                                        </label>
                                        <input type="text"
                                               name="variables[{{ $varKey }}]"
                                               value="{{ $var?->value ?? '' }}"
                                               placeholder="{{ $varConfig['default'] ?? '' }}"
                                               class="w-full px-2 py-1.5 text-sm bg-gray-900 border border-gray-600 rounded text-white">
                                    </div>
                                @endforeach
                            </div>
                            <button type="submit"
                                    class="mt-3 w-full px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-500 text-white rounded">
                                💾 Save & Export to .env
                            </button>
                        </form>
                    </div>

                    <div id="help-{{ $key }}" class="hidden mt-4 pt-4 border-t border-gray-600">
                        <h4 class="text-sm font-medium text-gray-300 mb-2">📖 Setup Instructions</h4>
                        <div class="text-xs text-gray-400 space-y-1.5">
                            @switch($key)
                                @case('cloudflare')
                                    <p>1. Cloudflare → Profile → API Tokens</p>
                                    <p>2. Create token: Zone:Read, Zone:Edit</p>
                                    <p>3. Copy token → CF_API_KEY</p>
                                @break
                                @case('elasticsearch')
                                    <p>1. Install Elasticsearch or use Elastic Cloud</p>
                                    <p>2. Set ELASTICSEARCH_HOST (default: 9200)</p>
                                @break
                                @case('aws')
                                    <p>1. AWS IAM → Create User</p>
                                    <p>2. Policy: AmazonS3FullAccess</p>
                                    <p>3. Copy Access Key & Secret</p>
                                @break
                                @case('google_adsense')
                                    <p>1. AdSense Dashboard</p>
                                    <p>2. Get Publisher ID (ca-pub-xxx)</p>
                                @break
                                @case('google_analytics')
                                    <p>1. analytics.google.com</p>
                                    <p>2. Create GA4 property</p>
                                    <p>3. Copy Measurement ID (G-XXX)</p>
                                @break
                                @case('redis')
                                    <p>1. Install Redis or use cloud</p>
                                    <p>2. Set REDIS_HOST, REDIS_PORT</p>
                                @break
                                @case('mailgun')
                                    <p>1. mailgun.com → Add domain</p>
                                    <p>2. Settings → API Keys</p>
                                @break
                                @case('sendgrid')
                                    <p>1. sendgrid.com</p>
                                    <p>2. API Keys → Create with Mail Send</p>
                                @break
                                @case('sentry')
                                    <p>1. sentry.io → Create Project</p>
                                    <p>2. Copy DSN → SENTRY_DSN</p>
                                @break
                                @case('queue')
                                    <p>1. Install Redis</p>
                                    <p>2. Set QUEUE_CONNECTION=redis</p>
                                    <p>3. Run: php artisan queue:work</p>
                                @break
                            @endswitch
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

<script>
function toggleConfig(key) {
    document.getElementById('config-' + key).classList.toggle('hidden');
    document.getElementById('help-' + key).classList.add('hidden');
}

function toggleHelp(key) {
    document.getElementById('help-' + key).classList.toggle('hidden');
    document.getElementById('config-' + key).classList.add('hidden');
}
</script>