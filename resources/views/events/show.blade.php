@extends('layouts.app')
@section('title', $event->title)

@section('content')
@php
    $heroImage = $event->banner_url ?: 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1600&h=900&fit=crop';
    $venueName = $event->venue_name ?: 'Central Park Amphitheater';
    $venueLine = collect([$event->venue_address, $event->city, $event->postcode])->filter()->unique()->implode(', ');
    $mapLocation = collect([$event->venue_name, $event->venue_address, $event->city, $event->postcode])->filter()->unique()->implode(', ');
    $mapQuery = rawurlencode($mapLocation ?: 'Central Park Amphitheater, New York, NY');
    $aboutSource = (string) ($event->description ?? '');
    $aboutHtml = trim(strip_tags(html_entity_decode($aboutSource))) !== ''
        ? $aboutSource
        : (filled($event->short_description)
            ? '<p>' . e($event->short_description) . '</p>'
            : '<p>Join us for a standout live experience with premium production, unforgettable performances, and an incredible crowd.</p>');
    $organiserName = $event->organiser->name ?? 'Premier Events Co.';
    $organiserBio = $event->organiser->bio ?: 'We create unforgettable experiences for all audiences.';
    $organiserLogo = $event->organiser->logo_url ?? null;
    $organiserInitials = $event->organiser->initials ?? 'PE';
    $ticketItemsOld = collect($selectedTicketItems ?? old('items', []));
    $activeReservationToken = $activeReservation?->token;
    $activeReservationSeconds = $activeReservation?->secondsRemaining() ?? 0;
    $reservedQuantities = $ticketItemsOld
        ->mapWithKeys(fn ($item) => [(int) data_get($item, 'ticket_tier_id') => (int) data_get($item, 'quantity', 0)]);
    $eventStartLabel = ticketly_format_compact_datetime($event->starts_at);
    $eventEndLabel = $event->starts_at && $event->ends_at && $event->starts_at->isSameDay($event->ends_at)
        ? $event->starts_at->format('d M Y') . ', ' . $event->ends_at->format('g:i A')
        : ticketly_format_compact_datetime($event->ends_at);
    $refundPolicySource = (string) ($event->refund_policy ?? '');
    $refundPolicyHtml = trim(strip_tags(html_entity_decode($refundPolicySource))) !== ''
        ? $refundPolicySource
        : '<p>Full refund available up to 7 days before the event. 50% refund up to 3 days before. No refunds within 72 hours of the event.</p>';
    $lineupItems = collect($event->performer_lineup ?? [])->filter(fn ($item) => filled(data_get($item, 'name')))->values();
    if ($lineupItems->isEmpty()) {
        $lineupItems = collect([
            ['time' => '2:00 PM', 'name' => 'Gates Open'],
            ['time' => '3:00 PM', 'name' => 'Opening Act - The Rising Stars'],
            ['time' => '5:00 PM', 'name' => 'Main Stage - Electronic Beats'],
            ['time' => '8:00 PM', 'name' => 'Headliner Performance'],
        ]);
    }
    $faqItems = [
        ["What's included in the ticket?", 'Your ticket includes event entry and access to the benefits of the tier you select.'],
        ['Is there parking and transport available?', $event->parking_info ?: 'Parking and transport details will be shared with ticket holders before the event.'],
        ['What is the minimum age requirement to attend the event?', $event->age_requirement ? "This event is {$event->age_requirement}+" : 'Age must be 18+ or for under 18 must be accompanied by a Parent or Guardian.'],
    ];
    $interestedCount = max(1200, (int) $event->ticketTiers->sum('sold_quantity') * 8);
    $hasSelectableTickets = !$event->isCancelled() && !$event->starts_at->isPast() && $event->ticketTiers->isNotEmpty();
@endphp

