@php
$adClient = config('services.adsense.ad_client', env('ADSENSE_AD_CLIENT'));
$publisherId = config('services.adsense.publisher_id', env('ADSENSE_PUBLISHER_ID'));
$headerSlot = config('services.adsense.header_slot', env('ADSENSE_AD_SLOT_HEADER'));
$sidebarSlot = config('services.adsense.sidebar_slot', env('ADSENSE_AD_SLOT_SIDEBAR'));
$inArticleSlot = config('services.adsense.inarticle_slot', env('ADSENSE_AD_SLOT_INARTICLE'));
$enabled = !empty($publisherId) && !empty($adClient);
@endphp

@if($enabled)
<style>
.adsense-ad-unit {
    min-height: 90px;
    margin: 1rem 0;
    background: #f8f9fa;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.adsense-ad-unit.ins-adsbygoogle {
    min-height: 250px;
}
.adsense-header {
    min-height: 90px;
}
.adsense-sidebar {
    min-height: 600px;
}
.adsense-inarticle {
    min-height: 250px;
}
.adsense-responsive {
    width: 100%;
    height: auto;
}
</style>
@endif

{{-- Header Reklam (Sayfa üstü) --}}
@if($enabled && !empty($headerSlot))
<div class="adsense-ad-unit adsense-header">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="{{ $adClient }}"
         data-ad-slot="{{ $headerSlot }}"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>
@endif

{{-- Sidebar Reklam --}}
@if($enabled && !empty($sidebarSlot))
<div class="adsense-ad-unit adsense-sidebar">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="{{ $adClient }}"
         data-ad-slot="{{ $sidebarSlot }}"
         data-ad-format="vertical"
         data-full-width-responsive="true"></ins>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>
@endif

{{-- In-Article Reklam (İçerik arası) --}}
@if($enabled && !empty($inArticleSlot))
<div class="adsense-ad-unit adsense-inarticle">
    <ins class="adsbygoogle"
         style="display:block; text-align:center;"
         data-ad-client="{{ $adClient }}"
         data-ad-slot="{{ $inArticleSlot }}"
         data-ad-format="fluid"
         data-full-width-responsive="true"></ins>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>
@endif

{{-- Basit reklam gösterme fonksiyonu --}}
@if($enabled)
<script>
function showAdSenseAd(slotId, targetElement) {
    (adsbygoogle = window.adsbygoogle || []).push({});
}
</script>
@endif