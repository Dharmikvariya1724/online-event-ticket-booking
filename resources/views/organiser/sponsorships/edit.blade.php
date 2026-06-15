@extends('layouts.organiser')
@section('title', 'Edit Sponsorship')
@section('page-title', 'Edit Sponsorship')
@section('page-subtitle', $event->title)
@section('hide-default-alerts', '1')

@section('content')
<div class="max-w-2xl space-y-5">
    @include('organiser.sponsorships._alerts')

    <div>
        <a href="{{ url('/organiser/events') }}" class="text-sm text-gray-400 transition-colors hover:text-white">&larr; Back to Events</a>
    </div>

    <div class="rounded-2xl border border-gray-800 bg-gray-900 p-5 sm:p-8">
        @include('organiser.sponsorships._form', [
            'action' => route('organiser.sponsorships.update', [$event->id, $sponsorship->id]),
            'event' => $event,
            'sponsorship' => $sponsorship,
            'method' => 'PUT',
            'submitLabel' => 'Save Sponsorship',
        ])
    </div>
</div>
@endsection
