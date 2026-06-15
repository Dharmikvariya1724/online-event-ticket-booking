@extends('layouts.app')
@section('title', 'Booking Confirmed - ' . $booking->reference)

@section('content')
@php
    $eventImage = $booking->event->banner_url ?: 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&h=400&fit=crop';
    $eventStartsAt = $booking->event->starts_at;
    $eventEndsAt = $booking->event->ends_at;
    $isMultiDayEvent = $eventEndsAt && ! $eventStartsAt->isSameDay($eventEndsAt);
    $eventDate = ticketly_format_date($eventStartsAt);
    $eventTime = ticketly_format_time($eventStartsAt) . ($eventEndsAt ? ' - ' . ticketly_format_time($eventEndsAt) : '');
    $eventStartDisplay = ticketly_format_compact_datetime($eventStartsAt);
    $eventEndDisplay = ticketly_format_compact_datetime($eventEndsAt);
    $eventLocation = collect([$booking->event->venue_name, $booking->event->city])->filter()->implode(', ');
    $portalFeePercentage = ticketly_format_percentage(ticketly_setting('portal_fee_percentage', config('ticketly.portal_fee_percentage', 10)));
    $serviceFeePercentage = ticketly_format_percentage(ticketly_setting('service_fee_percentage', config('ticketly.service_fee_percentage', 5)));
    $promoDiscountLabel = 'Discount';
    if ($booking->promoCode) {
        $promoValue = $booking->promoCode->type === 'percentage'
            ? ticketly_format_percentage($booking->promoCode->value) . '%'
            : ticketly_money($booking->promoCode->value);
        $promoDiscountLabel .= ' (' . $booking->promoCode->code . ' - ' . $promoValue . ')';
    }
    $refundPolicySource = (string) ($booking->event->refund_policy ?? '');
    $refundPolicyHtml = trim(strip_tags(html_entity_decode($refundPolicySource))) !== ''
        ? $refundPolicySource
        : '<p>Free cancellation up to 24h before event</p>';
@endphp

