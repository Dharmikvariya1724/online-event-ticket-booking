@extends('layouts.organiser')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . $organiser->name . '!')
@section('body-class', 'dashboard-page')

@section('page-icon')
<div class="dashboard-page-icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <rect x="4" y="5" width="7" height="14" rx="1.8" stroke-width="1.9"></rect>
        <rect x="13" y="5" width="7" height="14" rx="1.8" stroke-width="1.9"></rect>
    </svg>
</div>
@endsection

@section('header-actions')
<a href="{{ route('organiser.events.create') }}" class="dashboard-create-button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <circle cx="12" cy="12" r="8.5" stroke-width="1.8"></circle>
        <path d="M12 8.5v7M8.5 12h7" stroke-linecap="round" stroke-width="1.8"></path>
    </svg>
    <span>Create Event</span>
</a>
@endsection

@section('head')
<style>
  .dashboard-page {
    background-image: none !important;
  }

  .dashboard-page .organiser-shell-main {
    padding: 1.6rem 1.65rem 2.4rem;
  }

  .dashboard-page-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--dashboard-heading);
  }

  .dashboard-page-icon svg {
    width: 100%;
    height: 100%;
  }

  .dashboard-create-button {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    border-radius: 0.55rem;
    padding: 0.88rem 1.18rem;
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1;
    color: #ffffff;
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    box-shadow: none;
  }

  .dashboard-create-button svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
  }

  .dashboard-overview {
    display: grid;
    gap: 1.65rem;
    min-width: 0;
  }

  .dashboard-metrics,
  .dashboard-grid {
    display: grid;
    gap: 1.65rem;
    min-width: 0;
  }

  .dashboard-metrics {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .dashboard-grid {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    align-items: start;
  }

  .dashboard-panel,
  .dashboard-metric {
    background: var(--dashboard-surface);
    border: 1px solid var(--dashboard-border);
    border-radius: 1.15rem;
    box-shadow: var(--dashboard-shadow);
    min-width: 0;
  }

  .dashboard-metric {
    padding: 1.72rem 1.72rem 1.55rem;
    min-height: 11.2rem;
  }

  .dashboard-metric__head,
  .dashboard-panel__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .dashboard-metric__head {
    margin-bottom: 1.25rem;
  }

  .dashboard-metric__icon {
    width: 2.9rem;
    height: 2.9rem;
    border-radius: 0.8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #7c3aed;
    background: var(--dashboard-icon-bg);
  }

  .dashboard-metric__icon svg {
    width: 1.35rem;
    height: 1.35rem;
  }

  .dashboard-metric__delta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 4.7rem;
    border-radius: 0.45rem;
    padding: 0.34rem 0.7rem;
    background: var(--dashboard-pill-bg);
    color: var(--dashboard-positive);
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1;
  }

  .dashboard-metric__label {
    font-size: 0.98rem;
    font-weight: 500;
    color: var(--dashboard-muted);
    margin-bottom: 0.35rem;
  }

  .dashboard-metric__value {
    font-size: 2.02rem;
    font-weight: 800;
    line-height: 1.08;
    letter-spacing: -0.04em;
    color: var(--dashboard-heading);
  }

  .dashboard-panel {
    padding: 1.9rem 1.72rem 1.45rem;
  }

  .dashboard-panel--orders,
  .dashboard-panel--events {
    min-height: 30.2rem;
  }

  .dashboard-panel--chart {
    min-height: 22rem;
  }

  .dashboard-panel__title {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.03em;
    color: var(--dashboard-heading);
  }

  .dashboard-panel__link {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    gap: 0.45rem;
    color: var(--dashboard-heading);
    font-size: 0.95rem;
    font-weight: 500;
  }

  .dashboard-panel__link svg {
    width: 1rem;
    height: 1rem;
  }

  .dashboard-orders-table {
    width: 100%;
    margin-top: 1.55rem;
    border-collapse: collapse;
  }

  .dashboard-orders-table thead th {
    text-align: left;
    padding: 0 1.15rem 1rem 0;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--dashboard-muted);
    letter-spacing: 0;
  }

  .dashboard-orders-table tbody td {
    padding: 1.1rem 1.15rem 1.1rem 0;
    font-size: 0.86rem;
    color: var(--dashboard-heading);
    border-top: 1px solid var(--dashboard-divider);
    vertical-align: middle;
  }

  .dashboard-orders-table th:last-child,
  .dashboard-orders-table td:last-child {
    padding-right: 0;
  }

  .dashboard-orders-table td:nth-child(3) {
    white-space: nowrap;
  }

  .dashboard-orders-empty,
  .dashboard-events-empty {
    display: grid;
    place-items: center;
    min-height: 14rem;
    color: var(--dashboard-muted);
    font-size: 0.95rem;
    text-align: center;
  }

  .dashboard-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 5.85rem;
    border-radius: 0.45rem;
    padding: 0.54rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1;
    color: #ffffff;
  }

  .dashboard-status--success { background: #0f8b1d; }
  .dashboard-status--warning { background: #f59e0b; }
  .dashboard-status--danger { background: #ef4444; }
  .dashboard-status--muted { background: #64748b; }

  .dashboard-events-list {
    display: grid;
    gap: 1.1rem;
    margin-top: 1.55rem;
  }

  .dashboard-event-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1rem 0.95rem;
    border-radius: 0.9rem;
    border: 1px solid var(--dashboard-border);
    background: var(--dashboard-subtle);
  }

  .dashboard-event-item__title {
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.25;
    color: var(--dashboard-heading);
    margin-bottom: 0.2rem;
  }

  .dashboard-event-item__date,
  .dashboard-event-item__meta {
    font-size: 0.78rem;
    color: var(--dashboard-muted);
  }

  .dashboard-event-item__stats {
    text-align: right;
    flex-shrink: 0;
  }

  .dashboard-event-item__count {
    font-size: 0.88rem;
    font-weight: 800;
    color: #7c3aed;
    line-height: 1.1;
    margin-bottom: 0.25rem;
  }

  .dashboard-chart-canvas {
    position: relative;
    height: 16.5rem;
    margin-top: 1.45rem;
  }

  @media (min-width: 1024px) {
    .dashboard-page .organiser-shell-header {
      padding: 1rem 1.45rem 1.1rem;
      background: rgba(255, 255, 255, 0.92) !important;
      border-color: var(--dashboard-border) !important;
      box-shadow: none !important;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }

    .dashboard-page .organiser-shell-header h1 {
      font-size: 1rem;
      font-weight: 800;
      color: var(--dashboard-heading) !important;
    }

    .dashboard-page .organiser-shell-header p {
      margin-top: 0.25rem;
      font-size: 0.95rem;
      color: var(--dashboard-muted) !important;
    }
  }

  @media (max-width: 1279px) {
    .dashboard-metrics {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 767px) {
    .dashboard-page .organiser-shell-main {
      padding: 1.1rem 1rem 2rem;
    }

    .dashboard-page .organiser-shell-header {
      padding-inline: 1rem;
      gap: 0.75rem;
    }

    .dashboard-metrics {
      grid-template-columns: 1fr;
    }

    .dashboard-panel,
    .dashboard-metric {
      padding-inline: 1.15rem;
    }

    .dashboard-panel__head,
    .dashboard-event-item {
      gap: 0.8rem;
    }

    .dashboard-panel__head {
      flex-wrap: wrap;
    }

    .dashboard-event-item {
      align-items: flex-start;
      flex-direction: column;
    }

    .dashboard-event-item__stats {
      text-align: left;
    }

    .dashboard-orders-table {
      min-width: 33rem;
    }

    .dashboard-create-button {
      padding: 0.78rem 0.95rem;
      font-size: 0.82rem;
    }
  }

  @media (max-width: 575px) {
    .dashboard-page .organiser-shell-header {
      padding: 0.9rem 0.85rem;
    }

    .dashboard-page .organiser-shell-main {
      padding: 0.9rem 0.85rem 1.5rem;
    }

    .dashboard-overview,
    .dashboard-metrics,
    .dashboard-grid {
      gap: 1rem;
    }

    .dashboard-metric,
    .dashboard-panel {
      border-radius: 1rem;
      padding: 1rem;
    }

    .dashboard-metric {
      min-height: auto;
    }

    .dashboard-metric__head {
      margin-bottom: 0.95rem;
    }

    .dashboard-metric__icon {
      width: 2.5rem;
      height: 2.5rem;
    }

    .dashboard-metric__delta {
      min-width: auto;
      padding-inline: 0.55rem;
    }

    .dashboard-metric__value {
      font-size: 1.6rem;
    }

    .dashboard-panel--orders,
    .dashboard-panel--events,
    .dashboard-panel--chart {
      min-height: auto;
    }

    .dashboard-panel__title {
      font-size: 0.95rem;
    }

    .dashboard-panel__link {
      font-size: 0.82rem;
    }

    .dashboard-orders-table {
      min-width: 30rem;
      margin-top: 1rem;
    }

    .dashboard-orders-table thead th,
    .dashboard-orders-table tbody td,
    .dashboard-event-item__date,
    .dashboard-event-item__meta {
      font-size: 0.75rem;
    }

    .dashboard-event-item__title {
      font-size: 0.86rem;
    }

    .dashboard-event-item__count {
      font-size: 0.82rem;
    }

    .dashboard-chart-canvas {
      height: 13.5rem;
      margin-top: 1rem;
    }
  }

  :root[data-theme='light'] .dashboard-page {
    background: #ffffff !important;
    --dashboard-surface: #ffffff;
    --dashboard-subtle: #ffffff;
    --dashboard-border: #d7dce3;
    --dashboard-divider: #dfe3e8;
    --dashboard-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    --dashboard-heading: #12182a;
    --dashboard-muted: #7a8295;
    --dashboard-pill-bg: #f1f1f3;
    --dashboard-icon-bg: #efe7fb;
    --dashboard-positive: #16a34a;
    --dashboard-chart-grid: #edf0f5;
    --dashboard-chart-text: #94a3b8;
    --dashboard-tooltip-bg: #ffffff;
    --dashboard-tooltip-border: #d7dce3;
    --dashboard-revenue-fill: rgba(124, 58, 237, 0.16);
    --dashboard-revenue-stroke: #7c3aed;
    --dashboard-orders-fill: rgba(124, 58, 237, 0.08);
    --dashboard-orders-stroke: #a855f7;
  }

  :root[data-theme='dark'] .dashboard-page {
    background: #060b14 !important;
    --dashboard-surface: #101827;
    --dashboard-subtle: #111c2e;
    --dashboard-border: #243043;
    --dashboard-divider: #202c3d;
    --dashboard-shadow: none;
    --dashboard-heading: #f8fafc;
    --dashboard-muted: #94a3b8;
    --dashboard-pill-bg: #1a2435;
    --dashboard-icon-bg: rgba(124, 58, 237, 0.18);
    --dashboard-positive: #4ade80;
    --dashboard-chart-grid: #1f2937;
    --dashboard-chart-text: #64748b;
    --dashboard-tooltip-bg: #0f172a;
    --dashboard-tooltip-border: #334155;
    --dashboard-revenue-fill: rgba(139, 92, 246, 0.22);
    --dashboard-revenue-stroke: #8b5cf6;
    --dashboard-orders-fill: rgba(168, 85, 247, 0.12);
    --dashboard-orders-stroke: #c084fc;
  }

  :root[data-theme='dark'] .dashboard-page .organiser-shell-header {
    background: rgba(10, 15, 27, 0.92) !important;
    box-shadow: none !important;
  }
</style>
@endsection

@section('content')
@php
    $statusMap = [
        'paid' => 'dashboard-status dashboard-status--success',
        'pending' => 'dashboard-status dashboard-status--warning',
        'refunded' => 'dashboard-status dashboard-status--danger',
        'partially_refunded' => 'dashboard-status dashboard-status--danger',
        'cancelled' => 'dashboard-status dashboard-status--muted',
        'failed' => 'dashboard-status dashboard-status--muted',
    ];
@endphp

<div class="dashboard-overview">
    <section class="dashboard-metrics">
        <article class="dashboard-metric">
            <div class="dashboard-metric__head">
                <div class="dashboard-metric__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 4v16M16.5 7.5c0-1.66-2.01-3-4.5-3s-4.5 1.34-4.5 3 2.01 3 4.5 3 4.5 1.34 4.5 3-2.01 3-4.5 3-4.5-1.34-4.5-3" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </div>
                <span class="dashboard-metric__delta">{{ $revenueGrowth >= 0 ? '+' : '-' }}{{ abs($revenueGrowth) }}%</span>
            </div>
            <div class="dashboard-metric__label">Total Revenue</div>
            <div class="dashboard-metric__value">{{ ticketly_money($totalRevenue) }}</div>
        </article>

        <article class="dashboard-metric">
            <div class="dashboard-metric__head">
                <div class="dashboard-metric__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4.75 8.25a2.25 2.25 0 0 0 2.25-2.25h10a2.25 2.25 0 0 0 2.25 2.25v7.5A2.25 2.25 0 0 0 17 18H7a2.25 2.25 0 0 0-2.25-2.25v-7.5Z" stroke-linejoin="round" stroke-width="1.8"></path>
                        <path d="M10 9.5h4M10 14.5h4" stroke-linecap="round" stroke-width="1.8"></path>
                    </svg>
                </div>
                <span class="dashboard-metric__delta">{{ $ticketGrowth >= 0 ? '+' : '-' }}{{ abs($ticketGrowth) }}%</span>
            </div>
            <div class="dashboard-metric__label">Tickets Sold</div>
            <div class="dashboard-metric__value">{{ number_format($totalTicketsSold) }}</div>
        </article>

        <article class="dashboard-metric">
            <div class="dashboard-metric__head">
                <div class="dashboard-metric__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="4.75" y="5.5" width="14.5" height="13.5" rx="2" stroke-width="1.8"></rect>
                        <path d="M8 3.75v3.5M16 3.75v3.5M4.75 10h14.5" stroke-linecap="round" stroke-width="1.8"></path>
                    </svg>
                </div>
                <span class="dashboard-metric__delta">+{{ $newEventsThisMonth }}</span>
            </div>
            <div class="dashboard-metric__label">Upcoming Events</div>
            <div class="dashboard-metric__value">{{ number_format($upcomingEvents) }}</div>
        </article>

        <article class="dashboard-metric">
            <div class="dashboard-metric__head">
                <div class="dashboard-metric__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 15.25 10 11l3 3 5-6.25" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                        <path d="M14 7.75h4v4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </div>
                <span class="dashboard-metric__delta">{{ $conversionGrowth >= 0 ? '+' : '-' }}{{ abs($conversionGrowth) }}%</span>
            </div>
            <div class="dashboard-metric__label">Conversion Rate</div>
            <div class="dashboard-metric__value">{{ $conversionRate }}%</div>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="dashboard-panel dashboard-panel--orders">
            <div class="dashboard-panel__head">
                <h2 class="dashboard-panel__title">Recent Orders</h2>
                <a href="{{ route('organiser.orders.index') }}" class="dashboard-panel__link">
                    <span>View All</span>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="M4.5 10h11M10.5 4.5 16 10l-5.5 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </a>
            </div>

            @if($recentBookings->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="dashboard-orders-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $booking)
                        @php
                            $statusClass = $statusMap[$booking->status] ?? 'dashboard-status dashboard-status--muted';
                            $statusLabel = $booking->status_badge['label'] ?? ucfirst(str_replace('_', ' ', $booking->status));
                        @endphp
                        <tr>
                            <td>{{ $booking->reference }}</td>
                            <td>{{ $booking->customer_name }}</td>
                            <td>{{ ticketly_money($booking->total) }}</td>
                            <td><span class="{{ $statusClass }}">{{ $statusLabel }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="dashboard-orders-empty">No orders yet.</div>
            @endif
        </article>

        <article class="dashboard-panel dashboard-panel--events">
            <div class="dashboard-panel__head">
                <h2 class="dashboard-panel__title">Upcoming Events</h2>
                <a href="{{ route('organiser.events.index') }}" class="dashboard-panel__link">
                    <span>View All</span>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                        <path d="M4.5 10h11M10.5 4.5 16 10l-5.5 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                    </svg>
                </a>
            </div>

            @if($upcomingEventsList->isNotEmpty())
            <div class="dashboard-events-list">
                @foreach($upcomingEventsList as $event)
                @php
                    $totalTickets = (int) ($event->total_capacity ?: $event->total_tier_quantity ?: 0);
                    $availableTickets = (int) ($event->available_tier_quantity ?? 0);
                    $soldTickets = $totalTickets > 0 ? max($totalTickets - $availableTickets, 0) : (int) ($event->total_bookings ?? 0);
                @endphp
                <a href="{{ route('organiser.events.show', $event->id) }}" class="dashboard-event-item">
                    <div>
                        <div class="dashboard-event-item__title">{{ $event->title }}</div>
                        <div class="dashboard-event-item__date">{{ ticketly_format_date($event->starts_at) }}</div>
                    </div>
                    <div class="dashboard-event-item__stats">
                        <div class="dashboard-event-item__count">{{ number_format($soldTickets) }}</div>
                        <div class="dashboard-event-item__meta">
                            @if($totalTickets > 0)
                            of {{ number_format($totalTickets) }} tickets
                            @else
                            tickets tracked
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="dashboard-events-empty">No upcoming events.</div>
            @endif
        </article>
    </section>

    <section>
        <article class="dashboard-panel dashboard-panel--chart">
            <div class="dashboard-panel__head">
                <div>
                    <h2 class="dashboard-panel__title">Revenue Overview</h2>
                    <p class="dashboard-event-item__meta">Existing chart retained for the last 12 months.</p>
                </div>
            </div>
            <div class="dashboard-chart-canvas">
                <canvas id="revenueChart"></canvas>
            </div>
        </article>
    </section>
</div>
@endsection

@section('scripts')
<script>
let revenueChartInstance = null;

function renderRevenueChart() {
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;

    if (revenueChartInstance) {
        revenueChartInstance.destroy();
    }

    const styles = getComputedStyle(document.body);
    const ctx = canvas.getContext('2d');

    revenueChartInstance = new Chart(ctx, {
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    type: 'bar',
                    label: 'Revenue ({{ ticketly_currency_symbol() }})',
                    data: {!! json_encode($chartRevenue) !!},
                    backgroundColor: styles.getPropertyValue('--dashboard-revenue-fill').trim(),
                    borderColor: styles.getPropertyValue('--dashboard-revenue-stroke').trim(),
                    borderWidth: 1.5,
                    borderRadius: 7,
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Orders',
                    data: {!! json_encode($chartOrders) !!},
                    borderColor: styles.getPropertyValue('--dashboard-orders-stroke').trim(),
                    backgroundColor: styles.getPropertyValue('--dashboard-orders-fill').trim(),
                    borderWidth: 2.25,
                    pointBackgroundColor: styles.getPropertyValue('--dashboard-orders-stroke').trim(),
                    pointBorderColor: styles.getPropertyValue('--dashboard-surface').trim(),
                    pointBorderWidth: 2,
                    pointRadius: 3.5,
                    pointHoverRadius: 5.5,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: styles.getPropertyValue('--dashboard-tooltip-bg').trim(),
                    borderColor: styles.getPropertyValue('--dashboard-tooltip-border').trim(),
                    borderWidth: 1,
                    titleColor: styles.getPropertyValue('--dashboard-heading').trim(),
                    bodyColor: styles.getPropertyValue('--dashboard-muted').trim(),
                    padding: 10,
                    callbacks: {
                        label: function (item) {
                            return item.dataset.yAxisID === 'y'
                                ? ' ' + @js(ticketly_currency_symbol()) + item.parsed.y.toFixed(2)
                                : ' ' + item.parsed.y + ' orders';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: styles.getPropertyValue('--dashboard-chart-grid').trim() },
                    ticks: {
                        color: styles.getPropertyValue('--dashboard-chart-text').trim(),
                        font: { size: 11 }
                    }
                },
                y: {
                    type: 'linear',
                    position: 'left',
                    grid: { color: styles.getPropertyValue('--dashboard-chart-grid').trim() },
                    ticks: {
                        color: styles.getPropertyValue('--dashboard-chart-text').trim(),
                        font: { size: 11 },
                        callback: function (value) {
                            return @js(ticketly_currency_symbol()) + value;
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        color: styles.getPropertyValue('--dashboard-chart-text').trim(),
                        font: { size: 11 }
                    }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', renderRevenueChart);
window.addEventListener('ticketly:theme-changed', renderRevenueChart);
</script>
@endsection
