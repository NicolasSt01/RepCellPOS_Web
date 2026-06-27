@props([
    'chartId' => 'chart',
    'height' => 300,
])

<div class="relative overflow-hidden" style="min-height: {{ $height }}px">
    <div id="{{ $chartId }}"></div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const options = {!! $chartOptions ?? '{}' !!};
        const data = {!! $chartData ?? '[]' !!};
        options.series = data;
        options.chart = options.chart || {};
        options.chart.id = '{{ $chartId }}';
        options.chart.type = options.chart.type || 'line';
        options.chart.height = {{ $height }};
        options.chart.toolbar = options.chart.toolbar || { show: true };
        if (!options.chart.hasOwnProperty('zoom')) {
            options.chart.zoom = { enabled: true };
        }
        new ApexCharts(document.querySelector('#{{ $chartId }}'), options).render();
    });
</script>
@endpush
