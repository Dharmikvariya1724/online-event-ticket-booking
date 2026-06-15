@extends('layouts.organiser')
@section('title', 'Edit Tier')
@section('page-title', 'Edit Ticket Tier')
@section('page-subtitle', $event->title . ' – ' . $tier->name)

@section('content')
<div class="max-w-xl w-full">
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 sm:p-8">
  @if($errors->any())
  <div class="bg-red-900/40 border border-red-700/50 rounded-xl p-4 mb-5">
    @foreach($errors->all() as $e)<div class="text-red-300 text-sm">• {{ $e }}</div>@endforeach
  </div>
  @endif
  <form action="{{ route('organiser.tiers.update', [$event->id, $tier->id]) }}" method="POST" class="space-y-4">
    @csrf @method('PUT')
    <div><label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Tier Name *</label>
    <input type="text" name="name" value="{{ old('name', $tier->name) }}" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
    <div><label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Description</label>
    <textarea name="description" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description', $tier->description) }}</textarea></div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div><label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Price ({{ ticketly_currency_symbol() }}) *</label>
      <input type="number" name="price" value="{{ old('price', $tier->price) }}" step="0.01" min="0" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Total Quantity *</label>
        <input type="number" name="total_quantity" value="{{ old('total_quantity', $tier->total_quantity) }}" min="{{ $tier->sold_quantity }}" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p class="text-xs text-gray-600 mt-1">{{ $tier->sold_quantity }} already sold – cannot go below this.</p>
      </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Min Per Order</label>
        <input type="number" name="min_per_order" value="{{ old('min_per_order', $tier->min_per_order) }}" min="1" step="1" inputmode="numeric" required aria-describedby="min_per_order-error" class="w-full bg-gray-800 border {{ $errors->has('min_per_order') ? 'border-rose-500' : 'border-gray-700' }} rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p id="min_per_order-error" class="mt-2 text-sm text-rose-400 {{ $errors->has('min_per_order') ? '' : 'hidden' }}">{{ $errors->first('min_per_order') }}</p>
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Max Per Order</label>
        <input type="number" name="max_per_order" value="{{ old('max_per_order', $tier->max_per_order) }}" min="1" max="20" step="1" inputmode="numeric" required aria-describedby="max_per_order-error" class="w-full bg-gray-800 border {{ $errors->has('max_per_order') ? 'border-rose-500' : 'border-gray-700' }} rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p id="max_per_order-error" class="mt-2 text-sm text-rose-400 {{ $errors->has('max_per_order') ? '' : 'hidden' }}">{{ $errors->first('max_per_order') }}</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $tier->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-indigo-600 focus:ring-indigo-500">
      <label for="is_active" class="text-sm text-gray-300">Active (visible for purchase)</label>
    </div>
    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
      <button type="submit" class="px-6 py-3 text-sm font-extrabold text-white rounded-xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Save Changes</button>
      <a href="{{ route('organiser.tiers.index', $event->id) }}" class="px-5 py-3 text-sm font-semibold text-gray-400 bg-gray-800 hover:bg-gray-700 rounded-xl transition-colors">Cancel</a>
    </div>
  </form>
</div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rules = {
    min_per_order: {
      min: 1,
      required: 'Minimum per order is required.',
      invalid: 'Minimum per order must be at least 1.',
    },
    max_per_order: {
      min: 1,
      max: 20,
      required: 'Max per order is required.',
      invalid: 'Max per order is invalid.',
      tooHigh: 'Max per order must be maximum 20.',
    },
  };

  Object.entries(rules).forEach(function ([fieldName, rule]) {
    const field = document.querySelector('input[name="' + fieldName + '"]');
    const error = document.getElementById(fieldName + '-error');

    if (!field || !error) {
      return;
    }

    const syncValidationState = function () {
      const rawValue = field.value.trim();
      let message = '';
      const numericValue = Number(rawValue);

      if (rawValue !== '' && !Number.isFinite(numericValue)) {
        message = rule.invalid;
      } else if (rawValue !== '' && numericValue < rule.min) {
        message = rule.invalid;
      } else if (rawValue !== '' && numericValue > rule.max) {
        message = rule.tooHigh;
      }

      field.setCustomValidity(message);
      error.textContent = message;
      error.classList.toggle('hidden', message === '');
    };

    field.addEventListener('input', syncValidationState);
    field.addEventListener('blur', syncValidationState);
    field.addEventListener('invalid', function () {
      const rawValue = field.value.trim();
      const numericValue = Number(rawValue);
      let message = rule.invalid;

      if (rawValue === '') {
        message = rule.required;
      } else if (Number.isFinite(numericValue) && numericValue > rule.max) {
        message = rule.tooHigh;
      }

      field.setCustomValidity(message);
      error.textContent = message;
      error.classList.remove('hidden');
    });

    syncValidationState();
  });
});
</script>
@endsection
