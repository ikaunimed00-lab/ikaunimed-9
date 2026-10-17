@php
    $printHeader = app(\App\Services\TocShareableReport::class)->printHeader();
@endphp

<x-filament-panels::page class="fi-dashboard-page toc-dashboard-page">
    <div class="toc-print-header hidden print:block mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Teacher of Champions</h1>
        <p class="text-sm text-gray-700">{{ $printHeader }}</p>
    </div>

    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :data="$this->getWidgetData()"
        :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
