<?php

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/admin/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.auth.login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/password-reset/request' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.auth.password-reset.request',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/password-reset/reset' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.auth.password-reset.reset',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.auth.logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.auth.profile',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/ad-settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.ad-settings',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/quality' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.pages.quality',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/content-nodes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.content-nodes.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/content-nodes/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.content-nodes.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/env-variables' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.env-variables.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/env-variables/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.env-variables.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/global-ad-blocks' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.global-ad-blocks.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/global-ad-blocks/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.global-ad-blocks.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/keywords' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.keywords.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/keywords/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.keywords.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/live-data-vaults' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.live-data-vaults.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/live-data-vaults/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.live-data-vaults.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/locations' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.locations.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/locations/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.locations.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/post-templates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.post-templates.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/post-templates/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.post-templates.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/taxonomies' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.taxonomies.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/taxonomies/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.taxonomies.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sanctum/csrf-cookie' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sanctum.csrf-cookie',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'default.livewire.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/livewire.min.js' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::NyYzMG2RPItYQLBT',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/livewire.min.js.map' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::oW34ESNnUj0c9aJr',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/livewire/upload-file' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'livewire.upload-file',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/health' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::YmlUCk9qet0BXJq0',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/ingest/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::FOIFFWRtOUqom2F9',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::npwzZ3PQsn6JdvdQ',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/search/suggestions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::GoQMK9NdOUZci1Tv',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/token' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::TkUFZ8UdZj5m8a5j',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/ingest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::z7ezkYimRMdzxOnv',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::s4xko4CIVWBGNhuH',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/revoke' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::cCl0ImtHiDnsQrfA',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/auth/revoke-all' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::XZAIbBZeg8aCGzhV',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/taxonomies' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::0m5ofg8t95LeBbQm',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mGq95LQNPvObWbXw',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/locations' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::p7AmMD15oeV13faI',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::eeo07uUC7D4fWbS1',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/content-nodes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::wfZZQak6GF5TxYOw',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/keywords' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UVkPq145rXKsNUSb',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::LAof46ciJdcoBmXt',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/live-data' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::QnuF4MSn2ThnynPV',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::j4NMBJqNtooUwOAd',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/post-templates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::tK6U0zlj6V1m85kT',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::luV79NH6xRGv89Si',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/orchestra/keywords' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kGAk6HBew86IT8IP',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/orchestra/sync-countries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::iC2GAq388JC00eAf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/orchestra/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Awi3xwVsbaSqyC0f',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::C5Mot6DL03AG3gHg',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/robots.txt' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Qxh6MSmDzeHmTvid',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/ads.txt' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Og2SumX1Vps918Ps',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sellers.json' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Jch6UDREx1TFDIi8',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/services/save-service' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::oLFkvtGbugIgjFyg',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/env-variables/services' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::v407FndHIIGvXQpt',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Jq70MqB6B36mTSIh',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/rss.xml' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'rss',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'home',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/adblock/debug' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::tfjhVs88WHRkCCmW',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'search',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/search/suggest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'search.suggest',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/keywords/trending' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'keywords.trending',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/keywords/suggest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'keywords.suggest',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/keywords/generate' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'keywords.generate',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/keywords/auto-create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'keywords.auto-create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/privacy-policy' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'privacy-policy',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/terms' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'terms',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/about' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'about',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/contact' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'contact',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sitemap.xml' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sitemap',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sitemap-categories.xml' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xIfVEU7XS3HMpg97',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sitemap-locations.xml' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::R8AizmcFnanMyXnM',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'taxonomy.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/categories/tree' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'taxonomy.tree',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/categories/api' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'taxonomy.api',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/categories/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'taxonomy.search',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/locations' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'location.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/locations/tree' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'location.tree',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/locations/api' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'location.api',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/locations/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'location.search',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/filament/(?|exports/([^/]++)/download(*:45)|imports/([^/]++)/failed\\-rows/download(*:90))|/a(?|dmin/(?|content\\-nodes/([^/]++)/edit(*:139)|env\\-variables/([^/]++)/edit(*:175)|global\\-ad\\-blocks/([^/]++)/edit(*:215)|keywords/([^/]++)/edit(*:245)|l(?|ive\\-data\\-vaults/([^/]++)/edit(*:288)|ocations/([^/]++)/edit(*:318))|post\\-templates/([^/]++)/edit(*:356)|taxonomies/([^/]++)/edit(*:388)|services/(?|install/([^/]++)(*:424)|enable/([^/]++)(*:447)|disable/([^/]++)(*:471)))|pi/v1/(?|taxonomies/([^/]++)(?|(*:512))|locations/([^/]++)(?|(*:542))|content\\-nodes/([^/]++)(?|(*:577))|keywords/([^/]++)(?|(*:606))))|/l(?|ivewire/preview\\-file/([^/]++)(*:652)|ang/([^/]++)(*:672)|ocation/([^/]++)(?|(*:699)|/([^/]++)(*:716)))|/sitemap\\-content\\-([0-9]+)\\.xml(*:758)|/p/([^/]+)(*:776)|/([^/]+)/([^/]+)(*:800)|/([^/]+)(*:816)|/([^/]+)/([^/]+)/([^/]+)(*:848)|/storage/(.*)(?|(*:872)))/?$}sDu',
    ),
    3 => 
    array (
      45 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.exports.download',
          ),
          1 => 
          array (
            0 => 'export',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      90 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.imports.failed-rows.download',
          ),
          1 => 
          array (
            0 => 'import',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      139 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.content-nodes.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      175 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.env-variables.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      215 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.global-ad-blocks.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      245 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.keywords.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      288 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.live-data-vaults.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      318 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.locations.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      356 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.post-templates.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      388 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'filament.admin.resources.taxonomies.edit',
          ),
          1 => 
          array (
            0 => 'record',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      424 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mQ3ZNkmlOwkZZKED',
          ),
          1 => 
          array (
            0 => 'service',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      447 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::tjOIvFO7T2h0pQ4J',
          ),
          1 => 
          array (
            0 => 'service',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      471 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2Z70S3fkbJbRCdnE',
          ),
          1 => 
          array (
            0 => 'service',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      512 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::U6LoYWJwPhW9Il2a',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Gm7DvMEAvlmib799',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::uK4RhSTrVWu0d0fd',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      542 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ahN03Nc8iciO37aZ',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kCuR1mlZHy74WuPt',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::TEfjwDCfmAQIORxI',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      577 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::aP3v50j5bZcar19y',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::eJUHvAnVZawpbiGj',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::zXLshfSotvmbBoH2',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      606 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::fRQlcboQSiUWtti8',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::uORaRw85UGMSF073',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::LcFyjSwm9n01QCwF',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      652 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'livewire.preview-file',
          ),
          1 => 
          array (
            0 => 'filename',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      672 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'locale.switch',
          ),
          1 => 
          array (
            0 => 'locale',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      699 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'location.show',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      716 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'location.district',
          ),
          1 => 
          array (
            0 => 'citySlug',
            1 => 'districtSlug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      758 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::GgvqGwCYaCSG3sWc',
          ),
          1 => 
          array (
            0 => 'page',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      776 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'content.by-slug',
          ),
          1 => 
          array (
            0 => 'slug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      800 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'taxonomy.location',
          ),
          1 => 
          array (
            0 => 'category',
            1 => 'locationSlug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      816 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'taxonomy.category',
          ),
          1 => 
          array (
            0 => 'category',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      848 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'content.show',
          ),
          1 => 
          array (
            0 => 'category',
            1 => 'locationSlug',
            2 => 'slug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      872 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local.upload',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'filament.exports.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'filament/exports/{export}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'filament.actions',
        ),
        'uses' => 'Filament\\Actions\\Exports\\Http\\Controllers\\DownloadExport@__invoke',
        'controller' => 'Filament\\Actions\\Exports\\Http\\Controllers\\DownloadExport',
        'as' => 'filament.exports.download',
        'namespace' => NULL,
        'prefix' => 'filament',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.imports.failed-rows.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'filament/imports/{import}/failed-rows/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'filament.actions',
        ),
        'uses' => 'Filament\\Actions\\Imports\\Http\\Controllers\\DownloadImportFailureCsv@__invoke',
        'controller' => 'Filament\\Actions\\Imports\\Http\\Controllers\\DownloadImportFailureCsv',
        'as' => 'filament.imports.failed-rows.download',
        'namespace' => NULL,
        'prefix' => 'filament',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.auth.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/login',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
        ),
        'uses' => 'Filament\\Pages\\Auth\\Login@__invoke',
        'controller' => 'Filament\\Pages\\Auth\\Login',
        'as' => 'filament.admin.auth.login',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.auth.password-reset.request' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/password-reset/request',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
        ),
        'uses' => 'Filament\\Pages\\Auth\\PasswordReset\\RequestPasswordReset@__invoke',
        'controller' => 'Filament\\Pages\\Auth\\PasswordReset\\RequestPasswordReset',
        'as' => 'filament.admin.auth.password-reset.request',
        'namespace' => NULL,
        'prefix' => 'admin/password-reset',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.auth.password-reset.reset' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/password-reset/reset',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'signed',
        ),
        'uses' => 'Filament\\Pages\\Auth\\PasswordReset\\ResetPassword@__invoke',
        'controller' => 'Filament\\Pages\\Auth\\PasswordReset\\ResetPassword',
        'as' => 'filament.admin.auth.password-reset.reset',
        'namespace' => NULL,
        'prefix' => 'admin/password-reset',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.auth.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/logout',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'uses' => 'Filament\\Http\\Controllers\\Auth\\LogoutController@__invoke',
        'controller' => 'Filament\\Http\\Controllers\\Auth\\LogoutController',
        'as' => 'filament.admin.auth.logout',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.auth.profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/profile',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'uses' => 'Filament\\Pages\\Auth\\EditProfile@__invoke',
        'controller' => 'Filament\\Pages\\Auth\\EditProfile',
        'as' => 'filament.admin.auth.profile',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'uses' => 'App\\Filament\\Pages\\Dashboard@__invoke',
        'controller' => 'App\\Filament\\Pages\\Dashboard',
        'as' => 'filament.admin.pages.dashboard',
        'namespace' => NULL,
        'prefix' => 'admin/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.ad-settings' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ad-settings',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'uses' => 'App\\Filament\\Pages\\AdSettings@__invoke',
        'controller' => 'App\\Filament\\Pages\\AdSettings',
        'as' => 'filament.admin.pages.ad-settings',
        'namespace' => NULL,
        'prefix' => 'admin/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.pages.quality' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/quality',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'uses' => 'App\\Filament\\Pages\\QualityDashboard@__invoke',
        'controller' => 'App\\Filament\\Pages\\QualityDashboard',
        'as' => 'filament.admin.pages.quality',
        'namespace' => NULL,
        'prefix' => 'admin/',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.content-nodes.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/content-nodes',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ContentNodeResource\\Pages\\ListContentNodes@__invoke',
        'controller' => 'App\\Filament\\Resources\\ContentNodeResource\\Pages\\ListContentNodes',
        'as' => 'filament.admin.resources.content-nodes.index',
        'namespace' => NULL,
        'prefix' => 'admin/content-nodes',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.content-nodes.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/content-nodes/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ContentNodeResource\\Pages\\CreateContentNode@__invoke',
        'controller' => 'App\\Filament\\Resources\\ContentNodeResource\\Pages\\CreateContentNode',
        'as' => 'filament.admin.resources.content-nodes.create',
        'namespace' => NULL,
        'prefix' => 'admin/content-nodes',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.content-nodes.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/content-nodes/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\ContentNodeResource\\Pages\\EditContentNode@__invoke',
        'controller' => 'App\\Filament\\Resources\\ContentNodeResource\\Pages\\EditContentNode',
        'as' => 'filament.admin.resources.content-nodes.edit',
        'namespace' => NULL,
        'prefix' => 'admin/content-nodes',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.env-variables.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/env-variables',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\EnvVariableResource\\Pages\\ListEnvVariables@__invoke',
        'controller' => 'App\\Filament\\Resources\\EnvVariableResource\\Pages\\ListEnvVariables',
        'as' => 'filament.admin.resources.env-variables.index',
        'namespace' => NULL,
        'prefix' => 'admin/env-variables',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.env-variables.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/env-variables/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\EnvVariableResource\\Pages\\CreateEnvVariable@__invoke',
        'controller' => 'App\\Filament\\Resources\\EnvVariableResource\\Pages\\CreateEnvVariable',
        'as' => 'filament.admin.resources.env-variables.create',
        'namespace' => NULL,
        'prefix' => 'admin/env-variables',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.env-variables.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/env-variables/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\EnvVariableResource\\Pages\\EditEnvVariable@__invoke',
        'controller' => 'App\\Filament\\Resources\\EnvVariableResource\\Pages\\EditEnvVariable',
        'as' => 'filament.admin.resources.env-variables.edit',
        'namespace' => NULL,
        'prefix' => 'admin/env-variables',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.global-ad-blocks.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/global-ad-blocks',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\GlobalAdBlockResource\\Pages\\ListGlobalAdBlocks@__invoke',
        'controller' => 'App\\Filament\\Resources\\GlobalAdBlockResource\\Pages\\ListGlobalAdBlocks',
        'as' => 'filament.admin.resources.global-ad-blocks.index',
        'namespace' => NULL,
        'prefix' => 'admin/global-ad-blocks',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.global-ad-blocks.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/global-ad-blocks/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\GlobalAdBlockResource\\Pages\\CreateGlobalAdBlock@__invoke',
        'controller' => 'App\\Filament\\Resources\\GlobalAdBlockResource\\Pages\\CreateGlobalAdBlock',
        'as' => 'filament.admin.resources.global-ad-blocks.create',
        'namespace' => NULL,
        'prefix' => 'admin/global-ad-blocks',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.global-ad-blocks.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/global-ad-blocks/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\GlobalAdBlockResource\\Pages\\EditGlobalAdBlock@__invoke',
        'controller' => 'App\\Filament\\Resources\\GlobalAdBlockResource\\Pages\\EditGlobalAdBlock',
        'as' => 'filament.admin.resources.global-ad-blocks.edit',
        'namespace' => NULL,
        'prefix' => 'admin/global-ad-blocks',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.keywords.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/keywords',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\KeywordResource\\Pages\\ListKeywords@__invoke',
        'controller' => 'App\\Filament\\Resources\\KeywordResource\\Pages\\ListKeywords',
        'as' => 'filament.admin.resources.keywords.index',
        'namespace' => NULL,
        'prefix' => 'admin/keywords',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.keywords.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/keywords/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\KeywordResource\\Pages\\CreateKeyword@__invoke',
        'controller' => 'App\\Filament\\Resources\\KeywordResource\\Pages\\CreateKeyword',
        'as' => 'filament.admin.resources.keywords.create',
        'namespace' => NULL,
        'prefix' => 'admin/keywords',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.keywords.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/keywords/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\KeywordResource\\Pages\\EditKeyword@__invoke',
        'controller' => 'App\\Filament\\Resources\\KeywordResource\\Pages\\EditKeyword',
        'as' => 'filament.admin.resources.keywords.edit',
        'namespace' => NULL,
        'prefix' => 'admin/keywords',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.live-data-vaults.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/live-data-vaults',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LiveDataVaultResource\\Pages\\ListLiveDataVaults@__invoke',
        'controller' => 'App\\Filament\\Resources\\LiveDataVaultResource\\Pages\\ListLiveDataVaults',
        'as' => 'filament.admin.resources.live-data-vaults.index',
        'namespace' => NULL,
        'prefix' => 'admin/live-data-vaults',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.live-data-vaults.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/live-data-vaults/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LiveDataVaultResource\\Pages\\CreateLiveDataVault@__invoke',
        'controller' => 'App\\Filament\\Resources\\LiveDataVaultResource\\Pages\\CreateLiveDataVault',
        'as' => 'filament.admin.resources.live-data-vaults.create',
        'namespace' => NULL,
        'prefix' => 'admin/live-data-vaults',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.live-data-vaults.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/live-data-vaults/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LiveDataVaultResource\\Pages\\EditLiveDataVault@__invoke',
        'controller' => 'App\\Filament\\Resources\\LiveDataVaultResource\\Pages\\EditLiveDataVault',
        'as' => 'filament.admin.resources.live-data-vaults.edit',
        'namespace' => NULL,
        'prefix' => 'admin/live-data-vaults',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.locations.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/locations',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LocationResource\\Pages\\ListLocations@__invoke',
        'controller' => 'App\\Filament\\Resources\\LocationResource\\Pages\\ListLocations',
        'as' => 'filament.admin.resources.locations.index',
        'namespace' => NULL,
        'prefix' => 'admin/locations',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.locations.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/locations/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LocationResource\\Pages\\CreateLocation@__invoke',
        'controller' => 'App\\Filament\\Resources\\LocationResource\\Pages\\CreateLocation',
        'as' => 'filament.admin.resources.locations.create',
        'namespace' => NULL,
        'prefix' => 'admin/locations',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.locations.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/locations/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\LocationResource\\Pages\\EditLocation@__invoke',
        'controller' => 'App\\Filament\\Resources\\LocationResource\\Pages\\EditLocation',
        'as' => 'filament.admin.resources.locations.edit',
        'namespace' => NULL,
        'prefix' => 'admin/locations',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.post-templates.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/post-templates',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\PostTemplateResource\\Pages\\ListPostTemplates@__invoke',
        'controller' => 'App\\Filament\\Resources\\PostTemplateResource\\Pages\\ListPostTemplates',
        'as' => 'filament.admin.resources.post-templates.index',
        'namespace' => NULL,
        'prefix' => 'admin/post-templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.post-templates.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/post-templates/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\PostTemplateResource\\Pages\\CreatePostTemplate@__invoke',
        'controller' => 'App\\Filament\\Resources\\PostTemplateResource\\Pages\\CreatePostTemplate',
        'as' => 'filament.admin.resources.post-templates.create',
        'namespace' => NULL,
        'prefix' => 'admin/post-templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.post-templates.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/post-templates/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\PostTemplateResource\\Pages\\EditPostTemplate@__invoke',
        'controller' => 'App\\Filament\\Resources\\PostTemplateResource\\Pages\\EditPostTemplate',
        'as' => 'filament.admin.resources.post-templates.edit',
        'namespace' => NULL,
        'prefix' => 'admin/post-templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.taxonomies.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/taxonomies',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\TaxonomyResource\\Pages\\ListTaxonomies@__invoke',
        'controller' => 'App\\Filament\\Resources\\TaxonomyResource\\Pages\\ListTaxonomies',
        'as' => 'filament.admin.resources.taxonomies.index',
        'namespace' => NULL,
        'prefix' => 'admin/taxonomies',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.taxonomies.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/taxonomies/create',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\TaxonomyResource\\Pages\\CreateTaxonomy@__invoke',
        'controller' => 'App\\Filament\\Resources\\TaxonomyResource\\Pages\\CreateTaxonomy',
        'as' => 'filament.admin.resources.taxonomies.create',
        'namespace' => NULL,
        'prefix' => 'admin/taxonomies',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.taxonomies.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/taxonomies/{record}/edit',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\TaxonomyResource\\Pages\\EditTaxonomy@__invoke',
        'controller' => 'App\\Filament\\Resources\\TaxonomyResource\\Pages\\EditTaxonomy',
        'as' => 'filament.admin.resources.taxonomies.edit',
        'namespace' => NULL,
        'prefix' => 'admin/taxonomies',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'filament.admin.resources.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'panel:admin',
          1 => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
          2 => 'Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse',
          3 => 'Illuminate\\Session\\Middleware\\StartSession',
          4 => 'Illuminate\\View\\Middleware\\ShareErrorsFromSession',
          5 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
          6 => 'Illuminate\\Routing\\Middleware\\SubstituteBindings',
          7 => 'Filament\\Http\\Middleware\\DisableBladeIconComponents',
          8 => 'Filament\\Http\\Middleware\\DispatchServingFilamentEvent',
          9 => 'Illuminate\\Auth\\Middleware\\Authenticate',
        ),
        'excluded_middleware' => 
        array (
        ),
        'uses' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers@__invoke',
        'controller' => 'App\\Filament\\Resources\\UserResource\\Pages\\ListUsers',
        'as' => 'filament.admin.resources.users.index',
        'namespace' => NULL,
        'prefix' => 'admin/users',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sanctum.csrf-cookie' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sanctum/csrf-cookie',
      'action' => 
      array (
        'uses' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'controller' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'namespace' => NULL,
        'prefix' => 'sanctum',
        'where' => 
        array (
        ),
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'sanctum.csrf-cookie',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'default.livewire.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'livewire/update',
      'action' => 
      array (
        'uses' => 'Livewire\\Mechanisms\\HandleRequests\\HandleRequests@handleUpdate',
        'controller' => 'Livewire\\Mechanisms\\HandleRequests\\HandleRequests@handleUpdate',
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'default.livewire.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::NyYzMG2RPItYQLBT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livewire/livewire.min.js',
      'action' => 
      array (
        'uses' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@returnJavaScriptAsFile',
        'controller' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@returnJavaScriptAsFile',
        'as' => 'generated::NyYzMG2RPItYQLBT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::oW34ESNnUj0c9aJr' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livewire/livewire.min.js.map',
      'action' => 
      array (
        'uses' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@maps',
        'controller' => 'Livewire\\Mechanisms\\FrontendAssets\\FrontendAssets@maps',
        'as' => 'generated::oW34ESNnUj0c9aJr',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'livewire.upload-file' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'livewire/upload-file',
      'action' => 
      array (
        'uses' => 'Livewire\\Features\\SupportFileUploads\\FileUploadController@handle',
        'controller' => 'Livewire\\Features\\SupportFileUploads\\FileUploadController@handle',
        'as' => 'livewire.upload-file',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'livewire.preview-file' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'livewire/preview-file/{filename}',
      'action' => 
      array (
        'uses' => 'Livewire\\Features\\SupportFileUploads\\FilePreviewController@handle',
        'controller' => 'Livewire\\Features\\SupportFileUploads\\FilePreviewController@handle',
        'as' => 'livewire.preview-file',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::YmlUCk9qet0BXJq0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/health',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\IngestController@health',
        'controller' => 'App\\Http\\Controllers\\Api\\IngestController@health',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::YmlUCk9qet0BXJq0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::FOIFFWRtOUqom2F9' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/ingest/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\IngestController@status',
        'controller' => 'App\\Http\\Controllers\\Api\\IngestController@status',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::FOIFFWRtOUqom2F9',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::npwzZ3PQsn6JdvdQ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@stats',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@stats',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::npwzZ3PQsn6JdvdQ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::GoQMK9NdOUZci1Tv' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/search/suggestions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
        ),
        'uses' => 'App\\Http\\Controllers\\ContentController@suggestions',
        'controller' => 'App\\Http\\Controllers\\ContentController@suggestions',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::GoQMK9NdOUZci1Tv',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::TkUFZ8UdZj5m8a5j' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/auth/token',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'throttle:login',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@token',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@token',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::TkUFZ8UdZj5m8a5j',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::z7ezkYimRMdzxOnv' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/ingest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\IngestController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\IngestController@store',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::z7ezkYimRMdzxOnv',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::s4xko4CIVWBGNhuH' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/auth/me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@me',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@me',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::s4xko4CIVWBGNhuH',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::cCl0ImtHiDnsQrfA' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/auth/revoke',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@revoke',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@revoke',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::cCl0ImtHiDnsQrfA',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::XZAIbBZeg8aCGzhV' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/auth/revoke-all',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AuthController@revokeAll',
        'controller' => 'App\\Http\\Controllers\\Api\\AuthController@revokeAll',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::XZAIbBZeg8aCGzhV',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::0m5ofg8t95LeBbQm' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/taxonomies',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@taxonomies',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@taxonomies',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::0m5ofg8t95LeBbQm',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mGq95LQNPvObWbXw' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/taxonomies',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@createTaxonomy',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@createTaxonomy',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::mGq95LQNPvObWbXw',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::U6LoYWJwPhW9Il2a' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/taxonomies/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@showTaxonomy',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@showTaxonomy',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::U6LoYWJwPhW9Il2a',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Gm7DvMEAvlmib799' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/taxonomies/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@updateTaxonomy',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@updateTaxonomy',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::Gm7DvMEAvlmib799',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::uK4RhSTrVWu0d0fd' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/taxonomies/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteTaxonomy',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteTaxonomy',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::uK4RhSTrVWu0d0fd',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::p7AmMD15oeV13faI' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/locations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@locations',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@locations',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::p7AmMD15oeV13faI',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::eeo07uUC7D4fWbS1' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/locations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@createLocation',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@createLocation',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::eeo07uUC7D4fWbS1',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ahN03Nc8iciO37aZ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/locations/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@showLocation',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@showLocation',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::ahN03Nc8iciO37aZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kCuR1mlZHy74WuPt' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/locations/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@updateLocation',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@updateLocation',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::kCuR1mlZHy74WuPt',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::TEfjwDCfmAQIORxI' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/locations/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteLocation',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteLocation',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::TEfjwDCfmAQIORxI',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::wfZZQak6GF5TxYOw' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/content-nodes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@contentNodes',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@contentNodes',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::wfZZQak6GF5TxYOw',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::aP3v50j5bZcar19y' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/content-nodes/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@showContentNode',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@showContentNode',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::aP3v50j5bZcar19y',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::eJUHvAnVZawpbiGj' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/content-nodes/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@updateContentNode',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@updateContentNode',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::eJUHvAnVZawpbiGj',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::zXLshfSotvmbBoH2' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/content-nodes/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteContentNode',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteContentNode',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::zXLshfSotvmbBoH2',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UVkPq145rXKsNUSb' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/keywords',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@keywords',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@keywords',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::UVkPq145rXKsNUSb',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::LAof46ciJdcoBmXt' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/keywords',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@createKeyword',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@createKeyword',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::LAof46ciJdcoBmXt',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::fRQlcboQSiUWtti8' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/keywords/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@showKeyword',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@showKeyword',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::fRQlcboQSiUWtti8',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::uORaRw85UGMSF073' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/keywords/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@updateKeyword',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@updateKeyword',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::uORaRw85UGMSF073',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::LcFyjSwm9n01QCwF' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/keywords/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteKeyword',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@deleteKeyword',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::LcFyjSwm9n01QCwF',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::QnuF4MSn2ThnynPV' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/live-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@liveData',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@liveData',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::QnuF4MSn2ThnynPV',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::j4NMBJqNtooUwOAd' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/live-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@upsertLiveData',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@upsertLiveData',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::j4NMBJqNtooUwOAd',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::tK6U0zlj6V1m85kT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/post-templates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@postTemplates',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@postTemplates',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::tK6U0zlj6V1m85kT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::luV79NH6xRGv89Si' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResourceController@export',
        'controller' => 'App\\Http\\Controllers\\Api\\ResourceController@export',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::luV79NH6xRGv89Si',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kGAk6HBew86IT8IP' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/orchestra/keywords',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\KeywordOrchestraController@importKeywords',
        'controller' => 'App\\Http\\Controllers\\Api\\KeywordOrchestraController@importKeywords',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::kGAk6HBew86IT8IP',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::iC2GAq388JC00eAf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/orchestra/sync-countries',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\KeywordOrchestraController@syncCountries',
        'controller' => 'App\\Http\\Controllers\\Api\\KeywordOrchestraController@syncCountries',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::iC2GAq388JC00eAf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Awi3xwVsbaSqyC0f' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/orchestra/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'sql.protect',
          2 => 'rate.limit.enhanced',
          3 => 'api.token',
          4 => 'throttle:60,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\KeywordOrchestraController@getStatus',
        'controller' => 'App\\Http\\Controllers\\Api\\KeywordOrchestraController@getStatus',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::Awi3xwVsbaSqyC0f',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::C5Mot6DL03AG3gHg' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'up',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:808:"function () {
                    $exception = null;

                    try {
                        \\Illuminate\\Support\\Facades\\Event::dispatch(new \\Illuminate\\Foundation\\Events\\DiagnosingHealth);
                    } catch (\\Throwable $e) {
                        if (app()->hasDebugModeEnabled()) {
                            throw $e;
                        }

                        report($e);

                        $exception = $e->getMessage();
                    }

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'C:\\\\SEO\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $exception ? 500 : 200);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"0000000000000c450000000000000000";}}',
        'as' => 'generated::C5Mot6DL03AG3gHg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Qxh6MSmDzeHmTvid' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'robots.txt',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:930:"function () {
    $sitemapUrl = \\config(\'app.url\') . \'/sitemap.xml\';
    $content = "User-agent: *\\n";
    $content .= "Allow: /\\n\\n";
    $content .= "Sitemap: {$sitemapUrl}\\n\\n";

    try {
        $totalContent = \\App\\Models\\ContentNode::whereNotNull(\'publish_date\')->count();
        $totalShards = \\max(1, (int) \\ceil($totalContent / 45000));
        for ($i = 1; $i <= $totalShards; $i++) {
            $content .= "Sitemap: " . \\url("/sitemap-content-{$i}.xml") . "\\n";
        }
    } catch (\\Exception $e) {
        $content .= "Sitemap: " . \\url("/sitemap-content-1.xml") . "\\n";
    }

    $content .= "Sitemap: " . \\url("/sitemap-categories.xml") . "\\n";
    $content .= "Sitemap: " . \\url("/sitemap-locations.xml") . "\\n\\n";
    $content .= "Disallow: /admin/\\n";
    $content .= "Disallow: /api/\\n";
    $content .= "Disallow: /_ignition/\\n";

    return \\response($content, 200, [\'Content-Type\' => \'text/plain\']);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c440000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Qxh6MSmDzeHmTvid',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Og2SumX1Vps918Ps' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'ads.txt',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:832:"function () {
    $adsTxt = \\App\\Models\\LiveDataVault::where(\'key\', \'ads_txt\')->first();
    if ($adsTxt?->value) {
        $content = $adsTxt->value;
    } else {
        $physicalFile = \\public_path(\'ads.txt\');
        if (\\file_exists($physicalFile)) {
            $content = \\file_get_contents($physicalFile);
        } else {
            $adClient = \\config(\'services.adsense.ad_client\');
            if ($adClient) {
                $pubId = \\str_replace(\'ca-\', \'\', $adClient);
                $content = "google.com, {$pubId}, DIRECT, f08c47fec0942fa0" . PHP_EOL;
            } else {
                $content = \'# ads.txt - \' . \\config(\'app.name\') . PHP_EOL . \'# Configure your AdSense publisher ID to enable.\' . PHP_EOL;
            }
        }
    }
    return \\response($content, 200, [\'Content-Type\' => \'text/plain\']);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c240000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Og2SumX1Vps918Ps',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Jch6UDREx1TFDIi8' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sellers.json',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:664:"function () {
    $publisherId = \\config(\'services.adsense.ad_client\', \'\');
    $content = \\json_encode([
        \'contact_email\' => \\config(\'app.admin_email\', \'admin@example.com\'),
        \'contact_address\' => \\config(\'app.address\', \'\'),
        \'version\' => \'1.0\',
        \'sellers\' => [
            [
                \'seller_id\' => $publisherId,
                \'seller_type\' => \'PUBLISHER\',
                \'name\' => \\config(\'app.name\', \'Omni Portal\'),
                \'domain\' => \\request()->getHost(),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return \\response($content, 200, [\'Content-Type\' => \'application/json\']);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c220000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Jch6UDREx1TFDIi8',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mQ3ZNkmlOwkZZKED' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/services/install/{service}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ServiceManagerController@install',
        'controller' => 'App\\Http\\Controllers\\ServiceManagerController@install',
        'namespace' => NULL,
        'prefix' => '/admin/services',
        'where' => 
        array (
        ),
        'as' => 'generated::mQ3ZNkmlOwkZZKED',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::tjOIvFO7T2h0pQ4J' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/services/enable/{service}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ServiceManagerController@enable',
        'controller' => 'App\\Http\\Controllers\\ServiceManagerController@enable',
        'namespace' => NULL,
        'prefix' => '/admin/services',
        'where' => 
        array (
        ),
        'as' => 'generated::tjOIvFO7T2h0pQ4J',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2Z70S3fkbJbRCdnE' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/services/disable/{service}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ServiceManagerController@disable',
        'controller' => 'App\\Http\\Controllers\\ServiceManagerController@disable',
        'namespace' => NULL,
        'prefix' => '/admin/services',
        'where' => 
        array (
        ),
        'as' => 'generated::2Z70S3fkbJbRCdnE',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::oLFkvtGbugIgjFyg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/services/save-service',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ServiceManagerController@saveService',
        'controller' => 'App\\Http\\Controllers\\ServiceManagerController@saveService',
        'namespace' => NULL,
        'prefix' => '/admin/services',
        'where' => 
        array (
        ),
        'as' => 'generated::oLFkvtGbugIgjFyg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::v407FndHIIGvXQpt' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/env-variables/services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ServiceManagerController@showServicesPage',
        'controller' => 'App\\Http\\Controllers\\ServiceManagerController@showServicesPage',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::v407FndHIIGvXQpt',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Jq70MqB6B36mTSIh' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/env-variables/services',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ServiceManagerController@handlePagePost',
        'controller' => 'App\\Http\\Controllers\\ServiceManagerController@handlePagePost',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Jq70MqB6B36mTSIh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:53:"function () {
    return \\redirect(\'/admin/login\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c200000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:101:"function () {
    \\Illuminate\\Support\\Facades\\Auth::logout();
    return \\redirect(\'/admin/login\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c180000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'rss' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'rss.xml',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:1774:"function () {
    $content = \\App\\Models\\ContentNode::whereNotNull(\'publish_date\')
        ->with([\'taxonomy\', \'location\'])
        ->orderBy(\'publish_date\', \'desc\')
        ->limit(50)
        ->get();

    $xml = \'<?xml version="1.0" encoding="UTF-8"?>\';
    $xml .= \'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">\';
    $xml .= \'<channel>\';
    $xml .= \'<title>\' . \\e(\\config(\'app.name\')) . \'</title>\';
    $xml .= \'<link>\' . \\url(\'/\') . \'</link>\';
    $xml .= \'<description>Latest content from \' . \\e(\\config(\'app.name\')) . \'</description>\';
    $xml .= \'<language>tr</language>\';
    $xml .= \'<lastBuildDate>\' . \\now()->toRssString() . \'</lastBuildDate>\';
    $xml .= \'<atom:link href="\' . \\url(\'/rss.xml\') . \'" rel="self" type="application/rss+xml"/>\';

    foreach ($content as $node) {
        $taxSlug = $node->taxonomy?->slug ?? \'\';
        $locSlug = $node->location?->slug ?? \'\';
        $url = \\url("/{$taxSlug}/{$locSlug}/{$node->slug}");
        $desc = $node->meta_description ?? \\substr(\\strip_tags($node->body_content ?? \'\'), 0, 200);

        $xml .= \'<item>\';
        $xml .= \'<title>\' . \\e($node->title) . \'</title>\';
        $xml .= \'<link>\' . $url . \'</link>\';
        $xml .= \'<guid isPermaLink="true">\' . $url . \'</guid>\';
        $xml .= \'<description>\' . \\e($desc) . \'</description>\';
        $xml .= \'<pubDate>\' . ($node->publish_date ? $node->publish_date->toRssString() : \\now()->toRssString()) . \'</pubDate>\';
        if ($node->taxonomy) {
            $xml .= \'<category>\' . \\e($node->taxonomy->name) . \'</category>\';
        }
        $xml .= \'</item>\';
    }

    $xml .= \'</channel></rss>\';
    return \\response($xml, 200, [\'Content-Type\' => \'application/rss+xml\']);
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c160000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'rss',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'locale.switch' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'lang/{locale}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:457:"function ($locale) {
    $locale = \\strtolower($locale);
    if (\\in_array($locale, [\'tr\', \'en\', \'ru\', \'ar\'])) {
        \\session([\'locale\' => $locale]);
        \\session()->save();
        \\Illuminate\\Support\\Facades\\Cookie::queue(\'app_locale\', $locale, 43200);
    }
    $previous = \\url()->previous();
    $current = \\url()->current();
    if ($previous && $previous !== $current) {
        return \\redirect($previous);
    }
    return \\redirect(\'/\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000c120000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'locale.switch',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@index',
        'controller' => 'App\\Http\\Controllers\\HomeController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'home',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::tfjhVs88WHRkCCmW' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'adblock/debug',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\AdBlockDebugController@debug',
        'controller' => 'App\\Http\\Controllers\\AdBlockDebugController@debug',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::tfjhVs88WHRkCCmW',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'search' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\ContentController@search',
        'controller' => 'App\\Http\\Controllers\\ContentController@search',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'search',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'search.suggest' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'search/suggest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\SearchController@api',
        'controller' => 'App\\Http\\Controllers\\SearchController@api',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'search.suggest',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'keywords.trending' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'keywords/trending',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\KeywordController@trending',
        'controller' => 'App\\Http\\Controllers\\KeywordController@trending',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'keywords.trending',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'keywords.suggest' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'keywords/suggest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\KeywordController@suggest',
        'controller' => 'App\\Http\\Controllers\\KeywordController@suggest',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'keywords.suggest',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'keywords.generate' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'keywords/generate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\KeywordController@generate',
        'controller' => 'App\\Http\\Controllers\\KeywordController@generate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'keywords.generate',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'keywords.auto-create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'keywords/auto-create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\KeywordController@autoCreateContent',
        'controller' => 'App\\Http\\Controllers\\KeywordController@autoCreateContent',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'keywords.auto-create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'privacy-policy' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'privacy-policy',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => '\\Illuminate\\Routing\\ViewController@__invoke',
        'controller' => '\\Illuminate\\Routing\\ViewController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'privacy-policy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'view' => 'static.privacy-policy',
        'data' => 
        array (
        ),
        'status' => 200,
        'headers' => 
        array (
        ),
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'terms' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'terms',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => '\\Illuminate\\Routing\\ViewController@__invoke',
        'controller' => '\\Illuminate\\Routing\\ViewController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'terms',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'view' => 'static.terms',
        'data' => 
        array (
        ),
        'status' => 200,
        'headers' => 
        array (
        ),
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'about' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'about',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => '\\Illuminate\\Routing\\ViewController@__invoke',
        'controller' => '\\Illuminate\\Routing\\ViewController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'about',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'view' => 'static.about',
        'data' => 
        array (
        ),
        'status' => 200,
        'headers' => 
        array (
        ),
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'contact' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'contact',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => '\\Illuminate\\Routing\\ViewController@__invoke',
        'controller' => '\\Illuminate\\Routing\\ViewController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'contact',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'view' => 'static.contact',
        'data' => 
        array (
        ),
        'status' => 200,
        'headers' => 
        array (
        ),
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sitemap' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sitemap.xml',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\SitemapController@index',
        'controller' => 'App\\Http\\Controllers\\SitemapController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'sitemap',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::GgvqGwCYaCSG3sWc' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sitemap-content-{page}.xml',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\SitemapController@contentShard',
        'controller' => 'App\\Http\\Controllers\\SitemapController@contentShard',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::GgvqGwCYaCSG3sWc',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'page' => '[0-9]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xIfVEU7XS3HMpg97' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sitemap-categories.xml',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\SitemapController@categories',
        'controller' => 'App\\Http\\Controllers\\SitemapController@categories',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::xIfVEU7XS3HMpg97',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::R8AizmcFnanMyXnM' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sitemap-locations.xml',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\SitemapController@locations',
        'controller' => 'App\\Http\\Controllers\\SitemapController@locations',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::R8AizmcFnanMyXnM',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'taxonomy.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'categories',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\TaxonomyController@index',
        'controller' => 'App\\Http\\Controllers\\TaxonomyController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'taxonomy.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'taxonomy.tree' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'categories/tree',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\TaxonomyController@tree',
        'controller' => 'App\\Http\\Controllers\\TaxonomyController@tree',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'taxonomy.tree',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'taxonomy.api' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'categories/api',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\TaxonomyController@apiTree',
        'controller' => 'App\\Http\\Controllers\\TaxonomyController@apiTree',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'taxonomy.api',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'taxonomy.search' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'categories/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\TaxonomyController@search',
        'controller' => 'App\\Http\\Controllers\\TaxonomyController@search',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'taxonomy.search',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'location.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'locations',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\LocationController@index',
        'controller' => 'App\\Http\\Controllers\\LocationController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'location.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'location.tree' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'locations/tree',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\LocationController@tree',
        'controller' => 'App\\Http\\Controllers\\LocationController@tree',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'location.tree',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'location.api' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'locations/api',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\LocationController@apiTree',
        'controller' => 'App\\Http\\Controllers\\LocationController@apiTree',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'location.api',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'location.search' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'locations/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\LocationController@search',
        'controller' => 'App\\Http\\Controllers\\LocationController@search',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'location.search',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'location.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'location/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\LocationController@show',
        'controller' => 'App\\Http\\Controllers\\LocationController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'location.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'location.district' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'location/{citySlug}/{districtSlug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\LocationController@district',
        'controller' => 'App\\Http\\Controllers\\LocationController@district',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'location.district',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'content.by-slug' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'p/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\ContentController@showBySlug',
        'controller' => 'App\\Http\\Controllers\\ContentController@showBySlug',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'content.by-slug',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'slug' => '[^/]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'taxonomy.location' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{category}/{locationSlug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\ContentController@index',
        'controller' => 'App\\Http\\Controllers\\ContentController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'taxonomy.location',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'category' => '[^/]+',
        'locationSlug' => '[^/]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'taxonomy.category' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{category}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\ContentController@taxonomyIndex',
        'controller' => 'App\\Http\\Controllers\\ContentController@taxonomyIndex',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'taxonomy.category',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'category' => '[^/]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'content.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{category}/{locationSlug}/{slug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'sql.protect',
        ),
        'uses' => 'App\\Http\\Controllers\\ContentController@show',
        'controller' => 'App\\Http\\Controllers\\ContentController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'content.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'category' => '[^/]+',
        'locationSlug' => '[^/]+',
        'slug' => '[^/]+',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:26:"C:\\SEO\\storage\\app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:1;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"0000000000000c130000000000000000";}}',
        'as' => 'storage.local',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local.upload' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:26:"C:\\SEO\\storage\\app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:1;}s:8:"function";s:325:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ReceiveFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"0000000000000bf10000000000000000";}}',
        'as' => 'storage.local.upload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