<div class="event-page-shell bg-[#ffffff] text-slate-900">
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ $heroImage }}');"></div>
        <div class="absolute inset-0"></div>
        <div class="relative mx-auto max-w-[1440px] px-2 pb-28 pt-4 sm:px-4 sm:pb-40 sm:pt-5 lg:px-4 lg:pb-44">
            <div class="flex items-start justify-between gap-4">
                <button type="button" onclick="history.back()" class="inline-flex items-center gap-2 rounded-xl bg-white/95 px-4 py-2.5 text-sm font-medium text-slate-800 shadow-sm ring-1 ring-slate-200">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"><path d="M12.5 4.5 7 10l5.5 5.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                    <span>Back</span>
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" id="share-event-button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/95 text-slate-700 shadow-sm ring-1 ring-slate-200" aria-label="Share event">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="M15 8 9 12l6 4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                            <circle cx="18" cy="6" r="2.25" stroke-width="1.8"/>
                            <circle cx="6" cy="12" r="2.25" stroke-width="1.8"/>
                            <circle cx="18" cy="18" r="2.25" stroke-width="1.8"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div aria-hidden="true" class="invisible mx-auto mt-12 max-w-5xl text-center text-white sm:mt-16">
                <div class="inline-flex rounded-full bg-[linear-gradient(135deg,#6d28d9,#8b5cf6)] px-5 py-2 text-[12px] font-semibold uppercase tracking-[0.14em] text-white shadow-[0_12px_30px_rgba(109,40,217,0.35)] sm:px-8 sm:py-3 sm:text-[15px] sm:tracking-[0.2em]" style="color:#ffffff !important;">
                    {{ $event->category ? strtoupper($event->category) : 'Live Event' }}
                </div>
                <h1 class="mt-6 text-3xl font-black tracking-[-0.045em] text-white sm:mt-7 sm:text-5xl lg:text-[5rem] lg:leading-[1.02]" style="color:#ffffff !important;">{{ $event->title }}</h1>
                <div class="mt-4 space-y-1 text-lg font-medium text-white/95 sm:mt-5 sm:text-[2rem]">
                    <p>Start: {{ $eventStartLabel }}</p>
                    <p>End: {{ $eventEndLabel }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="relative z-10 -mt-10 pb-32 sm:-mt-16 sm:pb-36 lg:pb-20">
        <div class="event-page-container mx-auto max-w-[1440px] px-2 sm:px-4 lg:px-4">
            @if($errors->any())
                <div class="mb-6 rounded-[22px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-[0_20px_45px_rgba(239,68,68,0.08)]">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
                <div class="space-y-6">
                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                        <span class="inline-flex rounded-lg bg-violet-600 px-3 py-1 text-xs font-semibold text-white" style="color:#ffffff !important;">{{ $event->category ? $event->category : 'Event' }}</span>
                        <h2 class="mt-5 text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">{{ $event->title }}</h2>
                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 3v3m8-3v3M4 9h16M5.5 5.5h13A1.5 1.5 0 0 1 20 7v11.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5V7a1.5 1.5 0 0 1 1.5-1.5Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg></div>
                                <div>
                                    <p class="text-[1.05rem] font-semibold text-slate-900">Start: {{ $eventStartLabel }}</p>
                                    <p class="text-[1.05rem] font-semibold text-slate-900">End: {{ $eventEndLabel }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-violet-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 20.5s6.5-5.8 6.5-11A6.5 6.5 0 0 0 5.5 9.5c0 5.2 6.5 11 6.5 11Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/><path d="M12 12.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg></div>
                                <div><p class="text-[1.05rem] font-semibold text-slate-900">{{ $venueName }}</p><p class="mt-1 text-[1rem] font-semibold text-slate-900"><a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener noreferrer" class="text-inherit">{{ $venueLine ?: 'Central Park, New York, NY' }}</a></p></div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                            <h2 class="text-[1.75rem] m-4 font-bold tracking-[-0.03em] text-slate-900" sm:m-4>About This Event</h2>    
                    {!! $aboutHtml !!}
                    </section>

                     

                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                        <h2 class="text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">Lineup / Schedule</h2>
                        <div class="mt-6 space-y-5">@foreach($lineupItems as $item)<div class="grid gap-3 text-[1.02rem] sm:grid-cols-[116px_minmax(0,1fr)] sm:items-center"><div class="font-medium text-slate-500">{{ data_get($item, 'time', ticketly_format_time($event->starts_at)) }}</div><div class="font-medium text-slate-900">{{ data_get($item, 'name') }}</div></div>@endforeach</div>
                    </section>

                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                        <h2 class="text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">Venue</h2>
                        <div class="mt-5 overflow-hidden rounded-[20px] border border-slate-200"><iframe title="Venue map" src="https://maps.google.com/maps?q={{ $mapQuery }}&t=&z=13&ie=UTF8&iwloc=&output=embed" class="h-[320px] w-full sm:h-[400px]" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
                        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div><p class="text-[1.12rem] font-semibold text-slate-900">{{ $venueName }}</p><p class="mt-1 text-[1rem] text-slate-500"><a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener noreferrer" class="text-inherit">{{ $venueLine ?: 'Central Park, New York, NY' }}</a></p></div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 hover:bg-slate-50"><svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"><path d="M10 3.75h5.25V9M9.25 10.75l6-6M14.25 10.75v4.5a1 1 0 0 1-1 1h-8.5a1 1 0 0 1-1-1v-8.5a1 1 0 0 1 1-1h4.5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg><span>Get Directions</span></a>
                        </div>
                    </section>

                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                            @if($organiserLogo)
                                <img src="{{ $organiserLogo }}" alt="{{ $organiserName }}" class="h-16 w-16 rounded-full object-cover ring-4 ring-violet-100">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[linear-gradient(135deg,#7c3aed,#a855f7)] text-lg font-bold text-white ring-4 ring-violet-100">{{ $organiserInitials }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3"><h2 class="text-[1.35rem] font-bold text-slate-900">{{ $organiserName }}</h2>@if(($event->organiser->is_approved ?? false))<span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Verified</span>@endif</div>
                                <p class="mt-4 max-w-3xl text-[1rem] leading-7 text-slate-500">{{ $organiserBio }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                        <h2 class="text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">FAQs</h2>
                        <div class="mt-6 divide-y divide-slate-200">
                            @foreach($faqItems as [$question, $answer])
                                <div class="py-1">
                                    <button type="button" class="faq-toggle flex w-full items-center justify-between gap-4 py-5 text-left" aria-expanded="false"><span class="text-[1.12rem] font-medium text-slate-900">{{ $question }}</span><svg class="h-5 w-5 shrink-0 text-slate-500 transition-transform" viewBox="0 0 20 20" fill="none" stroke="currentColor"><path d="m6 8 4 4 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/></svg></button>
                                    <div class="faq-answer hidden pb-5 pr-8 text-[1rem] leading-7 text-slate-500">{{ $answer }}</div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                        <h2 class="text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">Refund Policy</h2>
                        {!! $refundPolicyHtml !!}
                    </section>

                 <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
    
    <h2 class="text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">
        How would you like to get there?
    </h2>

    <div class="mt-6">
        <ul class="space-y-4">

            <!-- Driving -->
            <li>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $mapQuery }}&travelmode=driving"
                   target="_blank"
                   class="flex items-center gap-3 text-slate-600 hover:text-blue-600 transition">

                    <span class="w-6 h-6 flex items-center justify-center">
                        <!-- Car Icon -->
                       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 13H17.9235H13.9235H6H2V7.989C2 7.4375 2.387 7 2.9385 7H3H9.5H10.5H16.9385C17.4895 7 18 7.4375 18 7.989V13ZM17 15C17 15.5515 16.5515 16 16 16C15.4485 16 15 15.5515 15 15V14H17V15ZM5 15C5 15.5515 4.5515 16 4 16C3.4485 16 3 15.5515 3 15V14H5V15ZM4 1H16V6H10.5V3H9.5V6H4V1ZM20 6H17V0H3V6H2.9385H0V7H1.257C1.097 7.2915 1 7.626 1 7.989V14H2V15C2 16.1045 2.8955 17 4 17C5.1045 17 6 16.1045 6 15V14H14V15C14 16.1045 14.8575 17 15.962 17H15.981C17.0855 17 18 16.1045 18 15V14H19V7.989C19 7.626 18.889 7.2915 18.711 7H20V6ZM9 10H11.5V9H9V10ZM4.99895 10.9777C4.44795 10.9777 3.99995 10.5297 3.99995 9.97871C3.99995 9.42821 4.44795 8.98021 4.99895 8.98021C5.54995 8.98021 5.99795 9.42821 5.99795 9.97871C5.99795 10.5297 5.54995 10.9777 4.99895 10.9777ZM4.99895 7.98021C3.89695 7.98021 2.99995 8.87671 2.99995 9.97871C2.99995 11.0807 3.89695 11.9777 4.99895 11.9777C6.10095 11.9777 6.99795 11.0807 6.99795 9.97871C6.99795 8.87671 6.10095 7.98021 4.99895 7.98021ZM15.0014 10.998C14.4504 10.998 14.0024 10.55 14.0024 9.999C14.0024 9.4485 14.4504 9.0005 15.0014 9.0005C15.5524 9.0005 16.0004 9.4485 16.0004 9.999C16.0004 10.55 15.5524 10.998 15.0014 10.998ZM15.0014 8.0005C13.8994 8.0005 13.0024 8.897 13.0024 9.999C13.0024 11.101 13.8994 11.998 15.0014 11.998C16.1034 11.998 17.0004 11.101 17.0004 9.999C17.0004 8.897 16.1034 8.0005 15.0014 8.0005Z" fill="#3659E3"></path></svg>
                    </span>

                    <span class="text-[1rem] font-medium">Driving</span>
                </a>
            </li>

            <!-- Public Transport -->
            <li>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $mapQuery }}&travelmode=transit"
                   target="_blank"
                   class="flex items-center gap-3 text-slate-600 hover:text-blue-600 transition">

                    <span class="w-6 h-6 flex items-center justify-center">
                        <!-- Bus Icon -->
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M13 11.5H16V10.5H13V11.5ZM4 11.5H7V10.5H4V11.5ZM17 13.5H13.9915H6.013H3V8.5H9.5H10.5H17V13.5ZM17 15.5C17 16.0515 16.5515 16.5 16 16.5C15.4485 16.5 15 16.0515 15 15.5V14.5H17V15.5ZM5 15.5C5 16.0515 4.5515 16.5 4 16.5C3.4485 16.5 3 16.0515 3 15.5V14.5H5V15.5ZM3 1.5H17L17.0065 7.5H10.5V3.5H9.5V7.5H3V1.5ZM18 3.5V0.5H2V3.5H0.0025H0V6.5H1V4.5H2V15.5C2 16.6045 2.8955 17.5 4 17.5C5.1045 17.5 6 16.6045 6 15.5V14.5H14V15.5C14 16.6045 14.8955 17.5 16 17.5C17.1045 17.5 18 16.6045 18 15.5V4.5H19V6.5H20V3.5H18Z" fill="#3659E3"></path></svg>
                    </span>

                    <span class="text-[1rem] font-medium">Public Transport</span>
                </a>
            </li>

            <!-- Cycling -->
            <li>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $mapQuery }}&travelmode=bicycling"
                   target="_blank"
                   class="flex items-center gap-3 text-slate-600 hover:text-blue-600 transition">

                    <span class="w-6 h-6 flex items-center justify-center">
                        <!-- Cycle Icon -->
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M18.8447 18.0002C17.1067 18.0002 15.6927 16.654 15.6927 14.9994C15.6927 13.9096 16.3077 12.9563 17.2227 12.4309L18.1552 14.7644L19.0837 14.393L18.1567 12.0725C18.3782 12.025 18.6082 11.998 18.8447 11.998C20.5827 11.998 21.9967 13.3442 21.9967 14.9994C21.9967 16.654 20.5827 18.0002 18.8447 18.0002ZM7.8207 11.935L9.97519 8.85221L11.6277 12.9883L10.9212 13.9976H9.1682C8.9487 13.1798 8.4717 12.4624 7.8207 11.935ZM8.1201 13.9978H6.3791L7.2441 12.7601C7.6366 13.093 7.9406 13.5169 8.1201 13.9978ZM8.49957 14.9994C8.49957 16.654 6.98807 18.0002 5.25007 18.0002C3.51207 18.0002 2.04907 16.654 2.04907 14.9994C2.04907 13.3442 3.43857 11.998 5.17657 11.998C5.61257 11.998 6.01557 12.082 6.39357 12.235L4.44507 14.9979H5.67957H5.68057H8.49957V14.9994ZM15.1198 7.99918L12.3128 12.0093L10.7108 7.99918H15.1198ZM18.845 10.998C18.477 10.998 18.122 11.0485 17.7825 11.1349L15.7305 5.99954H17.9995V6.99933H18.9995V4.99976H18.4995H17.9995H13.9995V5.99954H14.6535L15.053 6.99933H10.3115L9.912 5.99954H10.9995V4.99976H7.9995V5.99954H8.835L9.4995 7.66269V7.78516L6.9645 11.4034C6.416 11.1464 5.803 10.998 5.152 10.998C2.859 10.998 1 12.7891 1 14.9991C1 17.2086 2.957 18.9998 5.25 18.9998C7.543 18.9998 9.4995 17.2086 9.4995 14.9991V14.9976H11.392L11.4255 15.0211L15.7755 8.80694L16.848 11.4914C15.564 12.1717 14.693 13.4869 14.693 14.9991C14.693 17.2086 16.552 18.9998 18.845 18.9998C21.138 18.9998 22.997 17.2086 22.997 14.9991C22.997 12.7891 21.138 10.998 18.845 10.998Z" fill="#3659E3"></path></svg>
                    </span>

                    <span class="text-[1rem] font-medium">Cycling</span>
                </a>
            </li>

            <!-- Walking -->
            <li>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $mapQuery }}&travelmode=walking"
                   target="_blank"
                   class="flex items-center gap-3 text-slate-600 hover:text-blue-600 transition">

                    <span class="w-6 h-6 flex items-center justify-center">
                        <!-- Walk Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="20" viewBox="0 0 12 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.725205 19.4686L1.61201 20.0011L5.11996 14.6736L4.23315 14.1411L0.725205 19.4686ZM6.60956 13.3136L4.51432 11.5751V8.01665C4.51432 7.45715 4.9758 7.00165 5.56194 7.00165C6.14809 7.00165 6.60956 7.45715 6.60956 8.01665V13.3136ZM7.65726 8.01665C7.65726 6.90365 6.72016 6.00165 5.55468 6.00165C4.90987 6.00165 4.34364 6.28365 3.95654 6.71865L3.9424 6.70765L0.537109 11.0901L1.38673 11.6861L3.46678 9.02015V12.0351L8.70488 16.3586V19.7476H9.7525V15.9166L9.76088 15.9071L7.65726 14.1811V8.01665ZM5.44355 0.998047C6.27072 0.998047 6.94385 1.67105 6.94385 2.49805C6.94385 3.32505 6.27072 3.99805 5.44355 3.99805C4.61639 3.99805 3.94325 3.32505 3.94325 2.49805C3.94325 1.67105 4.61639 0.998047 5.44355 0.998047ZM5.44335 4.99805C6.82413 4.99805 7.94335 3.87855 7.94335 2.49805C7.94335 1.11755 6.82413 -0.00195312 5.44335 -0.00195312C4.06257 -0.00195312 2.94335 1.11755 2.94335 2.49805C2.94335 3.87855 4.06257 4.99805 5.44335 4.99805ZM9.45617 10.3908L8.71917 11.1013L10.7091 12.9818L11.4461 12.2713L9.45617 10.3908Z" fill="#3659E3"></path></svg>
                    </span>

                    <span class="text-[1rem] font-medium">Walking</span>
                </a>
            </li>

        </ul>
    </div>

