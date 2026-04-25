{{-- Ad Banner Component - Responsive placeholder untuk Google AdSense --}}
<div class="ad-banner-container" data-ad-slot="{{ $adSlot ?? 'ad-banner-default' }}">
    <div class="ad-banner bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden" 
         style="min-height: {{ $minHeight ?? '250px' }}">
        
        <!-- Placeholder untuk production AdSense script -->
        <div class="ad-placeholder text-center text-gray-400 py-8 px-4 w-full">
            @php
                $adsEnabled = \App\Models\SiteSetting::getValue('ads_enabled', false);
                $adsProvider = \App\Models\SiteSetting::getValue('ads_provider_primary', 'adsense');
                $clientId = \App\Models\SiteSetting::getValue('adsense_client_id', 'ca-pub-xxxxxxxxxxxxxxxx');
                $slotBanner = $adSlot ?? \App\Models\SiteSetting::getValue('adsense_slot_banner', 'ad-banner-default');
            @endphp
            @if(config('app.debug') || !$adsEnabled || $adsProvider !== 'adsense')
                <div class="text-sm font-medium">
                    📢 Ad Slot: {{ $adSlot ?? 'banner' }}
                </div>
                <div class="text-xs mt-1">
                    {{ $size ?? '300x250' }} • {{ $type ?? 'Display' }}
                </div>
            @else
                <!-- Google AdSense Script akan di-inject di sini -->
                <ins class="adsbygoogle"
                     style="display:block; min-height: {{ $minHeight ?? '250px' }}"
                     data-ad-client="{{ $clientId }}"
                     data-ad-slot="{{ $slotBanner }}"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            @endif
        </div>
    </div>
</div>

<style>
    .ad-banner-container {
        margin: 1.5rem 0;
    }
    
    .ad-banner {
        transition: all 0.3s ease;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .ad-banner-container {
            margin: 1rem 0;
        }
        
        .ad-placeholder {
            font-size: 0.75rem;
        }
    }
</style>
