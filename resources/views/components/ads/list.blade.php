{{-- Ad List Component - Iklan di antara list berita --}}
<div class="ad-list-item bg-white rounded-lg border border-gray-200 p-4 my-4 flex items-center justify-center">
    <div class="ad-placeholder text-center text-gray-400 w-full py-8">
        @php
            $adsEnabled = \App\Models\SiteSetting::getValue('ads_enabled', false);
            $adsProvider = \App\Models\SiteSetting::getValue('ads_provider_primary', 'adsense');
            $clientId = \App\Models\SiteSetting::getValue('adsense_client_id', 'ca-pub-xxxxxxxxxxxxxxxx');
            $slotList = \App\Models\SiteSetting::getValue('adsense_slot_list_item', 'list-ad-item');
        @endphp
        @if(config('app.debug') || !$adsEnabled || $adsProvider !== 'adsense')
            <div class="text-sm font-medium">
                📢 List Ad Item
            </div>
            <div class="text-xs mt-2 text-gray-500">
                Position: After {{ $afterItem ?? 'n' }} items
            </div>
            <div class="text-xs mt-1 text-gray-500">
                728x90 • Leaderboard (Desktop)
            </div>
            <div class="text-xs mt-1 text-gray-500">
                320x50 • Mobile Banner
            </div>
        @else
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="{{ $clientId }}"
                 data-ad-slot="{{ $slotList }}"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        @endif
    </div>
</div>

<style>
    .ad-list-item {
        transition: all 0.2s ease;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-color: #e5e7eb;
    }
    
    .ad-list-item:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .ad-placeholder {
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .ad-list-item {
            margin: 1rem 0;
            padding: 1rem;
        }
        
        .ad-placeholder {
            min-height: 100px;
            font-size: 0.85rem;
        }
    }
</style>
