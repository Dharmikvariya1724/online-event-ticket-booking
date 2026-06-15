@extends('layouts.admin')

@section('title', 'Payouts')
@section('page-title', 'Payouts')
@section('page-subtitle', 'Settlement window: ' . $settlementDays . ' day(s)')

@section('content')
<div class="grid gap-6">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">Organiser Balances</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-gray-400 text-xs uppercase">
          <tr>
            <th class="text-left py-3">Organiser</th>
            <th class="text-left py-3">Eligible</th>
            <th class="text-left py-3">Pending</th>
            <th class="text-left py-3">Paid Out</th>
            <th class="text-left py-3">Available</th>
            <th class="text-right py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @foreach($organisers as $org)
          <tr>
            <td class="py-3 text-white font-semibold">{{ $org->company_name }}</td>
            <td class="py-3 text-gray-300">{{ ticketly_money($org->eligible_amount) }}</td>
            <td class="py-3 text-gray-400">{{ ticketly_money($org->pending_amount) }}</td>
            <td class="py-3 text-gray-400">{{ ticketly_money($org->paid_amount) }}</td>
            <td class="py-3 text-gray-300">{{ ticketly_money($org->available_amount) }}</td>
            <td class="py-3 text-right">
              <form method="POST" action="{{ route('admin.payouts.trigger', $org->id) }}" class="flex items-center justify-end gap-2" data-confirm="Trigger payout for this organiser?">
                @csrf
                <input type="number" step="0.01" name="amount" value="{{ number_format($org->available_amount, 2, '.', '') }}" class="w-28 bg-gray-800 border border-gray-700 rounded-lg px-2 py-1 text-xs text-white">
                <button class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Trigger</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">Payout History</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-gray-400 text-xs uppercase">
          <tr>
            <th class="text-left py-3">Organiser</th>
            <th class="text-left py-3">Amount</th>
            <th class="text-left py-3">Status</th>
            <th class="text-right py-3">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @forelse($payouts as $payout)
          <tr>
            <td class="py-3 text-white font-semibold">{{ $payout->organiser?->company_name }}</td>
            <td class="py-3 text-gray-300">{{ ticketly_money($payout->amount) }}</td>
            <td class="py-3 text-gray-400">{{ ucfirst($payout->status) }}</td>
            <td class="py-3 text-gray-500 text-right">{{ ticketly_format_date($payout->created_at) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="py-6 text-center text-gray-500">No payouts recorded.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $payouts->links() }}</div>
  </div>
</div>
@endsection
