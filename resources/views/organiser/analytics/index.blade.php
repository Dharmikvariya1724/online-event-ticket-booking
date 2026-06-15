@extends('layouts.organiser')

@section('title', 'Analytics')
@section('page-title', 'Analytics')
@section('page-subtitle', 'Revenue and tickets sold over time')

@section('content')

<form method="GET" class="flex flex-wrap gap-3 mb-6">
  <select name="event_id" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <option value="">All Events</option>
    @foreach($events as $event)
    <option value="{{ $event->id }}" {{ (string) $selectedEventId === (string) $event->id ? 'selected' : '' }}>{{ Str::limit($event->title, 45) }}</option>
    @endforeach
  </select>

  <input type="date" name="date_from" value="{{ $dateFrom }}" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
  <input type="date" name="date_to" value="{{ $dateTo }}" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">

  <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl">Apply</button>

  @if(request()->hasAny(['event_id', 'date_from', 'date_to']))
  <a href="{{ route('organiser.analytics.index') }}" class="bg-gray-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-gray-600">Clear</a>
  @endif
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="text-xs text-gray-500 mb-1">Revenue in selected range</div>
    <div class="text-2xl font-extrabold text-white">{{ ticketly_money_code($totalRevenue) }}</div>
    <div class="text-xs text-gray-600 mt-1">{{ $selectedEventTitle }} · {{ $dateFrom }} to {{ $dateTo }}</div>
  </div>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="text-xs text-gray-500 mb-1">Tickets sold in selected range</div>
    <div class="text-2xl font-extrabold text-white">{{ number_format($totalTickets) }}</div>
    <div class="text-xs text-gray-600 mt-1">{{ $selectedEventTitle }} · {{ $dateFrom }} to {{ $dateTo }}</div>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
    <h2 class="text-lg font-extrabold text-white mb-1">Revenue Over Time</h2>
    <p class="text-gray-500 text-xs mb-4">Daily totals for paid bookings</p>
    <div class="relative" style="height:300px">
      <canvas id="analyticsRevenueChart"></canvas>
    </div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
    <h2 class="text-lg font-extrabold text-white mb-1">Tickets Sold Over Time</h2>
    <p class="text-gray-500 text-xs mb-4">Daily ticket quantity for paid bookings</p>
    <div class="relative" style="height:300px">
      <canvas id="analyticsTicketsChart"></canvas>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const analyticsLabels = {!! json_encode($chartLabels) !!};
const analyticsRevenue = {!! json_encode($chartRevenue) !!};
const analyticsTickets = {!! json_encode($chartTickets) !!};

new Chart(document.getElementById('analyticsRevenueChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: analyticsLabels,
    datasets: [{
      label: 'Revenue',
      data: analyticsRevenue,
      borderColor: '#6366f1',
      backgroundColor: 'rgba(99,102,241,0.18)',
      borderWidth: 2,
      pointRadius: 2,
      pointHoverRadius: 4,
      fill: true,
      tension: 0.35
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1f2937',
        borderColor: '#374151',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => ' ' + @js(ticketly_currency()) + ' ' + Number(ctx.parsed.y || 0).toFixed(2)
        }
      }
    },
    scales: {
      x: { grid: { color: '#1f2937' }, ticks: { color: '#6b7280', maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
      y: {
        grid: { color: '#1f2937' },
        ticks: {
          color: '#6b7280',
          callback: (v) => @js(ticketly_currency()) + ' ' + v
        }
      }
    }
  }
});

new Chart(document.getElementById('analyticsTicketsChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: analyticsLabels,
    datasets: [{
      label: 'Tickets',
      data: analyticsTickets,
      borderColor: '#ec4899',
      backgroundColor: 'rgba(236,72,153,0.14)',
      borderWidth: 2,
      pointRadius: 2,
      pointHoverRadius: 4,
      fill: true,
      tension: 0.35
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1f2937',
        borderColor: '#374151',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => ' ' + Number(ctx.parsed.y || 0).toFixed(0) + ' tickets'
        }
      }
    },
    scales: {
      x: { grid: { color: '#1f2937' }, ticks: { color: '#6b7280', maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
      y: {
        beginAtZero: true,
        grid: { color: '#1f2937' },
        ticks: {
          color: '#6b7280',
          precision: 0
        }
      }
    }
  }
});
</script>
@endsection
