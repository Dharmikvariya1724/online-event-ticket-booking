@if(session('success'))
    <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm">
        <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-emerald-900">Success</p>
            <p class="mt-1 text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if(session('info'))
    <div class="flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900 shadow-sm">
        <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-sky-900">Info</p>
            <p class="mt-1 text-sky-800">{{ session('info') }}</p>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-900 shadow-sm">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 005.1 19h13.8a2 2 0 001.73-2.84l-7.1-12.3a2 2 0 00-3.46 0z"></path>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-rose-900">Please fix the following</p>
                <div class="mt-2 space-y-1.5">
                    @foreach($errors->all() as $error)
                        <div class="flex items-start gap-2 text-rose-800">
                            <span class="pt-0.5 text-rose-500">&bull;</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
