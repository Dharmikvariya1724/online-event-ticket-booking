@extends('layouts.organiser')
@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')
@section('page-subtitle', 'Update your organiser details')

@section('content')
<div class="max-w-2xl">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
    @if($errors->any())
    <div class="bg-red-900/40 border border-red-700/50 rounded-xl p-4 mb-6">
      @foreach($errors->all() as $e)<div class="text-red-300 text-sm">• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form action="{{ route('organiser.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
      @csrf @method('PUT')

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Your Name *</label>
          <input type="text" name="name" value="{{ old('name', $organiser->name) }}" required
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Company Name *</label>
          <input type="text" name="company_name" value="{{ old('company_name', $organiser->company_name) }}" required
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Email Address *</label>
        <input type="email" name="email" value="{{ old('email', $organiser->email) }}" required
               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Phone</label>
          <input type="tel" name="phone" id="phone" value="{{ old('phone', $organiser->phone) }}"
                 maxlength="11" minlength="11" inputmode="numeric" pattern="07[0-9]{9}" title="Enter exactly 11 digits starting with 07" required
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="07123456789">
          <p class="text-xs text-gray-500 mt-1">Enter exactly 11 digits starting with 07. Numbers only, with no spaces or symbols.</p>
          <p class="text-red-500 text-xs mt-1 hidden input-error"></p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Website</label>
          <input type="url" name="website" value="{{ old('website', $organiser->website) }}"
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Bio</label>
        <textarea name="bio" rows="3" maxlength="2000"
                  class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Tell attendees about your organisation...">{{ old('bio', $organiser->bio) }}</textarea>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Company Logo</label>
        @if($organiser->logo_url)
        <div class="flex items-center gap-3 mb-3">
          <img src="{{ $organiser->logo_url }}" alt="" class="w-16 h-16 rounded-xl object-cover border border-gray-700">
          <span class="text-xs text-gray-500">Current logo</span>
        </div>
        @endif
        <input type="file" name="logo" accept="image/*"
               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-semibold file:text-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p class="text-xs text-gray-600 mt-1">JPG, PNG, WebP · Max 2MB</p>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="px-6 py-3 text-sm font-bold text-white rounded-xl transition-all" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Save Changes</button>
        <a href="{{ route('organiser.profile.show') }}" class="px-6 py-3 text-sm font-semibold text-gray-400 bg-gray-800 hover:bg-gray-700 rounded-xl transition-colors">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
const phoneInput = document.getElementById('phone');

if (phoneInput) {
    const phoneError = phoneInput.parentElement.querySelector('.input-error');
    const invalidFormatMessage = 'Phone number must be exactly 11 digits and contain numbers only.';
    const invalidPrefixMessage = 'Phone number must start with 07.';

    const sanitizePhoneValue = (value) => value.replace(/\D/g, '').slice(0, 11);

    const updatePhoneError = (value) => {
        if (!phoneError) {
            return;
        }

        if (value.length === 0) {
            phoneInput.setCustomValidity('');
            phoneError.classList.add('hidden');
            phoneError.textContent = '';
            return;
        }

        if (value.length >= 2 && !value.startsWith('07')) {
            phoneInput.setCustomValidity(invalidPrefixMessage);
            phoneError.textContent = invalidPrefixMessage;
            phoneError.classList.remove('hidden');
            return;
        }

        if (value.length !== 11) {
            phoneInput.setCustomValidity(invalidFormatMessage);
            phoneError.textContent = invalidFormatMessage;
            phoneError.classList.remove('hidden');
            return;
        }

        phoneInput.setCustomValidity('');
        phoneError.classList.add('hidden');
        phoneError.textContent = '';
    };

    const handlePhoneInput = () => {
        const sanitizedValue = sanitizePhoneValue(phoneInput.value);
        if (phoneInput.value !== sanitizedValue) {
            phoneInput.value = sanitizedValue;
        }

        updatePhoneError(phoneInput.value);
    };

    phoneInput.addEventListener('beforeinput', function (event) {
        if (event.data && /\D/.test(event.data)) {
            event.preventDefault();
        }
    });

    phoneInput.addEventListener('keydown', function (event) {
        if (event.key === ' ') {
            event.preventDefault();
        }
    });

    phoneInput.addEventListener('paste', function (event) {
        event.preventDefault();
        const pastedText = event.clipboardData?.getData('text') ?? '';
        const sanitizedValue = sanitizePhoneValue(pastedText);
        const start = this.selectionStart ?? this.value.length;
        const end = this.selectionEnd ?? this.value.length;
        const nextValue = sanitizePhoneValue(
            this.value.slice(0, start) + sanitizedValue + this.value.slice(end)
        );

        this.value = nextValue;
        updatePhoneError(this.value);
    });

    phoneInput.addEventListener('input', handlePhoneInput);
    phoneInput.addEventListener('invalid', function () {
        updatePhoneError(this.value);
    });

    updatePhoneError(phoneInput.value);
}
</script>
@endsection