<main id="main-content" class="min-h-screen bg-[#ffffff] px-4 py-8 sm:py-10">
    <div class="mx-auto max-w-[520px]">
        <section class="overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.08)]">
            <header class="bg-[linear-gradient(135deg,#1fbe71_0%,#0daf68_100%)] px-6 pb-9 pt-8 text-center">
                <div class="mx-auto mb-5 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/95">
                    <svg class="h-9 w-9 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-[2rem] font-extrabold tracking-[-0.02em] text-white">Booking Confirmed!</h1>
                <p class="mt-2 text-[1.08rem] font-medium text-emerald-50">Get ready for an amazing experience</p>
            </header>

            <div class="relative border-t border-dashed border-slate-300">
                <span class="absolute -left-4 -top-4 h-8 w-8 rounded-full bg-[#ececef]"></span>
                <span class="absolute -right-4 -top-4 h-8 w-8 rounded-full bg-[#ececef]"></span>
            </div>

            <div class="px-6 pb-7 pt-6">
                <img src="{{ $eventImage }}" alt="{{ $booking->event->title }}" class="h-[130px] w-full rounded-2xl object-cover sm:h-[145px]">

                <h2 class="mt-5 text-[2rem] font-extrabold tracking-[-0.03em] text-slate-900">{{ $booking->event->title }}</h2>

                <div class="mt-3 space-y-2.5 text-[1.02rem] text-slate-600">
                    <div class="flex items-center gap-2.5">
                        <svg class="h-5 w-5 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <rect x="3.5" y="5.5" width="17" height="15" rx="2.5" stroke-width="1.8"></rect>
                            <path d="M7 3.5v4M17 3.5v4M3.5 10.5h17" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                        @if($isMultiDayEvent)
                            <div class="space-y-1">
                                <p>Start: {{ $eventStartDisplay }}</p>
                                <p>End: {{ $eventEndDisplay }}</p>
                            </div>
                        @else
                            <span>{{ $eventDate }} &middot; {{ $eventTime }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2.5">
                        <svg class="h-5 w-5 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-5.3 7-11a7 7 0 1 0-14 0c0 5.7 7 11 7 11Z"></path>
                            <circle cx="12" cy="10" r="2.5" stroke-width="1.8"></circle>
                        </svg>
                        <span>{{ $eventLocation }}</span>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl bg-slate-100 px-4 py-4">
                    <div class="space-y-2.5 text-[1rem]">
                        @foreach($booking->items as $item)
                            <div class="flex items-center justify-between gap-3">
                                <span class="min-w-0 flex-1 break-words text-slate-700">{{ $item->ticketTier->name }} x {{ $item->quantity }}</span>
                                <span class="shrink-0 font-semibold text-slate-900">{{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->subtotal) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3 border-t border-slate-300 pt-3 text-[1rem]">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span>{{ ticketly_money($booking->subtotal) }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-slate-600">
                            <span>Portal Fee ({{ $portalFeePercentage }}%)</span>
                            <span>{{ ticketly_money($booking->portal_fee ?? 0) }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-slate-600">
                            <span>Service Fee ({{ $serviceFeePercentage }}%)</span>
                            <span>{{ ticketly_money($booking->service_fee ?? 0) }}</span>
                        </div>
                        @if($booking->discount_amount > 0)
                            <div class="mt-2 flex items-center justify-between text-emerald-600">
                                <span>{{ $promoDiscountLabel }}</span>
                                <span>-{{ ticketly_money($booking->discount_amount) }}</span>
                            </div>
                        @endif
                        <div class="mt-3 flex items-center justify-between border-t border-slate-300 pt-3 text-[1.18rem] font-extrabold text-slate-900">
                            <span>Total Paid</span>
                            <span class="text-violet-600">{{ ticketly_money($booking->total) }}</span>
                        </div>
                    </div>
                </div>

                <p class="mt-5 text-[1rem] text-slate-700">
                    <span class="font-semibold text-slate-900">{{ $booking->customer_name }}</span>
                    <span class="text-slate-500"> &middot; {{ $booking->customer_email }}</span>
                </p>
            </div>

            <div class="relative border-t border-dashed border-slate-300">
                <span class="absolute -left-4 -top-4 h-8 w-8 rounded-full bg-[#ececef]"></span>
                <span class="absolute -right-4 -top-4 h-8 w-8 rounded-full bg-[#ececef]"></span>
            </div>

            <div class="bg-[#efedfb] px-6 pb-7 pt-7 text-center">
                <p class="inline-flex items-center gap-2 text-[1.08rem] font-semibold text-violet-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm14 0v6h-6M14 14h2v2h-2v-2Zm2 2h2v2h-2v-2Zm-4 0h2v2h-2v-2Zm0 2h2v2h-2v-2Z"></path>
                    </svg>
                    Scan at Entry Gate
                </p>

                <div class="mx-auto mt-5 w-[170px] rounded-2xl bg-white p-3 shadow-[0_6px_18px_rgba(15,23,42,0.14)]">
                    <img
                        src="{{ $qrImageSrc }}"
                        alt="QR code for booking reference {{ $booking->reference }}"
                        class="h-full w-full rounded-lg"
                        loading="lazy"
                    >
                </div>

                <p class="mt-4 text-[1.7rem] font-extrabold tracking-[-0.02em] text-violet-700">{{ $booking->reference }}</p>
                <p class="text-[0.98rem] text-violet-500">Present this code at the door</p>
            </div>

            <div class="flex items-center justify-between bg-[#0f1933] px-6 py-3 text-[0.86rem] text-slate-300">
                <span>Ticketly &middot; Official Ticket</span>
                <span class="font-semibold tracking-[0.03em]">{{ $booking->reference }}</span>
            </div>
        </section>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-white px-5 py-4">
            <p class="flex items-center gap-2 text-[1rem] text-slate-700">
                <svg class="h-5 w-5 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <rect x="3.5" y="5.5" width="17" height="13" rx="2.5" stroke-width="1.8"></rect>
                    <path d="m4 7 8 6 8-6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span>Confirmation sent to <span class="font-semibold text-slate-900">{{ $booking->customer_email }}</span></span>
            </p>
            <div class="mt-2 flex items-start gap-2 text-[1rem] text-slate-700">
                <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V7a5 5 0 0 1 10 0v3"></path>
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8"></rect>
                </svg>
                <div class="flex-1">{!! $refundPolicyHtml !!}</div>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <a href="{{ route('events.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-[1rem] font-semibold text-slate-900 transition hover:bg-slate-50"
               aria-label="Browse more events">
                Browse Events
            </a>
            <a href="{{ route('booking.ticket.pdf', $booking->reference) }}"
               target="_blank"
               rel="noopener"
               class="inline-flex items-center justify-center rounded-xl bg-[linear-gradient(90deg,#7c3aed,#6d28d9)] px-4 py-3.5 text-[1rem] font-semibold text-white transition hover:opacity-95"
               aria-label="Open printable PDF ticket" style="color:#ffffff !important;">
                Print Ticket
            </a>
        </div>
    </div>
</main>
@endsection
