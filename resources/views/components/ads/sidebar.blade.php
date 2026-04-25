{{-- Ad Sidebar Component - Sticky sidebar untuk desktop --}}
<div class="ad-sidebar-wrapper hidden lg:block">
    <div class="ad-sidebar sticky top-20 space-y-4">
        
        <!-- Sidebar Ad Slot 1 -->
        <div class="ad-slot bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden"
             style="width: 300px; min-height: 250px;">
            <div class="ad-placeholder text-center text-gray-400 p-4 w-full">
                @php
                    $adsEnabled = \App\Models\SiteSetting::getValue('ads_enabled', false);
                    $adsProvider = \App\Models\SiteSetting::getValue('ads_provider_primary', 'adsense');
                    $clientId = \App\Models\SiteSetting::getValue('adsense_client_id', 'ca-pub-xxxxxxxxxxxxxxxx');
                    $slotSidebar1 = \App\Models\SiteSetting::getValue('adsense_slot_sidebar_1', 'sidebar-ad-1');
                    $slotSidebar2 = \App\Models\SiteSetting::getValue('adsense_slot_sidebar_2', 'sidebar-ad-2');
                @endphp
                @if(config('app.debug') || !$adsEnabled || $adsProvider !== 'adsense')
                    <div class="text-sm font-medium">
                        📢 Sidebar Ad 1
                    </div>
                    <div class="text-xs mt-1 text-gray-500">
                        300x250 • Display
                    </div>
                @else
                    <ins class="adsbygoogle"
                         style="display:inline-block;width:300px;height:250px"
                         data-ad-client="{{ $clientId }}"
                         data-ad-slot="{{ $slotSidebar1 }}"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                @endif
            </div>
        </div>
        
        <!-- Optional: Sidebar Ad Slot 2 (tall format) -->
        @if($showSecondSlot ?? true)
        <div class="ad-slot bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden"
             style="width: 300px; min-height: 600px;">
            <div class="ad-placeholder text-center text-gray-400 p-4 w-full">
                @if(config('app.debug') || !$adsEnabled || $adsProvider !== 'adsense')
                    <div class="text-sm font-medium">
                        📢 Sidebar Ad 2
                    </div>
                    <div class="text-xs mt-1 text-gray-500">
                        300x600 • Tall Display
                    </div>
                @else
                    <ins class="adsbygoogle"
                         style="display:inline-block;width:300px;height:600px"
                         data-ad-client="{{ $clientId }}"
                         data-ad-slot="{{ $slotSidebar2 }}"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                @endif
            </div>
        </div>
        @endif
        
    </div>
</div>

<style>
    .ad-sidebar-wrapper {
        /* Hide on tablets and below */
        display: none;
    }
    
    @media (min-width: 1024px) {
        .ad-sidebar-wrapper {
            display: block;
        }
    }
    
    .ad-sidebar {
        /* Sticky positioning untuk follow scroll */
        position: sticky;
        top: 80px; /* Adjust based on header height */
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    
    .ad-slot {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .ad-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
    }
</style>
