<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900 shadow-sm">
        Sponsorships are optional. Add one sponsor at a time for <span class="font-semibold text-indigo-950">{{ $event->title }}</span>.
    </div>

    <div>
        <label for="sponsor-name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-400">Sponsor Name *</label>
        <input
            id="sponsor-name"
            type="text"
            name="name"
            value="{{ old('name', $sponsorship?->name ?? '') }}"
            maxlength="100"
            required
            class="w-full rounded-xl border bg-gray-800 px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 {{ $errors->has('name') ? 'border-rose-500/60 focus:ring-rose-500' : 'border-gray-700 focus:ring-indigo-500' }}"
            placeholder="Enter sponsor name">
        <p class="mt-1 text-xs text-gray-500">Maximum 100 characters.</p>
        @error('name')
            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div>
        <label for="sponsor-photo" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-400">Sponsor Photo</label>
        <input
            id="sponsor-photo"
            type="file"
            name="photo"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            class="block w-full rounded-xl border border-dashed bg-gray-800 px-4 py-3 text-sm text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500 {{ $errors->has('photo') ? 'border-rose-500/60' : 'border-gray-700' }}">
        <p class="mt-1 text-xs text-gray-500">Optional. Allowed types: jpg, jpeg, png, webp.</p>
        @error('photo')
            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $message }}
            </div>
        @enderror
    </div>

    @if($sponsorship?->photo_url)
        <div class="rounded-2xl border border-gray-800 bg-gray-950/70 p-4">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Current Photo</p>
            <img src="{{ asset('storage/app/public/sponsorships/'.basename($sponsorship->photo_url)) }}" alt="{{ $sponsorship->name }}" class="h-24 w-24 rounded-2xl object-cover ring-1 ring-gray-700">
        </div>
    @endif

    <div class="flex flex-wrap gap-3 pt-2">
        <button type="submit" class="rounded-xl px-6 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('organiser.sponsorships.index', $event->id) }}" class="rounded-xl bg-gray-800 px-5 py-3 text-sm font-semibold text-gray-300 transition-colors hover:bg-gray-700">
            Cancel
        </a>
    </div>
</form>
