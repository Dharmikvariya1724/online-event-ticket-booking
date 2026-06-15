@extends('layouts.admin')

@section('title', 'Email Logs')
@section('page-title', 'Email Monitoring')
@section('page-subtitle', 'View and retry email deliveries')

@section('content')
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
  <form method="GET" class="flex flex-col md:flex-row gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipient or subject"
      class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    <select name="status" class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Statuses</option>
      <option value="sent" @selected(request('status')==='sent')>Sent</option>
      <option value="failed" @selected(request('status')==='failed')>Failed</option>
      <option value="queued" @selected(request('status')==='queued')>Queued</option>
    </select>
    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Filter</button>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-gray-400 text-xs uppercase">
        <tr>
          <th class="text-left py-3">Recipient</th>
          <th class="text-left py-3">Subject</th>
          <th class="text-left py-3">Status</th>
          <th class="text-left py-3">Date</th>
          <th class="text-right py-3">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($logs as $log)
        <tr>
          <td class="py-3 text-white font-semibold">{{ $log->to }}</td>
          <td class="py-3 text-gray-300">{{ $log->subject ?? $log->mailable }}</td>
          <td class="py-3 text-gray-400">{{ ucfirst($log->status) }}</td>
          <td class="py-3 text-gray-500">{{ ticketly_format_datetime($log->created_at) }}</td>
          <td class="py-3 text-right">
            @if($log->status === 'failed')
            <form method="POST" action="{{ route('admin.emails.retry', $log->id) }}">
              @csrf
              <button class="text-emerald-400 hover:text-emerald-300 text-sm">Retry</button>
            </form>
            @else
            <span class="text-gray-500 text-xs">—</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="py-6 text-center text-gray-500">No email logs found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
