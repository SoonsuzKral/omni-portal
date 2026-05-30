<?php
use App\Models\EnvVariable;
use App\Services\EnvManager;

$services = EnvVariable::services();
$envManager = app(EnvManager::class);
?>

<div class="p-4">
    <div class="mb-4 flex justify-between items-center">
        <p class="text-sm text-gray-400">Click "Install" to add service variables, then configure and enable.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($services as $key => $service)
            <?php $status = $envManager->getServiceStatus($key); ?>
            <div class="border rounded-lg p-4 hover:shadow-md transition bg-gray-800/50 border-gray-700">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ $service['icon'] }}</span>
                        <div>
                            <h3 class="font-semibold text-white">{{ $service['name'] }}</h3>
                            <p class="text-xs text-gray-400">{{ $service['description'] }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 text-right">
                        @if($status['total'] == 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-500/20 text-gray-400">Not Installed</span>
                        @elseif($status['complete'])
                            <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400">✓ Ready</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400">{{ $status['filled'] }}/{{ $status['total'] }}</span>
                        @endif
                        @if($status['enabled'])
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-500/20 text-blue-400">● Active</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    @if($status['total'] == 0)
                        <button onclick="installService('{{ $key }}')"
                                class="px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-500 text-white rounded">
                            + Install
                        </button>
                    @else
                        @if($status['complete'] && !$status['enabled'])
                            <button onclick="enableService('{{ $key }}')"
                                    class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-500 text-white rounded">
                                ✓ Enable
                            </button>
                        @endif
                        @if($status['enabled'])
                            <button onclick="disableService('{{ $key }}')"
                                    class="px-3 py-1.5 text-xs bg-red-600 hover:bg-red-500 text-white rounded">
                                ✕ Disable
                            </button>
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

                <div id="config-{{ $key }}" class="hidden mt-4 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium text-gray-300 mb-2">Configuration Variables</h4>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach($service['variables'] as $varKey => $varConfig)
                            <?php $var = \App\Models\EnvVariable::where('key', $varKey)->first(); ?>
                            <div class="flex flex-col">
                                <label class="text-xs text-gray-400 flex justify-between">
                                    <span>{{ $varConfig['label'] }}</span>
                                    @if(isset($varConfig['help']))
                                        <span class="text-gray-500 text-xs">{{ $varConfig['help'] }}</span>
                                    @endif
                                </label>
                                <input type="text"
                                       id="input-{{ $key }}-{{ $varKey }}"
                                       value="{{ $var?->value ?? '' }}"
                                       placeholder="{{ $varConfig['default'] ?? '' }}"
                                       class="service-input px-2 py-1 text-sm bg-gray-900 border border-gray-700 rounded text-white focus:border-indigo-500 focus:outline-none">
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button onclick="saveServiceConfig('{{ $key }}')"
                                class="flex-1 px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-500 text-white rounded">
                            💾 Save & Export to .env
                        </button>
                    </div>
                </div>

                <div id="help-{{ $key }}" class="hidden mt-4 pt-4 border-t border-gray-700">
                    <h4 class="text-sm font-medium text-gray-300 mb-2">📖 Setup Instructions</h4>
                    <div class="text-xs text-gray-400 space-y-2">
                        @switch($key)
                            @case('cloudflare')
                                <p>1. <strong>Cloudflare Dashboard</strong> → Profile → API Tokens</p>
                                <p>2. Create token with <strong>Zone:Read, Zone:Edit</strong> permissions</p>
                                <p>3. Copy token to <code>CF_API_KEY</code></p>
                                @break
                            @case('elasticsearch')
                                <p>1. Install <strong>Elasticsearch</strong> or use Elastic Cloud</p>
                                <p>2. Set <code>ELASTICSEARCH_HOST</code> to your server URL</p>
                                <p>3. Default port: <code>9200</code></p>
                                @break
                            @case('opensearch')
                                <p>1. Create <strong>AWS OpenSearch Service</strong> domain</p>
                                <p>2. Get endpoint URL from AWS console</p>
                                <p>3. Create IAM user with search access</p>
                                @break
                            @case('aws')
                                <p>1. <strong>AWS IAM Console</strong> → Create User</p>
                                <p>2. Attach policy: <strong>AmazonS3FullAccess</strong></p>
                                <p>3. Copy Access Key ID and Secret</p>
                                @break
                            @case('google_adsense')
                                <p>1. <strong>AdSense Dashboard</strong></p>
                                <p>2. Get your <strong>Publisher ID</strong> (ca-pub-xxx)</p>
                                <p>3. Create ad units in AdSense dashboard</p>
                                @break
                            @case('google_analytics')
                                <p>1. <strong>analytics.google.com</strong></p>
                                <p>2. Create <strong>GA4 property</strong></p>
                                <p>3. Copy <strong>Measurement ID</strong> (G-XXXXXX)</p>
                                @break
                            @case('redis')
                                <p>1. Install <strong>Redis server</strong> or use cloud service</p>
                                <p>2. Update <code>REDIS_HOST</code> and <code>REDIS_PORT</code></p>
                                <p>3. Set password if required</p>
                                @break
                            @case('mailgun')
                                <p>1. Sign up at <strong>mailgun.com</strong></p>
                                <p>2. Add your domain in Mailgun</p>
                                <p>3. Copy API key from Settings → API Keys</p>
                                @break
                            @case('sendgrid')
                                <p>1. Sign up at <strong>sendgrid.com</strong></p>
                                <p>2. Create API Key with <strong>Mail Send</strong> permissions</p>
                                <p>3. Verify sender email</p>
                                @break
                            @case('sentry')
                                <p>1. <strong>sentry.io</strong> → Create Project</p>
                                <p>2. Copy DSN from project settings</p>
                                <p>3. Add DSN to <code>SENTRY_DSN</code></p>
                                @break
                            @case('queue')
                                <p>1. Ensure <strong>Redis</strong> is installed</p>
                                <p>2. Set <code>QUEUE_CONNECTION=redis</code></p>
                                <p>3. Run <code>php artisan queue:work</code></p>
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

function installService(serviceName) {
    fetch('/admin/services/install/' + serviceName, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.message) {
            location.reload();
        } else {
            alert('Error: Service not found');
        }
    })
    .catch(() => alert('Error installing service'));
}

function enableService(serviceName) {
    fetch('/admin/services/enable/' + serviceName, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    })
    .catch(() => alert('Error enabling service'));
}

function disableService(serviceName) {
    fetch('/admin/services/disable/' + serviceName, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    })
    .catch(() => alert('Error disabling service'));
}

function saveServiceConfig(serviceName) {
    const inputs = document.querySelectorAll('#config-' + serviceName + ' .service-input');
    const data = {};

    inputs.forEach(input => {
        const key = input.id.replace('input-' + serviceName + '-', '');
        data[key] = input.value;
    });

    fetch('/admin/services/save-service', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            service: serviceName,
            variables: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Configuration saved and exported to .env!');
            location.reload();
        }
    })
    .catch(() => alert('Error saving configuration'));
}
</script>