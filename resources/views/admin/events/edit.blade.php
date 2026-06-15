@extends('layouts.admin')

@section('title', 'Edit Event')
@section('page-title', 'Edit Event')
@section('page-subtitle', $event->title)

@section('content')
@php
  $inputBaseClass = 'w-full bg-gray-800 border rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:ring-2';
  $inputClasses = fn (string $field) => $inputBaseClass . ' ' . ($errors->has($field) ? 'border-rose-500 focus:ring-rose-500' : 'border-gray-700 focus:ring-emerald-500');
  $descriptionValue = old('description') ?? strip_tags($event->description ?? '');
  $parkingValue = old('parking_info') ?? trim(html_entity_decode(strip_tags($event->parking_info ?? '')));
  $refundValue = old('refund_policy') ?? trim(html_entity_decode(strip_tags($event->refund_policy ?? '')));
@endphp

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
  <form id="admin-event-form" method="POST" action="{{ route('admin.events.update', $event->id) }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">Title</label>
        <input type="text" name="title" value="{{ old('title', $event->title) }}" maxlength="50" class="{{ $inputClasses('title') }}" required>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('title')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">Category</label>
        <select name="category" class="{{ $inputClasses('category') }}" required>
          @foreach(\App\Models\Event::CATEGORIES as $cat)
            <option value="{{ $cat }}" @selected(old('category', $event->category) === $cat)>{{ $cat }}</option>
          @endforeach
        </select>
        @error('category')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">Start</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\\TH:i')) }}" class="{{ $inputClasses('starts_at') }}" required>
        @error('starts_at')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">End</label>
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\\TH:i')) }}" class="{{ $inputClasses('ends_at') }}" required>
        @error('ends_at')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">Ticket Validation Start</label>
        <input type="datetime-local" name="ticket_validation_starts_at" value="{{ old('ticket_validation_starts_at', $event->ticketValidationStartsAt()?->format('Y-m-d\\TH:i')) }}" class="{{ $inputClasses('ticket_validation_starts_at') }}" required>
        @error('ticket_validation_starts_at')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">Ticket Validation End</label>
        <input type="datetime-local" name="ticket_validation_ends_at" value="{{ old('ticket_validation_ends_at', $event->ticketValidationEndsAt()?->format('Y-m-d\\TH:i')) }}" class="{{ $inputClasses('ticket_validation_ends_at') }}" required>
        @error('ticket_validation_ends_at')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">Venue Name</label>
        <input type="text" name="venue_name" value="{{ old('venue_name', $event->venue_name) }}" maxlength="50" class="{{ $inputClasses('venue_name') }}" required>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('venue_name')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">City</label>
        <input type="text" name="city" value="{{ old('city', $event->city) }}" maxlength="50" class="{{ $inputClasses('city') }}" required>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('city')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div class="md:col-span-2">
        <label class="text-xs uppercase tracking-wider text-gray-400">Address</label>
        <input type="text" name="venue_address" value="{{ old('venue_address', $event->venue_address) }}" maxlength="300" class="{{ $inputClasses('venue_address') }}" required>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('venue_address')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-xs uppercase tracking-wider text-gray-400">Postcode</label>
        <input type="text" name="postcode" value="{{ old('postcode', $event->postcode) }}" maxlength="10" class="{{ $inputClasses('postcode') }}">
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('postcode')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div class="md:col-span-2">
        <label class="text-xs uppercase tracking-wider text-gray-400">Short Description</label>
        <textarea name="short_description" rows="3" maxlength="255" class="{{ $inputClasses('short_description') }}">{{ old('short_description', $event->short_description) }}</textarea>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('short_description')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div class="md:col-span-2">
        <label class="text-xs uppercase tracking-wider text-gray-400">Description</label>
        <textarea name="description" rows="6" maxlength="5000" class="{{ $inputClasses('description') }}">{{ $descriptionValue }}</textarea>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('description')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div class="md:col-span-2">
        <label class="text-xs uppercase tracking-wider text-gray-400">Parking Info</label>
        <textarea name="parking_info" rows="3" maxlength="1000" class="{{ $inputClasses('parking_info') }}">{{ $parkingValue }}</textarea>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('parking_info')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div class="md:col-span-2">
        <label class="text-xs uppercase tracking-wider text-gray-400">Refund Policy</label>
        <textarea name="refund_policy" rows="3" maxlength="1000" class="{{ $inputClasses('refund_policy') }}">{{ $refundValue }}</textarea>
        <p class="input-error mt-1 text-xs text-red-500 hidden"></p>
        @error('refund_policy')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
      </div>

      <div class="md:col-span-2">
        <label class="text-xs uppercase tracking-wider text-gray-400">Banner</label>
        <input type="file" name="banner" class="{{ $inputClasses('banner') }}">
        @error('banner')
          <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror

        @if($event->banner_url)
          <div class="mt-3">
            <img src="{{ $event->banner_url }}" alt="Current banner" class="max-h-48 rounded-xl border border-gray-800">
          </div>
        @endif
      </div>
    </div>

    <div class="flex items-center gap-3">
      <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $event->is_featured))>
      <span class="text-sm text-gray-400">Featured event</span>
    </div>

    <div class="flex justify-end gap-3">
      <a href="{{ route('admin.events.show', $event->id) }}" class="px-4 py-2 rounded-xl border border-gray-700 text-gray-300 text-sm">Cancel</a>
      <button class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Save Changes</button>
    </div>
  </form>
</div>
@endsection

@section('scripts')
<script>
const adminEventForm = document.getElementById('admin-event-form');

if (adminEventForm) {
    adminEventForm.querySelectorAll('input[maxlength], textarea[maxlength]').forEach((field) => {
        const errorEl = field.parentElement?.querySelector('.input-error');

        if (!errorEl) {
            return;
        }

        const toggleMaxlengthError = () => {
            const maxLength = Number(field.getAttribute('maxlength'));

            if (maxLength && field.value.length >= maxLength) {
                errorEl.textContent = `Maximum ${maxLength} characters allowed`;
                errorEl.classList.remove('hidden');
                return;
            }

            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        };

        field.addEventListener('input', toggleMaxlengthError);
        field.addEventListener('blur', toggleMaxlengthError);
        toggleMaxlengthError();
    });
}
</script>
@endsection
