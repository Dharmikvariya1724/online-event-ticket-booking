@extends('layouts.organiser')
@section('title', 'Create Promo Code')
@section('page-title', 'Create Promo Code')
@section('page-subtitle', 'Set up a discount code for your events')

@section('content')
<div class="max-w-lg">
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
  @if($errors->any())
  <div class="bg-red-900/40 border border-red-700/50 rounded-xl p-4 mb-5">
    @foreach($errors->all() as $e)<div class="text-red-300 text-sm">• {{ $e }}</div>@endforeach
  </div>
  @endif
  <form action="{{ route('organiser.promos.store') }}" method="POST" class="space-y-4">
    @csrf
    <div>
      <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Code *</label>
      <input type="text" name="code" value="{{ old('code') }}" required maxlength="30"
             class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 uppercase tracking-widest font-mono" placeholder="SUMMER25" style="text-transform:uppercase">
      <p class="text-xs text-gray-600 mt-1">Alphanumeric only, no spaces. Will be uppercased.</p>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Discount Type *</label>
        <select name="type" id="promo-type" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="updateValueLabel()">
          <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
          <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount ({{ ticketly_currency_symbol() }})</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Value * <span id="value-unit" class="normal-case font-normal text-gray-600">(%)</span></label>
        <input type="number" name="value" value="{{ old('value') }}" required step="0.01" min="0.01"
               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. 20">
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Max Discount ({{ ticketly_currency_symbol() }}) <span class="text-gray-600 font-normal normal-case">optional</span></label>
        <input type="number" name="max_discount" value="{{ old('max_discount') }}" step="0.01" min="0"
               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g. 50.00">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Max Uses <span class="text-gray-600 font-normal normal-case">optional</span></label>
        <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1"
               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Unlimited">
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Expires At <span class="text-gray-600 font-normal normal-case">optional</span></label>
        <input type="date" name="expires_at" value="{{ old('expires_at') }}"
               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Restrict to Event <span class="text-gray-600 font-normal normal-case">optional</span></label>
        <select name="event_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All events</option>
          @foreach($events as $event)
          <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>{{ Str::limit($event->title, 35) }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-indigo-600">
      <label for="is_active" class="text-sm text-gray-300">Active immediately</label>
    </div>
    <div class="flex gap-3 pt-2">
      <button type="submit" class="px-6 py-3 text-sm font-extrabold text-white rounded-xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Create Promo Code</button>
      <a href="{{ route('organiser.promos.index') }}" class="px-5 py-3 text-sm font-semibold text-gray-400 bg-gray-800 hover:bg-gray-700 rounded-xl transition-colors">Cancel</a>
    </div>
  </form>
</div>
</div>
@endsection
@section('scripts')
<script>
function updateValueLabel() {
  const type = document.getElementById('promo-type').value;
  document.getElementById('value-unit').textContent = type === 'percentage' ? '(%)' : '({{ ticketly_currency_symbol() }})';
}
</script>
@endsection
