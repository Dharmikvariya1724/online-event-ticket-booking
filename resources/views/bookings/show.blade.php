@extends('layouts.app')

@section('title', 'Booking Confirmed – ' . $booking->reference)

@section('content')
@php
    $promoDiscountLabel = 'Discount';
    if ($booking->promoCode) {
        $promoValue = $booking->promoCode->type === 'percentage'
            ? ticketly_format_percentage($booking->promoCode->value) . '%'
            : ticketly_money($booking->promoCode->value);
        $promoDiscountLabel .= ' (' . $booking->promoCode->code . ' - ' . $promoValue . ')';
    }
@endphp
<div class="min-h-screen bg-gray-950 py-16 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- ── Success Animation Header ──────────────────────────── --}}
        <div class="text-center mb-8">
            <div class="relative w-24 h-24 mx-auto mb-6">
                <div class="absolute inset-0 bg-emerald-500/10 rounded-full animate-ping" style="animation-duration:2s"></div>
                <div class="absolute inset-0 bg-emerald-900/40 border-2 border-emerald-700/50 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-2">Booking Confirmed! 🎉</h1>
            <p class="text-gray-400">Confirmation sent to <strong class="text-white">{{ $booking->customer_email }}</strong></p>
            @if($booking->customer_phone)
            <p class="text-gray-500 text-sm mt-1">SMS sent to {{ $booking->customer_phone }}</p>
            @endif
        </div>

        {{-- ── Reference Card ───────────────────────────────────── --}}
        <div class="bg-gradient-to-br from-indigo-600 via-purple-700 to-pink-700 rounded-2xl p-6 text-white text-center mb-6 shadow-2xl shadow-indigo-500/20">
            <p class="text-indigo-200 text-sm mb-1 font-medium">Booking Reference</p>
            <p class="text-4xl font-extrabold tracking-[0.2em] mb-1">{{ $booking->reference }}</p>
            <p class="text-indigo-200 text-xs">Present this at the venue entrance</p>
        </div>

        {{-- ── Booking Details ──────────────────────────────────── --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-6">

            {{-- Event Info --}}
            <div class="px-5 py-5 border-b border-gray-800">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-white text-lg leading-tight">{{ $booking->event->title }}</h2>
                        <div class="mt-2 space-y-1 text-sm text-gray-400">
                            <div class="flex items-center gap-1.5">📅 {{ ticketly_format_date($booking->event->starts_at) }}</div>
                            <div class="flex items-center gap-1.5">🕐 {{ ticketly_format_time($booking->event->starts_at) }}</div>
                            <div class="flex items-center gap-1.5">📍 {{ $booking->event->venue_name }}, {{ $booking->event->venue_address }}, {{ $booking->event->city }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ticket Summary --}}
            <div class="px-5 py-5 border-b border-gray-800">
                <h3 class="font-bold text-white mb-4">Ticket Summary</h3>
                <div class="space-y-2">
                    @foreach($booking->items as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-600/20 border border-indigo-500/30 rounded-lg flex items-center justify-center">
                                <span class="text-indigo-400 font-bold text-sm">{{ $item->quantity }}</span>
                            </div>
                            <span class="text-gray-200 text-sm font-medium">{{ $item->ticketTier->name }}</span>
                        </div>
                        <span class="text-white font-semibold text-sm">
                            {{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->subtotal) }}
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- Price breakdown --}}
                <div class="mt-5 pt-4 border-t border-gray-800 space-y-2">
                    <div class="flex justify-between text-sm text-gray-400">
                        <span>Subtotal</span><span>{{ ticketly_money($booking->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-400">
                        <span>Portal Fee</span><span>{{ ticketly_money($booking->portal_fee ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-400">
                        <span>Service Fee</span><span>{{ ticketly_money($booking->service_fee ?? 0) }}</span>
                    </div>
                    @if($booking->discount_amount > 0)
                    <div class="flex justify-between text-sm text-emerald-400">
                        <span>{{ $promoDiscountLabel }}</span>
                        <span>-{{ ticketly_money($booking->discount_amount) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-extrabold text-white text-lg pt-3 border-t border-gray-700 mt-2">
                        <span>Total Paid</span>
                        <span class="text-indigo-400">{{ ticketly_money($booking->total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Customer Details --}}
            <div class="px-5 py-5">
                <h3 class="font-bold text-white mb-3">Customer Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-gray-800/50 rounded-xl p-3">
                        <p class="text-gray-500 text-xs mb-1">Name</p>
                        <p class="text-white text-sm font-semibold">{{ $booking->customer_name }}</p>
                    </div>
                    <div class="bg-gray-800/50 rounded-xl p-3">
                        <p class="text-gray-500 text-xs mb-1">Email</p>
                        <p class="text-white text-sm truncate">{{ $booking->customer_email }}</p>
                    </div>
                    @if($booking->customer_phone)
                    <div class="bg-gray-800/50 rounded-xl p-3">
                        <p class="text-gray-500 text-xs mb-1">Mobile</p>
                        <p class="text-white text-sm">{{ $booking->customer_phone }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Payment Status Badge ────────────────────────────── --}}
        <div class="bg-emerald-900/30 border border-emerald-700/50 rounded-2xl p-4 text-center mb-6">
            <span class="inline-flex items-center gap-2 text-emerald-300 font-bold">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Payment Confirmed · {{ strtoupper($booking->status) }}
                @if($booking->stripe_payment_intent_id)
                <span class="font-mono text-xs text-emerald-500 font-normal ml-2 hidden sm:inline">{{ substr($booking->stripe_payment_intent_id, 0, 20) }}...</span>
                @endif
            </span>
        </div>

        {{-- ── What's Next ──────────────────────────────────────── --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-6">
            <h3 class="font-bold text-white mb-4">What happens next?</h3>
            <div class="space-y-3">
                @foreach([
                    ['✉️', 'Confirmation email', 'A confirmation has been sent to '.$booking->customer_email.' with your booking details and reference number.'],
                    ['📱', 'SMS notification', $booking->customer_phone ? 'A text has been sent to '.$booking->customer_phone.' with your booking reference.' : 'Add your mobile number next time to receive SMS confirmations.'],
                    ['🎟', 'At the venue', 'Show your booking reference '.$booking->reference.' at the entrance. Have it saved or printed ready.'],
                ] as [$icon, $title, $desc])
                <div class="flex items-start gap-3">
                    <span class="text-xl w-8 text-center flex-shrink-0 mt-0.5">{{ $icon }}</span>
                    <div>
                        <p class="font-semibold text-white text-sm">{{ $title }}</p>
                        <p class="text-gray-400 text-xs mt-0.5 leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── CTA Buttons ──────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('events.index') }}"
               class="flex-1 text-center border border-gray-700 text-gray-300 font-semibold py-3 rounded-xl hover:bg-gray-800 transition-colors">
               Browse More Events
            </a>
            <a href="{{ route('events.show', $booking->event->slug) }}"
               class="flex-1 text-center bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-bold py-3 rounded-xl hover:opacity-90 transition-opacity">
               View Event Details
            </a>
        </div>
    </div>
</div>
@endsection
