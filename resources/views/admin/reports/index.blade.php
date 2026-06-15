@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Tickets, revenue, and performance')

@section('content')
<div class="grid gap-6">
  <form method="GET" class="bg-gray-900 border border-gray-800 rounded-2xl p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="text-xs text-gray-400 uppercase">From</label>
      <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div>
      <label class="text-xs text-gray-400 uppercase">To</label>
      <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div class="flex items-end">
      <button class="w-full px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Update</button>
    </div>
  </form>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">Tickets Sold Per Day</h2>
    <div class="h-64">
      <canvas id="ticketsChart"></canvas>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <h2 class="text-sm font-semibold text-white mb-4">Revenue Per Event</h2>
      <div class="h-64">
        <canvas id="revenueEventChart"></canvas>
      </div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <h2 class="text-sm font-semibold text-white mb-4">Revenue Per Organiser</h2>
      <div class="h-64">
        <canvas id="revenueOrganiserChart"></canvas>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <h2 class="text-sm font-semibold text-white mb-4">Top Events</h2>
      <ul class="space-y-2 text-sm text-gray-300">
        @foreach($topEvents as $row)
          <li class="flex items-center justify-between border border-gray-800 rounded-xl px-3 py-2">
            <span>{{ $row->label }}</span>
            <span class="text-gray-400">{{ number_format($row->value) }} tickets</span>
          </li>
        @endforeach
      </ul>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <h2 class="text-sm font-semibold text-white mb-4">Top Organisers</h2>
      <ul class="space-y-2 text-sm text-gray-300">
        @foreach($topOrganisers as $row)
          <li class="flex items-center justify-between border border-gray-800 rounded-xl px-3 py-2">
            <span>{{ $row->label }}</span>
            <span class="text-gray-400">{{ ticketly_money($row->value) }}</span>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const ticketsCtx = document.getElementById('ticketsChart');
new Chart(ticketsCtx, {
  type: 'line',
  data: {
    labels: {!! json_encode($chartLabels) !!},
    datasets: [{
      label: 'Tickets',
      data: {!! json_encode($chartTickets) !!},
      borderColor: '#14b8a6',
      backgroundColor: 'rgba(20,184,166,0.2)',
      fill: true,
      tension: 0.4
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

const revenueEventCtx = document.getElementById('revenueEventChart');
new Chart(revenueEventCtx, {
  type: 'bar',
  data: {
    labels: {!! json_encode($revenueByEvent->pluck('label')) !!},
    datasets: [{
      label: 'Revenue',
      data: {!! json_encode($revenueByEvent->pluck('value')) !!},
      backgroundColor: 'rgba(16,185,129,0.5)',
      borderColor: '#10b981',
      borderWidth: 1
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});

const revenueOrgCtx = document.getElementById('revenueOrganiserChart');
new Chart(revenueOrgCtx, {
  type: 'bar',
  data: {
    labels: {!! json_encode($revenueByOrganiser->pluck('label')) !!},
    datasets: [{
      label: 'Revenue',
      data: {!! json_encode($revenueByOrganiser->pluck('value')) !!},
      backgroundColor: 'rgba(14,165,233,0.5)',
      borderColor: '#0ea5e9',
      borderWidth: 1
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
</script>
@endsection