</section>

                    @if($event->sponsorships->isNotEmpty())
                    <section class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-8">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">Event Sponsors</h2>
                                <p class="mt-2 text-[1rem] text-slate-500">Supporting partners shown in clean logo cards.</p>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach($event->sponsorships as $sponsor)
                                @php
                                    $sponsorInitials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sponsor->name, 0, 2));
                                @endphp
                                <div class="group relative aspect-square overflow-hidden rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,#ffffff,#f8fafc)] p-4 shadow-[0_14px_35px_rgba(15,23,42,0.06)] transition-all duration-200 hover:-translate-y-1 hover:border-violet-200 hover:shadow-[0_20px_45px_rgba(99,102,241,0.14)]" title="{{ $sponsor->name }}">
                                    <div class="absolute inset-x-6 top-0 h-px bg-[linear-gradient(90deg,rgba(124,58,237,0),rgba(124,58,237,0.35),rgba(124,58,237,0))]"></div>
                                    <div class="flex h-full items-center justify-center rounded-[18px] border border-slate-100 bg-white p-4">
                                        @if($sponsor->photo_url)
                                            <img src="{{ asset('storage/app/public/sponsorships/'.basename($sponsor->photo_url)) }}" alt="{{ $sponsor->name }}" class="h-full w-full object-contain">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center rounded-[16px] bg-[linear-gradient(135deg,#7c3aed,#4f46e5)] text-xl font-bold tracking-[0.08em] text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.22)]">{{ $sponsorInitials }}</div>
                                        @endif
                                    </div>
                                    <span class="sr-only">{{ $sponsor->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif
                </div>

                <aside class="w-full lg:sticky lg:top-24 lg:w-[25rem]">
                    @if($event->isCancelled())
                        <div class="rounded-[24px] border border-red-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-6"><h2 class="text-[1.55rem] font-bold tracking-[-0.03em] text-slate-900 sm:text-[2rem]">Event Unavailable</h2><p class="mt-4 text-[1rem] leading-7 text-slate-500">{{ $event->cancellation_reason ?: 'This event has been cancelled by the organiser.' }}</p></div>
                    @elseif($event->starts_at->isPast())
                        <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-6"><h2 class="text-[1.55rem] font-bold tracking-[-0.03em] text-slate-900 sm:text-[2rem]">Event Closed</h2><p class="mt-4 text-[1rem] leading-7 text-slate-500">This event has already taken place.</p></div>
                    @elseif($event->ticketTiers->isEmpty())
                        <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-6"><h2 class="text-[1.55rem] font-bold tracking-[-0.03em] text-slate-900 sm:text-[2rem]">Tickets Coming Soon</h2><p class="mt-4 text-[1rem] leading-7 text-slate-500">Tickets for this event are not available yet. Please check back later.</p></div>
                    @else
                        <div id="select-tickets-section" class="scroll-mt-24 rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_20px_50px_rgba(15,23,42,0.06)] sm:p-6">
                            <h2 class="text-[1.75rem] font-bold tracking-[-0.03em] text-slate-900">Select Tickets</h2>
                            @if($activeReservationToken)
                                <div data-active-reservation-alert class="mt-4 rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-700">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <p>Your previous ticket selection is still saved. You can change quantities and continue checkout.</p>
                                        <div class="inline-flex items-center gap-3 rounded-xl border border-violet-200 bg-white/80 px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-violet-700">
                                            <span>Timer</span>
                                            <span data-event-reservation-countdown class="text-sm font-extrabold tracking-[0.2em] text-violet-600">{{ gmdate('i:s', $activeReservationSeconds) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <form action="{{ route('reservation.store') }}" method="POST" id="ticket-form" class="mt-5">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                @if($activeReservationToken)
                                    <input type="hidden" name="replace_reservation_token" value="{{ $activeReservationToken }}">
                                @endif
                                <div class="space-y-3">
                                    @foreach($event->ticketTiers as $tier)
                                        @php
                                            $reservedQty = (int) ($reservedQuantities[$tier->id] ?? 0);
                                            $maxSelect = min($tier->max_per_order, $tier->available_quantity + $reservedQty);
                                            $soldOut = $maxSelect <= 0;
                                            $oldItem = $ticketItemsOld->firstWhere('ticket_tier_id', $tier->id);
                                            $initialQty = min($maxSelect, max(0, (int) data_get($oldItem, 'quantity', 0)));
                                        @endphp
                                        <div id="tier-card-{{ $tier->id }}" class="rounded-[18px] border bg-white p-4 transition {{ $soldOut ? 'border-slate-200 opacity-60' : ($initialQty > 0 ? 'border-violet-500 shadow-[0_0_0_3px_rgba(124,58,237,0.08)]' : 'border-slate-200') }}">
                                            <div class="space-y-2">
                                                <div class="event-ticket-row flex items-start justify-between gap-[10px]">
                                                    <div class="min-w-0 flex-1 pr-2">
                                                        <h3 class="truncate text-[1.02rem] font-semibold text-slate-900">
                                                            {{ $tier->name }}
                                                        </h3>
                                                    </div>
                                                    @if(!$soldOut)
                                                        <div class="event-ticket-controls flex shrink-0 items-center gap-[10px] self-start whitespace-nowrap sm:ml-4">
                                                            <button type="button" onclick="changeQty({{ $tier->id }}, -1)" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-lg font-medium text-slate-500 hover:bg-slate-50">-</button>
                                                            <span id="qty-display-{{ $tier->id }}" class="w-5 shrink-0 text-center text-[1.05rem] font-medium text-slate-900">{{ $initialQty }}</span>
                                                            <button type="button" onclick="changeQty({{ $tier->id }}, 1)" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-lg font-medium text-slate-900 hover:bg-slate-50">+</button>
                                                        </div>
                                                        <input type="hidden" name="items[{{ $loop->index }}][ticket_tier_id]" value="{{ $tier->id }}">
                                                        <input type="hidden" name="items[{{ $loop->index }}][quantity]" id="qty-{{ $tier->id }}" data-max="{{ $maxSelect }}" value="{{ $initialQty }}">
                                                    @else
                                                        <div class="shrink-0 self-start rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">Sold Out</div>
                                                        <input type="hidden" name="items[{{ $loop->index }}][ticket_tier_id]" value="{{ $tier->id }}">
                                                        <input type="hidden" name="items[{{ $loop->index }}][quantity]" id="qty-{{ $tier->id }}" data-max="0" value="0">
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="mt-0 text-[0.98rem] leading-6 text-slate-500">{{ \Illuminate\Support\Str::limit($tier->description ?: 'Standard entry to all stages and event areas.', 64) }}</p>
                                                    <div class="mt-3 flex flex-wrap items-center gap-2"><span class="text-[1.08rem] font-bold text-violet-600">{{ $tier->price == 0 ? 'Free' : ticketly_money($tier->price) }}</span></div>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $soldOut ? 'Sold out' : number_format($tier->available_quantity) . ' left' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6">
                                    <h3 class="text-[1.45rem] font-bold tracking-[-0.03em] text-slate-900">Order Summary</h3>
                                    <div id="summary-items" class="mt-4 space-y-3 border-b border-slate-200 pb-4 text-sm"></div>
                                    <div class="space-y-2 pt-4 text-[0.98rem] text-slate-500">
                                        <div class="flex items-center justify-between"><span>Subtotal</span><span id="subtotal-display" class="font-medium text-slate-900">{{ ticketly_money(0) }}</span></div>
                                        <div class="flex items-center justify-between"><span>Portal Fee ({{ ticketly_format_percentage(ticketly_setting('portal_fee_percentage', config('ticketly.portal_fee_percentage', 10))) }}%)</span><span id="portal-fee-display" class="font-medium text-slate-900">{{ ticketly_money(0) }}</span></div>
                                        <div class="flex items-center justify-between"><span>Service Fee ({{ ticketly_format_percentage(ticketly_setting('service_fee_percentage', config('ticketly.service_fee_percentage', 5))) }}%)</span><span id="fee-display" class="font-medium text-slate-900">{{ ticketly_money(0) }}</span></div>
                                    </div>
                                </div>

                                <!-- <div class="mt-5 flex gap-2">
                                    <input type="text" placeholder="Promo code" class="h-11 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-violet-300 focus:ring-2 focus:ring-violet-100">
                                    <button type="button" class="rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-500 hover:bg-slate-50">Apply</button>
                                </div> -->

                                <div class="mt-6 flex items-center justify-between text-[1.1rem] font-semibold text-slate-900"><span>Total</span><span id="total-display" class="font-bold text-violet-600">{{ ticketly_money(0) }}</span></div>
                                <button type="submit" id="reserve-btn" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#7c3aed,#9333ea)] px-5 py-4 text-base font-semibold text-white shadow-[0_16px_40px_rgba(124,58,237,0.28)] disabled:cursor-not-allowed disabled:opacity-50" style="color:#ffffff !important;" disabled>Get Ticket</button>
                                <div class="mt-4 flex items-center justify-center gap-4 text-xs text-slate-500"><span>{{ number_format($interestedCount / 1000, 1) }}k interested</span><span>Selling fast</span></div>
                            </form>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>

    @if($hasSelectableTickets)
        <div id="mobile-ticket-cta"
             class="event-mobile-ticket-cta fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/95 px-4 pt-4 shadow-[0_-18px_40px_rgba(15,23,42,0.08)] backdrop-blur transition duration-300 lg:hidden sm:px-6">
            <div class="mx-auto max-w-[1440px]">
                <a id="mobile-ticket-cta-link"
                   href="#select-tickets-section"
                   aria-controls="select-tickets-section"
                   class="inline-flex h-16 w-full items-center justify-center rounded-2xl bg-[linear-gradient(90deg,#7c3aed,#8b5cf6)] px-6 text-[1.02rem] font-medium text-white shadow-[0_18px_45px_rgba(124,58,237,0.24)] max-[375px]:h-14 max-[375px]:text-[0.95rem] sm:h-[70px] sm:text-[1.08rem]"
                   style="color:#ffffff !important;">
                    Select Tickets
                </a>

                <div class="mt-3 text-center text-[0.95rem] text-slate-400 max-[375px]:text-[0.85rem]">
                    Choose your tickets and continue booking
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('head')
<style>
    body {
        overflow-x: hidden;
    }

    .event-mobile-ticket-cta {
        padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 1rem);
    }

    @media (max-width: 639px) {
        .event-page-container {
            max-width: 100%;
        }

        .event-ticket-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .event-ticket-controls {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: nowrap;
            flex-shrink: 0;
        }
    }
</style>
@endsection

@section('scripts')
@php
    $tiersForJs = $event->ticketTiers->map(function ($tier) use ($ticketItemsOld, $reservedQuantities) {
        $oldItem = $ticketItemsOld->firstWhere('ticket_tier_id', $tier->id);
        $reservedQty = (int) ($reservedQuantities[$tier->id] ?? 0);
        $maxSelect = min($tier->max_per_order, $tier->available_quantity + $reservedQty);
        return ['id' => $tier->id, 'name' => $tier->name, 'price' => (float) $tier->price, 'max' => (int) $maxSelect, 'quantity' => min($maxSelect, max(0, (int) data_get($oldItem, 'quantity', 0)))];
    })->values();
@endphp
<script>
const tiers = @json($tiersForJs), feePct = {{ (float) ticketly_setting('service_fee_percentage', config('ticketly.service_fee_percentage', 5)) }}, portalFeePct = {{ (float) ticketly_setting('portal_fee_percentage', config('ticketly.portal_fee_percentage', 10)) }}, currencySymbol = @js(ticketly_currency_symbol());
const money = (amount) => currencySymbol + Number(amount).toFixed(2);
const activeReservationCountdown = document.querySelector('[data-event-reservation-countdown]');
let activeReservationSecs = {{ $activeReservationSeconds }};
function updateActiveReservationCountdown(seconds) {
    if (!activeReservationCountdown) return;
    const safeSeconds = Math.max(0, seconds);
    const mins = String(Math.floor(safeSeconds / 60)).padStart(2, '0');
    const secs = String(safeSeconds % 60).padStart(2, '0');
    activeReservationCountdown.textContent = `${mins}:${secs}`;
    activeReservationCountdown.setAttribute('aria-label', `${mins} minutes ${secs} seconds remaining`);
}

if (activeReservationCountdown) {
    const activeReservationReturnUrl = @js(route('events.show', $event->slug) . '#ticket-form');
    updateActiveReservationCountdown(activeReservationSecs);

    const activeReservationTick = setInterval(() => {
        if (activeReservationSecs <= 0) {
            clearInterval(activeReservationTick);
            updateActiveReservationCountdown(0);
            window.location.replace(activeReservationReturnUrl);
            return;
        }

        activeReservationSecs -= 1;
        updateActiveReservationCountdown(activeReservationSecs);
    }, 1000);
}

function setTierState(id, qty) { const card = document.getElementById('tier-card-' + id); if (!card) return; card.classList.toggle('border-violet-500', qty > 0); card.classList.toggle('border-slate-200', qty === 0); card.style.boxShadow = qty > 0 ? '0 0 0 3px rgba(124,58,237,.08)' : ''; }
function changeQty(id, delta) { const input = document.getElementById('qty-' + id), display = document.getElementById('qty-display-' + id); if (!input || !display) return; const max = parseInt(input.dataset.max || '0', 10); const value = Math.max(0, Math.min(max, (parseInt(input.value || '0', 10) + delta))); input.value = value; display.textContent = value; updateSummary(); }
function updateSummary() {
    const summary = document.getElementById('summary-items'), subtotalEl = document.getElementById('subtotal-display'), portalFeeEl = document.getElementById('portal-fee-display'), feeEl = document.getElementById('fee-display'), totalEl = document.getElementById('total-display'), btn = document.getElementById('reserve-btn');
    if (!summary || !subtotalEl || !portalFeeEl || !feeEl || !totalEl || !btn) return;
    let subtotal = 0, count = 0, html = '';
    tiers.forEach((tier) => {
        const input = document.getElementById('qty-' + tier.id), qty = parseInt(input ? input.value : tier.quantity, 10) || 0, lineTotal = qty * Number(tier.price);
        setTierState(tier.id, qty);
        if (qty > 0) { count += qty; subtotal += lineTotal; html += `<div class="flex items-center justify-between gap-3 text-[0.98rem]"><span class="min-w-0 truncate text-slate-500">${tier.name} x ${qty}</span><span class="shrink-0 font-medium text-slate-900">${money(lineTotal)}</span></div>`; }
    });
    if (!html) html = '<p class="text-[0.98rem] text-slate-400">Select tickets to see your order summary.</p>';
    const portalFee = count > 0 && subtotal > 0 ? Number((subtotal * portalFeePct / 100).toFixed(2)) : 0;
    const fee = count > 0 && subtotal > 0 ? Number((subtotal * feePct / 100).toFixed(2)) : 0;
    const total = subtotal + portalFee + fee;
    summary.innerHTML = html; subtotalEl.textContent = money(subtotal); portalFeeEl.textContent = money(portalFee); feeEl.textContent = money(fee); totalEl.textContent = money(total); btn.disabled = count === 0; btn.textContent = count > 0 ? `Get ${count} Ticket${count > 1 ? 's' : ''}` : 'Get Ticket';
}
document.getElementById('ticket-form')?.addEventListener('submit', (e) => { if (!tiers.some((tier) => parseInt(document.getElementById('qty-' + tier.id)?.value || '0', 10) > 0)) e.preventDefault(); });
document.querySelectorAll('.faq-toggle').forEach((btn) => btn.addEventListener('click', function () { const answer = this.nextElementSibling, icon = this.querySelector('svg'), open = this.getAttribute('aria-expanded') === 'true'; this.setAttribute('aria-expanded', open ? 'false' : 'true'); answer?.classList.toggle('hidden', open); icon?.classList.toggle('rotate-180', !open); }));
document.getElementById('favorite-event-button')?.addEventListener('click', function () { const pressed = this.getAttribute('aria-pressed') === 'true'; this.setAttribute('aria-pressed', pressed ? 'false' : 'true'); this.classList.toggle('text-red-500', !pressed); });
document.getElementById('share-event-button')?.addEventListener('click', async function () { const url = window.location.href, data = { title: @json($event->title), url }; if (navigator.share) { try { await navigator.share(data); return; } catch (error) {} } try { await navigator.clipboard.writeText(url); this.classList.add('text-violet-600'); setTimeout(() => this.classList.remove('text-violet-600'), 1200); } catch (error) {} });
const mobileTicketCta = document.getElementById('mobile-ticket-cta');
const mobileTicketCtaLink = document.getElementById('mobile-ticket-cta-link');
const selectTicketsSection = document.getElementById('select-tickets-section');
mobileTicketCtaLink?.addEventListener('click', (event) => {
    event.preventDefault();
    window.location.hash = 'select-tickets-section';
    selectTicketsSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
function refreshMobileTicketCtaVisibility() {
    if (!mobileTicketCta || !selectTicketsSection) return;
    if (window.innerWidth >= 1024) {
        mobileTicketCta.classList.add('translate-y-full', 'opacity-0', 'pointer-events-none');
        return;
    }
    const targetTop = selectTicketsSection.getBoundingClientRect().top;
    const shouldHide = targetTop <= Math.max(window.innerHeight * 0.7, 320);
    mobileTicketCta.classList.toggle('translate-y-full', shouldHide);
    mobileTicketCta.classList.toggle('opacity-0', shouldHide);
    mobileTicketCta.classList.toggle('pointer-events-none', shouldHide);
}
refreshMobileTicketCtaVisibility();
window.addEventListener('scroll', refreshMobileTicketCtaVisibility, { passive: true });
window.addEventListener('resize', refreshMobileTicketCtaVisibility);
updateSummary();
</script>
@endsection
