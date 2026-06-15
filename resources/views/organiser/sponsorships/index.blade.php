@extends('layouts.organiser')
@section('title', 'Sponsorships')
@section('page-title', 'Sponsorships')
@section('page-subtitle', $event->title)
@section('hide-default-alerts', '1')

@section('content')
<div class="mx-auto flex w-full max-w-6xl flex-col gap-5">
    @include('organiser.sponsorships._alerts')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ url('/organiser/events') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-gray-400 transition-colors hover:text-white">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-700 bg-transparent text-gray-400 transition-colors group-hover:border-gray-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6 9 12l6 6"></path>
                </svg>
            </span>
            <span>Back to event</span>
        </a>

        <a href="{{ route('organiser.sponsorships.create', $event->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white sm:w-auto" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
            + Add Sponsorship
        </a>
    </div>

    @if($sponsorships->isEmpty())
        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-10 text-center sm:p-16">
            <div class="mb-4 text-5xl">SP</div>
            <h3 class="mb-2 text-lg font-bold text-white">No sponsors yet</h3>
            <p class="mb-5 text-sm text-gray-400">Add sponsor logos for this event to showcase your partners on the event page.</p>
            <a href="{{ route('organiser.sponsorships.create', $event->id) }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                Add First Sponsor
            </a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($sponsorships as $sponsorship)
                @php
                    $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sponsorship->name, 0, 2));
                @endphp
                <div class="flex h-full flex-col rounded-2xl border border-gray-800 bg-gray-900 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-800 bg-gradient-to-br from-indigo-500 to-violet-500 text-lg font-extrabold text-white">
                                @if($sponsorship->photo_url)
                                    <img src="{{ asset('storage/app/public/sponsorships/'.basename($sponsorship->photo_url)) }}" alt="{{ $sponsorship->name }}" class="h-full w-full object-cover">
                                @else
                                    <span>{{ $initials }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-bold text-white">{{ $sponsorship->name }}</h3>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-gray-500">
                                    {{ $sponsorship->photo_url ? 'Logo uploaded' : 'Name only' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-2 pt-5 sm:flex-row" style="border-top:1px solid rgba(55,65,81,0.8)">
                        <a href="{{ route('organiser.sponsorships.edit', [$event->id, $sponsorship->id]) }}" class="inline-flex w-full items-center justify-center rounded-lg border border-indigo-500/30 px-3 py-2 text-xs font-semibold text-indigo-400 transition-colors hover:bg-indigo-600/10 sm:w-auto">
                            Edit
                        </a>
                        <form action="{{ route('organiser.sponsorships.destroy', [$event->id, $sponsorship->id]) }}" method="POST" class="w-full sm:w-auto" data-confirm="Delete this sponsorship?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-lg border border-red-700/50 px-3 py-2 text-xs font-semibold text-red-400 transition-colors hover:bg-red-900/20 sm:w-auto">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
