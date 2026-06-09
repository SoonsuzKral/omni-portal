<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SeoJsonLd extends Component
{
    public $content;
    public $taxonomy;
    public $location;
    public $jsonLd;

    public function __construct($content, $taxonomy = null, $location = null)
    {
        $this->content = $content;
        $this->taxonomy = $taxonomy;
        $this->location = $location;
        $this->jsonLd = $this->generateJsonLd();
    }

    protected function generateJsonLd(): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@graph' => [],
        ];

        $article = [
            '@type' => 'Article',
            'headline' => $this->content->title,
            'description' => $this->content->meta_description ?? substr(strip_tags($this->content->body_content ?? ''), 0, 160),
            'datePublished' => $this->content->publish_date?->toIso8601String(),
            'dateModified' => $this->content->updated_at?->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'Omni Portal'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'Omni Portal'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('logo.png'),
                ],
            ],
        ];

        if (!empty($this->content->featured_image)) {
            $article['image'] = $this->content->featured_image;
        }

        if ($this->taxonomy && $this->taxonomy->name) {
            $article['articleSection'] = $this->taxonomy->name;
        }

        $data['@graph'][] = $article;

        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        $items = [];

        $items[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => url('/'),
        ];

        if ($this->taxonomy) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $this->taxonomy->name,
                'item' => url('/' . $this->taxonomy->slug),
            ];
        }

        if ($this->location) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $this->location->name,
                'item' => url('/' . ($this->taxonomy?->slug ?? '') . '/' . $this->location->slug),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $this->content->title,
            'item' => url('/' . ($this->taxonomy?->slug ?? '') . '/' . ($this->location?->slug ?? '') . '/' . $this->content->slug),
        ];

        $breadcrumb['itemListElement'] = $items;
        $data['@graph'][] = $breadcrumb;

        $data['@graph'][] = [
            '@type' => 'Product',
            'name' => $this->content->title,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'bestRating' => '5',
                'worstRating' => '1',
                'ratingCount' => max(1, $this->content->page_views ?? 1),
                'reviewCount' => max(1, (int) (($this->content->page_views ?? 10) / 10)),
            ],
        ];

        $cityName = $this->location?->name;
        if ($cityName) {
            $data['@graph'][] = [
                '@type' => 'LocalBusiness',
                'name' => $cityName . ' ' . ($this->taxonomy?->name ?? 'Hizmet'),
                'url' => url()->current(),
                'areaServed' => [
                    '@type' => 'City',
                    'name' => $cityName,
                ],
                'serviceType' => $this->taxonomy?->name ?? '',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $cityName,
                    'addressCountry' => 'TR',
                ],
            ];
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function render()
    {
        return view('components.seo-json-ld');
    }
}
