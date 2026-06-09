<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostTemplate;

class PostTemplateSeeder extends Seeder
{
    public function run(): void
    {
        PostTemplate::create([
            'name' => 'Türkçe Hizmet Sayfası',
            'slug' => 'tr-hizmet-sayfasi',
            'template_body' => '
<article class="hizmet-sayfasi">
    <h1>{city} {category} Servisi — 7/24 Profesyonel Hizmet</h1>

    <div class="giris">
        <p>{city} ilinde {category} hizmeti mi ar\u0131yorsunuz? Uzman ekibimiz {city} ve t\u00fcm il\u00e7elerinde 7/24 hizmetinizdedir.</p>
        <p>{district} ba\u015fta olmak \u00fczere {city} genelinde h\u0131zl\u0131 ve g\u00fcvenilir {category} hizmeti sunuyoruz.</p>
    </div>

    <h2>Neden Bizi Se\u00e7melisiniz?</h2>
    <ul>
        <li>\u2713 {city} genelinde 30 dakikada ula\u015f\u0131m</li>
        <li>\u2713 Sertifikal\u0131 ve deneyimli teknisyenler</li>
        <li>\u2713 Garantili {category} hizmeti</li>
        <li>\u2713 Uygun ve \u015feffaf fiyatland\u0131rma</li>
        <li>\u2713 7/24 acil {category} deste\u011fi</li>
    </ul>

    <h2>{city} {category} Fiyatlar\u0131 2026</h2>
    <p>{city} {category} fiyatlar\u0131 hizmetin kapsam\u0131na, kullan\u0131lan malzemeye ve ula\u015f\u0131m mesafesine g\u00f6re de\u011fi\u015fmektedir. \u00dccretsiz ke\u015fif i\u00e7in hemen aray\u0131n.</p>

    <h2>S\u0131k\u00e7a Sorulan Sorular</h2>
    <details>
        <summary>{city}\'de {category} fiyat\u0131 ne kadar?</summary>
        <p>{city} {category} fiyatlar\u0131 2026 y\u0131l\u0131nda ortalama 500-2000 TL aras\u0131nda de\u011fi\u015fmektedir. Kesin fiyat i\u00e7in \u00fccretsiz ke\u015fif talep edin.</p>
    </details>
    <details>
        <summary>Acil {category} hizmeti var m\u0131?</summary>
        <p>Evet! {city} genelinde 7/24 acil {category} hizmeti sunuyoruz. Hafta sonu ve tatil g\u00fcnleri de dahil.</p>
    </details>
    <details>
        <summary>{category} hizmeti ne kadar s\u00fcrer?</summary>
        <p>Standart {category} i\u015flemleri 1-3 saat s\u00fcrmektedir. Karma\u015f\u0131k durumlarda bu s\u00fcre uzayabilir.</p>
    </details>
</article>',
        ]);

        PostTemplate::create([
            'name' => 'English Service Page',
            'slug' => 'en-service-page',
            'template_body' => '
<article class="service-page">
    <h1>{category} Service in {city} \u2014 Professional 24/7 Service</h1>

    <div class="intro">
        <p>Looking for {category} services in {city}? Our expert team serves {city} and all districts 24/7.</p>
        <p>We provide fast and reliable {category} services throughout {city}, including {district}.</p>
    </div>

    <h2>Why Choose Us?</h2>
    <ul>
        <li>\u2713 30-minute response time across {city}</li>
        <li>\u2713 Certified and experienced technicians</li>
        <li>\u2713 Guaranteed {category} service</li>
        <li>\u2713 Transparent and competitive pricing</li>
        <li>\u2713 24/7 emergency {category} support</li>
    </ul>

    <h2>{city} {category} Prices 2026</h2>
    <p>Prices for {category} in {city} vary based on scope, materials, and distance. Free inspection available upon request.</p>

    <h2>Frequently Asked Questions</h2>
    <details>
        <summary>How much does {category} cost in {city}?</summary>
        <p>{city} {category} prices in 2026 range depending on the scope of work. Request a free inspection for an accurate quote.</p>
    </details>
    <details>
        <summary>Is emergency {category} service available?</summary>
        <p>Yes! We offer 24/7 emergency {category} service throughout {city}, including weekends and holidays.</p>
    </details>
</article>',
        ]);

        PostTemplate::create([
            'name' => 'Russian Service Page',
            'slug' => 'ru-service-page',
            'template_body' => '
<article class="service-page">
    <h1>\u0423\u0441\u043b\u0443\u0433\u0430 {category} \u0432 {city} \u2014 \u041f\u0440\u043e\u0444\u0435\u0441\u0441\u0438\u043e\u043d\u0430\u043b\u044c\u043d\u044b\u0439 \u0441\u0435\u0440\u0432\u0438\u0441 24/7</h1>

    <div class="intro">
        <p>\u0418\u0449\u0435\u0442\u0435 \u0443\u0441\u043b\u0443\u0433\u0438 {category} \u0432 {city}? \u041d\u0430\u0448\u0430 \u043a\u043e\u043c\u0430\u043d\u0434\u0430 \u044d\u043a\u0441\u043f\u0435\u0440\u0442\u043e\u0432 \u043e\u0431\u0441\u043b\u0443\u0436\u0438\u0432\u0430\u0435\u0442 {city} \u0438 \u0432\u0441\u0435 \u0440\u0430\u0439\u043e\u043d\u044b \u043a\u0440\u0443\u0433\u043b\u043e\u0441\u0443\u0442\u043e\u0447\u043d\u043e.</p>
    </div>

    <h2>\u041f\u043e\u0447\u0435\u043c\u0443 \u0432\u044b\u0431\u0438\u0440\u0430\u044e\u0442 \u043d\u0430\u0441?</h2>
    <ul>
        <li>\u2713 \u0412\u044b\u0435\u0437\u0434 \u0437\u0430 30 \u043c\u0438\u043d\u0443\u0442 \u043f\u043e \u0432\u0441\u0435\u043c\u0443 {city}</li>
        <li>\u2713 \u0421\u0435\u0440\u0442\u0438\u0444\u0438\u0446\u0438\u0440\u043e\u0432\u0430\u043d\u043d\u044b\u0435 \u0441\u043f\u0435\u0446\u0438\u0430\u043b\u0438\u0441\u0442\u044b</li>
        <li>\u2713 \u0413\u0430\u0440\u0430\u043d\u0442\u0438\u0440\u043e\u0432\u0430\u043d\u043d\u043e\u0435 \u043a\u0430\u0447\u0435\u0441\u0442\u0432\u043e {category}</li>
        <li>\u2713 \u041f\u0440\u043e\u0437\u0440\u0430\u0447\u043d\u043e\u0435 \u0446\u0435\u043d\u043e\u043e\u0431\u0440\u0430\u0437\u043e\u0432\u0430\u043d\u0438\u0435</li>
        <li>\u2713 \u0410\u0432\u0430\u0440\u0438\u0439\u043d\u0430\u044f \u0441\u043b\u0443\u0436\u0431\u0430 {category} 24/7</li>
    </ul>

    <h2>\u0427\u0430\u0441\u0442\u043e \u0437\u0430\u0434\u0430\u0432\u0430\u0435\u043c\u044b\u0435 \u0432\u043e\u043f\u0440\u043e\u0441\u044b</h2>
    <details>
        <summary>\u0421\u043a\u043e\u043b\u044c\u043a\u043e \u0441\u0442\u043e\u0438\u0442 {category} \u0432 {city}?</summary>
        <p>\u0426\u0435\u043d\u044b \u043d\u0430 {category} \u0432 {city} \u0432\u0430\u0440\u044c\u0438\u0440\u0443\u044e\u0442\u0441\u044f \u0432 \u0437\u0430\u0432\u0438\u0441\u0438\u043c\u043e\u0441\u0442\u0438 \u043e\u0442 \u043e\u0431\u044a\u0435\u043c\u0430 \u0440\u0430\u0431\u043e\u0442. \u0417\u0430\u043f\u0440\u043e\u0441\u0438\u0442\u0435 \u0431\u0435\u0441\u043f\u043b\u0430\u0442\u043d\u044b\u0439 \u043e\u0441\u043c\u043e\u0442\u0440.</p>
    </details>
    <details>
        <summary>\u0415\u0441\u0442\u044c \u043b\u0438 \u044d\u043a\u0441\u0442\u0440\u0435\u043d\u043d\u0430\u044f \u0441\u043b\u0443\u0436\u0431\u0430 {category}?</summary>
        <p>\u0414\u0430! \u041c\u044b \u043f\u0440\u0435\u0434\u043b\u0430\u0433\u0430\u0435\u043c \u044d\u043a\u0441\u0442\u0440\u0435\u043d\u043d\u0443\u044e \u0441\u043b\u0443\u0436\u0431\u0443 {category} \u043a\u0440\u0443\u0433\u043b\u043e\u0441\u0443\u0442\u043e\u0447\u043d\u043e.</p>
    </details>
</article>',
        ]);

        PostTemplate::create([
            'name' => 'French Service Page',
            'slug' => 'fr-service-page',
            'template_body' => '
<article class="service-page">
    <h1>Service {category} \u00e0 {city} \u2014 Service professionnel 24/7</h1>

    <div class="intro">
        <p>Vous cherchez des services {category} \u00e0 {city}? Vous \u00eates au bon endroit.</p>
        <p>Notre \u00e9quipe experte sert {city} et tous les quartiers 24h/24.</p>
    </div>

    <h2>Pourquoi nous choisir?</h2>
    <ul>
        <li>\u2713 Intervention en 30 minutes dans tout {city}</li>
        <li>\u2713 Techniciens certifi\u00e9s et exp\u00e9riment\u00e9s</li>
        <li>\u2713 Service {category} garantie</li>
        <li>\u2713 Prix transparents et comp\u00e9titifs</li>
        <li>\u2713 Service d\'urgence {category} 24/7</li>
    </ul>

    <h2>{city} Prix {category} 2026</h2>
    <p>Les prix pour {category} \u00e0 {city} varient selon la port\u00e9e, les mat\u00e9riaux et la distance. Inspection gratuite disponible.</p>

    <h2>Questions Fr\u00e9quentes</h2>
    <details>
        <summary>Combien co\u00fbte {category} \u00e0 {city}?</summary>
        <p>Les prix {category} \u00e0 {city} en 2026 varient selon l\'\u00e9tendue des travaux. Demandez une inspection gratuite.</p>
    </details>
    <details>
        <summary>Le service d\'urgence {category} est-il disponible?</summary>
        <p>Oui! Nous offrons un service d\'urgence {category} 24/7 dans tout {city}, y compris les week-ends.</p>
    </details>
</article>',
        ]);

        $this->command->info('4 post template yüklendi (TR/EN/RU/FR).');
    }
}
