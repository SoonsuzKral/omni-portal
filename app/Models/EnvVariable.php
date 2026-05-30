<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvVariable extends Model
{
    protected $fillable = ['key', 'value', 'category', 'is_encrypted', 'description', 'is_system', 'service_name', 'is_service_enabled', 'service_help'];

    protected $hidden = ['is_encrypted'];

    public static function categories(): array
    {
        return [
            'general' => 'General',
            'database' => 'Database',
            'cache' => 'Cache & Redis',
            'queue' => 'Queue & Jobs',
            'mail' => 'Mail & SMTP',
            'storage' => 'Storage & AWS',
            'api' => 'External APIs',
            'elasticsearch' => 'Elasticsearch/OpenSearch',
            'cloudflare' => 'Cloudflare',
            'adsense' => 'AdSense & Ads',
            'analytics' => 'Analytics',
            'security' => 'Security',
        ];
    }

    public static function services(): array
    {
        return [
            'cloudflare' => [
                'name' => 'Cloudflare',
                'icon' => '☁️',
                'description' => 'CDN, DDoS koruması ve web güvenlik duvarı',
                'color' => 'orange',
                'variables' => [
                    'CF_API_KEY' => ['label' => 'API Key', 'type' => 'password', 'help' => 'Cloudflare dashboard > Profile > API Tokens'],
                    'CF_API_EMAIL' => ['label' => 'API Email', 'type' => 'text', 'help' => 'Cloudflare hesap email adresi'],
                    'CF_ZONE_ID' => ['label' => 'Zone ID', 'type' => 'text', 'help' => 'Domain için Zone ID (Cloudflare Overview sayfasında)'],
                    'CF_ACCOUNT_ID' => ['label' => 'Account ID', 'type' => 'text', 'help' => 'Cloudflare account ID'],
                ],
            ],
            'elasticsearch' => [
                'name' => 'Elasticsearch/OpenSearch',
                'icon' => '🔍',
                'description' => 'Gelişmiş tam metin arama ve analitik motoru',
                'color' => 'purple',
                'variables' => [
                    'ELASTICSEARCH_HOST' => ['label' => 'Host URL', 'type' => 'text', 'help' => 'örn: http://localhost:9200'],
                    'ELASTICSEARCH_PORT' => ['label' => 'Port', 'type' => 'text', 'default' => '9200'],
                    'ELASTICSEARCH_INDEX' => ['label' => 'Index Name', 'type' => 'text', 'default' => 'omni_portal'],
                    'ELASTICSEARCH_USERNAME' => ['label' => 'Username', 'type' => 'text', 'help' => 'Optional - elastic'],
                    'ELASTICSEARCH_PASSWORD' => ['label' => 'Password', 'type' => 'password', 'help' => 'Optional'],
                    'ELASTICSEARCH_SSL_VERIFY' => ['label' => 'SSL Verify', 'type' => 'select', 'options' => ['true' => 'Enable', 'false' => 'Disable']],
                ],
            ],
            'opensearch' => [
                'name' => 'OpenSearch',
                'icon' => '🔎',
                'description' => 'AWS OpenSearch Service entegrasyonu',
                'color' => 'blue',
                'variables' => [
                    'OPENSEARCH_ENDPOINT' => ['label' => 'Endpoint URL', 'type' => 'text', 'help' => 'örn: https://search-xxx.us-east-1.es.amazonaws.com'],
                    'OPENSEARCH_REGION' => ['label' => 'Region', 'type' => 'text', 'default' => 'us-east-1'],
                    'OPENSEARCH_INDEX' => ['label' => 'Index Name', 'type' => 'text', 'default' => 'omni_portal'],
                    'OPENSEARCH_ACCESS_KEY' => ['label' => 'Access Key', 'type' => 'password'],
                    'OPENSEARCH_SECRET_KEY' => ['label' => 'Secret Key', 'type' => 'password'],
                ],
            ],
            'aws' => [
                'name' => 'AWS S3 Storage',
                'icon' => '📦',
                'description' => 'AWS S3 bucket storage entegrasyonu',
                'color' => 'yellow',
                'variables' => [
                    'AWS_ACCESS_KEY_ID' => ['label' => 'Access Key ID', 'type' => 'text'],
                    'AWS_SECRET_ACCESS_KEY' => ['label' => 'Secret Access Key', 'type' => 'password'],
                    'AWS_DEFAULT_REGION' => ['label' => 'Default Region', 'type' => 'text', 'default' => 'us-east-1'],
                    'AWS_BUCKET' => ['label' => 'Bucket Name', 'type' => 'text'],
                    'AWS_USE_PATH_STYLE_ENDPOINT' => ['label' => 'Path Style', 'type' => 'select', 'options' => ['false' => 'Virtual Hosted', 'true' => 'Path Style']],
                ],
            ],
            'google_adsense' => [
                'name' => 'Google AdSense',
                'icon' => '💰',
                'description' => 'Google AdSense reklam entegrasyonu',
                'color' => 'green',
                'variables' => [
                    'ADSENSE_PUBLISHER_ID' => ['label' => 'Publisher ID', 'type' => 'text', 'help' => 'örn: ca-pub-xxxxxxxxxxxxx'],
                    'ADSENSE_AD_CLIENT' => ['label' => 'Ad Client', 'type' => 'text', 'default' => 'ca-pub-xxxx'],
                    'ADSENSE_AD_SLOT_HEADER' => ['label' => 'Header Ad Slot', 'type' => 'text', 'help' => 'Sayfa üstü reklam'],
                    'ADSENSE_AD_SLOT_SIDEBAR' => ['label' => 'Sidebar Ad Slot', 'type' => 'text', 'help' => 'Yan panel reklamı'],
                    'ADSENSE_AD_SLOT_INARTICLE' => ['label' => 'In-Article Ad Slot', 'type' => 'text', 'help' => 'İçerik arası reklam'],
                    'ADSENSE_AD_SLOT_FOOTER' => ['label' => 'Footer Ad Slot', 'type' => 'text', 'help' => 'Sayfa altı reklam'],
                    'ADSENSE_ENABLED' => ['label' => 'Enable Ads', 'type' => 'select', 'options' => ['true' => 'Yes', 'false' => 'No']],
                ],
            ],
            'google_analytics' => [
                'name' => 'Google Analytics 4',
                'icon' => '📊',
                'description' => 'Google Analytics 4 entegrasyonu',
                'color' => 'blue',
                'variables' => [
                    'GA4_MEASUREMENT_ID' => ['label' => 'Measurement ID', 'type' => 'text', 'help' => 'G-XXXXXXXXXX'],
                    'GA4_API_SECRET' => ['label' => 'API Secret', 'type' => 'password', 'help' => 'Google Analytics > Admin > Data Streams > Measurement Protocol'],
                    'GA4_PROPERTY_ID' => ['label' => 'Property ID', 'type' => 'text', 'help' => 'Analytics property ID'],
                ],
            ],
            'redis' => [
                'name' => 'Redis Cache',
                'icon' => '⚡',
                'description' => 'Redis cache ve session storage',
                'color' => 'red',
                'variables' => [
                    'REDIS_CLIENT' => ['label' => 'Client', 'type' => 'select', 'options' => ['phpredis' => 'PHP Redis', 'predis' => 'Predis'], 'default' => 'phpredis'],
                    'REDIS_HOST' => ['label' => 'Host', 'type' => 'text', 'default' => '127.0.0.1'],
                    'REDIS_PORT' => ['label' => 'Port', 'type' => 'text', 'default' => '6379'],
                    'REDIS_PASSWORD' => ['label' => 'Password', 'type' => 'password', 'nullable' => true],
                    'REDIS_DATABASE' => ['label' => 'Database', 'type' => 'text', 'default' => '0'],
                ],
            ],
            'mailgun' => [
                'name' => 'Mailgun',
                'icon' => '📧',
                'description' => 'Mailgun SMTP email servisi',
                'color' => 'gray',
                'variables' => [
                    'MAILGUN_DOMAIN' => ['label' => 'Domain', 'type' => 'text', 'help' => 'Mailgun domain adı'],
                    'MAILGUN_API_KEY' => ['label' => 'API Key', 'type' => 'password', 'help' => 'Mailgun > Settings > API Keys'],
                    'MAILGUN_ENDPOINT' => ['label' => 'Endpoint', 'type' => 'text', 'default' => 'api.mailgun.net'],
                ],
            ],
            'sendgrid' => [
                'name' => 'SendGrid',
                'icon' => '✉️',
                'description' => 'SendGrid email servisi',
                'color' => 'blue',
                'variables' => [
                    'SENDGRID_API_KEY' => ['label' => 'API Key', 'type' => 'password'],
                    'SENDGRID_FROM_EMAIL' => ['label' => 'From Email', 'type' => 'text', 'help' => 'Verified sender email'],
                    'SENDGRID_FROM_NAME' => ['label' => 'From Name', 'type' => 'text'],
                ],
            ],
            'sentry' => [
                'name' => 'Sentry',
                'icon' => '🐛',
                'description' => 'Error tracking ve performance monitoring',
                'color' => 'purple',
                'variables' => [
                    'SENTRY_DSN' => ['label' => 'DSN', 'type' => 'text', 'help' => 'Sentry Project Settings > Client Keys'],
                    'SENTRY_TRACES_SAMPLE_RATE' => ['label' => 'Traces Sample Rate', 'type' => 'text', 'default' => '0.1'],
                ],
            ],
            'queue' => [
                'name' => 'Queue (Redis)',
                'icon' => '📫',
                'description' => 'Redis-backed queue worker',
                'color' => 'indigo',
                'variables' => [
                    'QUEUE_CONNECTION' => ['label' => 'Connection', 'type' => 'select', 'options' => ['redis' => 'Redis', 'database' => 'Database', 'sync' => 'Sync'], 'default' => 'redis'],
                    'QUEUE_NAME' => ['label' => 'Queue Name', 'type' => 'text', 'default' => 'default'],
                    'REDIS_QUEUE_PREFIX' => ['label' => 'Queue Prefix', 'type' => 'text', 'default' => 'omni_queue'],
                ],
            ],
        ];
    }

    public static function getServiceInfo(string $service): ?array
    {
        return self::services()[$service] ?? null;
    }
}