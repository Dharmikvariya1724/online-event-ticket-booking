@extends('layouts.app')
@section('title', 'Checkout - ' . $reservation->event->title)

@section('head')
<script id="stripe-js" src="https://js.stripe.com/v3/"></script>
@include('partials.checkout-transition-guard-head', ['reservationToken' => $reservation->token, 'processingUrl' => route('checkout.success', $reservation->token)])
<style>
    #card-element {
        min-height: 52px;
        width: 100%;
        display: flex;
        align-items: center;
    }
    #card-element .StripeElement,
    #card-element .__PrivateStripeElement {
        width: 100%;
    }
    .mobile-checkout-cta {
        padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 1rem);
    }
</style>
@endsection

@section('content')
@php
    $eventPageUrl = route('events.show', $reservation->event->slug);
    $ticketSelectionUrl = $eventPageUrl . '?reservation=' . $reservation->token . '#ticket-form';
    $heroImage = $reservation->event->banner_url ?: 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&h=400&fit=crop';
    $startsAt = $reservation->event->starts_at;
    $endsAt = $reservation->event->ends_at;
    $desktopDateLine = ticketly_format_datetime($startsAt);
    $mobileDateLine = ticketly_format_datetime($startsAt) . ($endsAt ? ' - ' . ticketly_format_time($endsAt) : '');
    $desktopLocation = collect([$reservation->event->venue_name, $reservation->event->city])->filter()->implode(', ');
    $mobileLocation = collect([$reservation->event->venue_name, $reservation->event->city])->filter()->implode(' | ');
    $initialPricing = \App\Services\ServiceFeeCalculator::total(
        (float) $reservation->subtotal,
        (float) ($reservation->discount_amount ?? 0)
    );
    $initialDiscount = (float) $initialPricing['discount'];
    $initialTotal = (float) $initialPricing['total'];
    $initialDiscountLabel = 'Discount';
    if ($reservation->promoCode) {
        $promoValue = $reservation->promoCode->type === 'percentage'
            ? ticketly_format_percentage($reservation->promoCode->value) . '%'
            : ticketly_money($reservation->promoCode->value);
        $initialDiscountLabel .= ' (' . $reservation->promoCode->code . ' - ' . $promoValue . ')';
    }
@endphp

<div class="min-h-screen bg-[#ffffff] text-slate-900">
    <section class="relative overflow-hidden lg:hidden">
        <img src="{{ $heroImage }}" alt="{{ $reservation->event->title }}" class="h-[238px] w-full object-cover max-[375px]:h-[220px] sm:h-[274px]">
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(32,14,77,0.1)_0%,rgba(34,15,79,0.48)_28%,rgba(16,10,41,0.8)_100%)]"></div>
        <div class="absolute inset-x-0 bottom-0 px-5 pb-7 text-center text-white max-[375px]:px-3 max-[375px]:pb-5">
            <div class="inline-flex rounded-full bg-[linear-gradient(135deg,#7c3aed,#8b5cf6)] px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] max-[375px]:px-3 max-[375px]:text-[10px]" style="color:#ffffff !important;">FEATURED</div>
            <h1 class="mt-4 break-words text-[1.65rem] leading-none tracking-[-0.05em] max-[375px]:text-[1.4rem] sm:text-[1.95rem]" style="color:#ffffff !important;">{{ $reservation->event->title }}</h1>
            <p class="mt-3 text-[0.95rem] font-medium text-white/90 max-[375px]:text-[0.85rem] sm:text-[1rem]">{{ $mobileDateLine }}</p>
            <p class="mt-3 text-[0.9rem] font-semibold text-white/88 max-[375px]:mt-2 max-[375px]:text-[0.82rem] sm:text-[0.95rem]" style="color:#ffffff !important;">{{ $mobileLocation }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-[1480px] px-4 pb-32 pt-6 max-[375px]:px-3 sm:px-6 sm:pb-36 lg:px-8 lg:py-8">
        @if(session('payment_cancelled'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert" aria-live="polite">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m8.938-3A9.002 9.002 0 0012 3a9.002 9.002 0 00-8.938 9 9.002 9.002 0 008.938 9 9.002 9.002 0 008.938-9z"></path>
                </svg>
                <span class="font-medium">Your payment was cancelled. Your tickets are still held. Complete checkout before the timer expires.</span>
            </div>
        @endif

        @if(session('payment_error'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"></path>
                </svg>
                <span class="font-medium">Payment failed: {{ session('payment_error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                @foreach($errors->all() as $e)
                    <div class="flex items-start gap-2">
                        <span class="pt-0.5 text-rose-500">&bull;</span>
                        <span>{{ $e }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div id="hold-expired-alert" class="hidden mb-6 rounded-[24px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700" role="alert" aria-live="assertive">
            <p id="hold-expired-message" class="font-semibold text-rose-800"></p>
            <a id="hold-expired-return-link"
               href="{{ $ticketSelectionUrl }}"
               class="mt-3 inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white">
                Return to ticket selection
            </a>
            <p class="mt-2 text-xs text-rose-500">Redirecting automatically...</p>
        </div>

        <div class="grid gap-6 max-[375px]:gap-5 lg:grid-cols-[minmax(0,1fr)_404px] lg:gap-14">
            <div class="order-2 space-y-8 lg:order-1">
                <div class="hidden lg:flex">
                    <a href="{{ $ticketSelectionUrl }}" class="inline-flex items-center gap-3 text-[0.98rem] font-medium text-slate-800">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 6 9 12l6 6"></path>
                            </svg>
                        </span>
                        <span>Modify Tickets</span>
                    </a>
                </div>

                <section class="hidden rounded-[30px] border border-slate-200 bg-white p-7 shadow-[0_10px_30px_rgba(15,23,42,0.04)] lg:block">
                    <div class="flex items-center gap-5">
                        <img src="{{ $heroImage }}" alt="{{ $reservation->event->title }}" class="h-[142px] w-[174px] rounded-[18px] object-cover">
                        <div class="min-w-0">
                            <h2 class="text-[1.75rem] font-extrabold tracking-[-0.04em] text-slate-900">{{ $reservation->event->title }}</h2>
                            <p class="mt-4 text-[0.98rem] text-slate-500">{{ $desktopDateLine }}</p>
                            <p class="mt-3 text-[0.98rem] text-slate-500">{{ $desktopLocation }}</p>
                        </div>
                    </div>
                </section>

                <section id="contact-section" class="scroll-mt-6 rounded-[30px] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.04)] max-[375px]:rounded-[24px] max-[375px]:p-4 sm:p-8">
                    <div class="flex items-start justify-between gap-3 max-[375px]:gap-2">
                        <h2 class="text-[1.45rem] font-extrabold tracking-[-0.04em] text-slate-900 max-[375px]:text-[1.25rem] sm:text-[1.6rem]">Contact Information</h2>
                        <span class="shrink-0 text-sm font-semibold text-rose-600 max-[375px]:text-xs"><span aria-hidden="true">*</span> Required</span>
                    </div>
                    <div class="mt-8 space-y-7 max-[375px]:mt-5 max-[375px]:space-y-5">
                        <div>
                            <label for="customer-name" class="mb-3 block text-[0.98rem] font-medium text-slate-700 max-[375px]:mb-2 max-[375px]:text-[0.9rem]">Full Name <span class="text-rose-500" aria-hidden="true">*</span></label>
                            <input id="customer-name" type="text" autocomplete="name" aria-describedby="customer-name-error"
                                   maxlength="100" pattern="[A-Za-z .']+" title="Use letters, spaces, dots, and apostrophes only" required
                                   class="h-16 w-full rounded-2xl border border-slate-200 bg-white px-5 text-[1rem] text-slate-900 outline-none transition focus:border-violet-300 focus:ring-4 focus:ring-violet-100 max-[375px]:h-14 max-[375px]:px-4 max-[375px]:text-[0.95rem]"
                                   placeholder="Enter your full name" value="{{ old('name', $reservation->customer_name) }}">
                            <p id="customer-name-error" class="hidden mt-2 text-sm text-rose-600"></p>
                        </div>
                        <div>
                            <label for="customer-email" class="mb-3 block text-[0.98rem] font-medium text-slate-700 max-[375px]:mb-2 max-[375px]:text-[0.9rem]">Email Address <span class="text-rose-500" aria-hidden="true">*</span></label>
                            <input id="customer-email" type="email" autocomplete="email" aria-describedby="customer-email-error"
                                   maxlength="100" required
                                   class="h-16 w-full rounded-2xl border border-slate-200 bg-white px-5 text-[1rem] text-slate-900 outline-none transition focus:border-violet-300 focus:ring-4 focus:ring-violet-100 max-[375px]:h-14 max-[375px]:px-4 max-[375px]:text-[0.95rem]"
                                   placeholder="Enter your email address" value="{{ old('email', $reservation->customer_email) }}">
                            <p id="customer-email-error" class="hidden mt-2 text-sm text-rose-600"></p>
                        </div>
                        <div>
                            <label for="customer-phone" class="mb-3 block text-[0.98rem] font-medium text-slate-700 max-[375px]:mb-2 max-[375px]:text-[0.9rem]">Phone Number <span class="text-rose-500" aria-hidden="true">*</span></label>
                            <input id="customer-phone" type="tel" autocomplete="tel" aria-describedby="customer-phone-error"
                                   maxlength="11" minlength="11" inputmode="numeric" pattern="07[0-9]{9}" title="Enter exactly 11 digits starting with 07" required
                                    class="h-16 w-full rounded-2xl border border-slate-200 bg-white px-5 text-[1rem] text-slate-900 outline-none transition focus:border-violet-300 focus:ring-4 focus:ring-violet-100 max-[375px]:h-14 max-[375px]:px-4 max-[375px]:text-[0.95rem]"
                                   placeholder="07123456789" value="{{ old('phone', $reservation->customer_phone) }}">
                            <p id="customer-phone-error" class="hidden mt-2 text-sm text-rose-600"></p>
                        </div>
                    </div>
                </section>

                <section id="payment-section" class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.04)] max-[375px]:rounded-[24px] max-[375px]:p-4 sm:p-8">
                    <h2 class="text-[1.45rem] font-extrabold tracking-[-0.04em] text-slate-900 max-[375px]:text-[1.25rem] sm:text-[1.6rem]">Payment Method</h2>
                    <div class="mt-8 rounded-[24px] border border-slate-200 bg-[#fbfbfc] p-5 max-[375px]:mt-5 max-[375px]:p-4 sm:p-8">
                        <div class="mb-6 flex items-center gap-4 text-slate-900">
                            <svg class="h-7 w-7 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <rect x="3.5" y="5.5" width="17" height="13" rx="2.5" stroke-width="1.8"></rect>
                                <path d="M3.5 10h17" stroke-width="1.8" stroke-linecap="round"></path>
                            </svg>
                            <span class="text-[1rem] font-medium max-[375px]:text-[0.92rem] sm:text-[1.05rem]">Credit / Debit Card</span>
                        </div>

                        <div id="card-element" class="rounded-2xl border border-slate-200 bg-white p-4 max-[375px]:p-3" aria-label="Card payment form"></div>
                        <div id="payment-placeholder" class="hidden mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600" role="status"></div>
                        <div id="payment-message" class="hidden mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert" aria-live="assertive"></div>
                    </div>

                    <div class="mt-7 flex items-center gap-3 text-[0.98rem] text-slate-500 max-[375px]:mt-5 max-[375px]:items-start max-[375px]:text-[0.88rem]">
                        <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V7a5 5 0 0 1 10 0v3"></path>
                            <rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8"></rect>
                        </svg>
                        <span>Your payment information is encrypted and secure</span>
                    </div>
                </section>

                <section class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.04)] max-[375px]:rounded-[24px] max-[375px]:p-4">
                    <label class="flex items-center gap-4 text-[1rem] text-slate-700 max-[375px]:items-start max-[375px]:gap-3 max-[375px]:text-[0.9rem]">
                        <input id="terms-checkbox" type="checkbox" aria-describedby="terms-checkbox-error" class="h-6 w-6 rounded-md border border-slate-300 text-violet-600 focus:ring-violet-500 max-[375px]:mt-0.5 max-[375px]:h-5 max-[375px]:w-5">
                        <span>I agree to the <a href="{{ route('terms') }}" target="_blank" class="font-medium text-violet-500">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank" class="font-medium text-violet-500">Privacy Policy</a></span>
                    </label>
                    <p id="terms-checkbox-error" class="hidden mt-3 text-sm text-rose-600"></p>
                </section>

                <button id="pay-btn" type="button"
                        class="inline-flex h-16 w-full items-center justify-center rounded-2xl bg-[linear-gradient(90deg,#7c3aed,#8b5cf6)] px-6 text-[1.02rem] font-semibold text-white shadow-[0_18px_45px_rgba(124,58,237,0.24)] transition max-[375px]:h-14 max-[375px]:px-4 max-[375px]:text-[0.95rem] sm:h-[70px] sm:text-[1.08rem] disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label="Complete payment" disabled style="color:#ffffff !important;">
                    <span id="pay-btn-text">Complete Purchase <span class="sr-only">for {{ ticketly_currency_symbol() }}<span id="final-total">{{ number_format($initialTotal, 2) }}</span></span></span>
                    <span id="pay-spinner" class="hidden">Processing...</span>
                </button>
            </div>

            <aside class="order-1 lg:order-2" aria-label="Order summary">
                <div class="rounded-t-[34px] bg-[#f5f5f7] px-6 pb-8 pt-7 shadow-none max-[375px]:px-3 max-[375px]:pb-6 max-[375px]:pt-5 sm:px-8 lg:hidden lg:rounded-none lg:bg-transparent lg:px-0 lg:pb-0 lg:pt-0">
                    <div class="-mx-6 mb-7 border-b border-slate-200 px-6 pb-5 max-[375px]:-mx-3 max-[375px]:mb-5 max-[375px]:px-3 max-[375px]:pb-4 sm:-mx-8 sm:px-8">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-[1.5rem] font-extrabold tracking-[-0.05em] text-slate-900 max-[375px]:text-[1.3rem] sm:text-[1.7rem]">Checkout</h2>
                            <a href="{{ $ticketSelectionUrl }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full text-slate-400" aria-label="Close checkout">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="mb-6 rounded-[24px] border border-violet-100 bg-white px-5 py-4 max-[375px]:px-4 max-[375px]:py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-500">Reservation Timer</p>
                        <div class="mt-2 flex items-center justify-between gap-4">
                            <span class="text-[1rem] font-medium text-slate-700 max-[375px]:text-[0.9rem]">Time remaining</span>
                            <span data-countdown-display class="text-[1.2rem] font-extrabold tracking-[0.18em] text-violet-600 max-[375px]:text-[1.05rem] sm:text-[1.3rem]">{{ gmdate('i:s', $reservation->secondsRemaining()) }}</span>
                        </div>
                    </div>

                    <div class="rounded-[26px] border border-slate-200 bg-white px-5 py-6 shadow-[0_10px_30px_rgba(15,23,42,0.04)] max-[375px]:px-4 max-[375px]:py-5">
                        <h3 class="text-[1.1rem] font-extrabold tracking-[-0.03em] text-slate-900 sm:text-[1.2rem]">Order Summary</h3>
                        <div class="mt-8 space-y-5 text-[1rem] max-[375px]:mt-6 max-[375px]:space-y-4 max-[375px]:text-[0.92rem]">
                            @foreach($reservation->items as $item)
                                <div class="flex items-center justify-between gap-4">
                                    <span class="min-w-0 flex-1 break-words pr-2 text-slate-600">{{ $item->ticketTier->name }} <span class="ml-2 text-slate-500">x {{ $item->quantity }}</span></span>
                                    <span class="shrink-0 font-semibold text-slate-900">{{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->subtotal) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-8 border-t border-slate-200 pt-6 text-[1rem] text-slate-500">
                            <div class="flex items-center justify-between">
                                <span>Subtotal</span>
                                <span data-summary="subtotal" class="font-medium text-slate-900">{{ ticketly_money($reservation->subtotal) }}</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <span>Portal Fee ({{ ticketly_format_percentage(ticketly_setting('portal_fee_percentage', $portalFeePct)) }}%)</span>
                                <span data-summary="portal-fee" class="font-medium text-slate-900">{{ ticketly_money($initialPricing['portal_fee']) }}</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <span>Service Fee ({{ ticketly_format_percentage(ticketly_setting('service_fee_percentage', $feePct)) }}%)</span>
                                <span data-summary="service-fee" class="font-medium text-slate-900">{{ ticketly_money($initialPricing['service_fee']) }}</span>
                            </div>
                            <div data-discount-row class="{{ $initialDiscount > 0 ? '' : 'hidden ' }}mt-4 flex items-center justify-between text-emerald-600">
                                <span data-discount-label>{{ $initialDiscountLabel }}</span>
                                <span data-discount-value>-{{ ticketly_money($initialDiscount) }}</span>
                            </div>
                            <div class="mt-8 flex items-center justify-between text-[1.05rem] font-extrabold text-slate-900 max-[375px]:mt-6 max-[375px]:text-[0.98rem] sm:text-[1.15rem]">
                                <span>Total</span>
                                <span data-summary="total" class="text-[1.2rem] text-violet-600 max-[375px]:text-[1.08rem] sm:text-[1.3rem]">{{ ticketly_money($initialTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:releteve lg:top-24 sticky ">
                    <div class="hidden space-y-6 lg:block">
                        <div class="rounded-[28px] border border-violet-100 bg-white px-6 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)]" style="margin-top:4.3rem;">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-500">Reservation Timer</p>
                            <div class="mt-2 flex items-center justify-between gap-4">
                                <span class="text-[1rem] font-medium text-slate-700">Time remaining</span>
                                <span data-countdown-display class="text-[1.25rem] font-extrabold tracking-[0.18em] text-violet-600">{{ gmdate('i:s', $reservation->secondsRemaining()) }}</span>
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-slate-200 bg-white px-8 py-8 shadow-[0_10px_30px_rgba(15,23,42,0.04)]">
                            <h3 class="text-[1.1rem] font-extrabold tracking-[-0.03em] text-slate-900 sm:text-[1.2rem]">Order Summary</h3>
                            <div class="mt-9 space-y-6 text-[1rem]">
                                @foreach($reservation->items as $item)
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="min-w-0 flex-1 break-words pr-2 text-slate-600">{{ $item->ticketTier->name }} x {{ $item->quantity }}</span>
                                        <span class="shrink-0 font-semibold text-slate-900">{{ $item->unit_price == 0 ? 'Free' : ticketly_money($item->subtotal) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-9 border-t border-slate-200 pt-7 text-[1rem] text-slate-500">
                                <div class="flex items-center justify-between">
                                    <span>Subtotal</span>
                                    <span data-summary="subtotal" class="font-medium text-slate-900">{{ ticketly_money($reservation->subtotal) }}</span>
                                </div>
                                <div class="mt-4 flex items-center justify-between">
                                    <span>Portal Fee ({{ ticketly_format_percentage(ticketly_setting('portal_fee_percentage', $portalFeePct)) }}%)</span>
                                    <span data-summary="portal-fee" class="font-medium text-slate-900">{{ ticketly_money($initialPricing['portal_fee']) }}</span>
                                </div>
                                <div class="mt-4 flex items-center justify-between">
                                    <span>Service Fee ({{ ticketly_format_percentage(ticketly_setting('service_fee_percentage', $feePct)) }}%)</span>
                                    <span data-summary="service-fee" class="font-medium text-slate-900">{{ ticketly_money($initialPricing['service_fee']) }}</span>
                                </div>
                                <div data-discount-row class="{{ $initialDiscount > 0 ? '' : 'hidden ' }}mt-4 flex items-center justify-between text-emerald-600">
                                    <span data-discount-label>{{ $initialDiscountLabel }}</span>
                                    <span data-discount-value>-{{ ticketly_money($initialDiscount) }}</span>
                                </div>
                                <div class="mt-8 flex items-center justify-between border-t border-slate-200 pt-6 text-[1.05rem] font-extrabold text-slate-900 sm:text-[1.15rem]">
                                    <span>Total</span>
                                    <span data-summary="total" class="text-[1.2rem] text-violet-600 sm:text-[1.3rem]">{{ ticketly_money($initialTotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-white px-5 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.04)] max-[375px]:px-4 max-[375px]:py-4 sm:px-6">
                        <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-3 max-[375px]:gap-2">
                            <label for="promo-input" class="sr-only">Enter promo code</label>
                            <input id="promo-input" type="text" placeholder="Promo code"
                                   value="{{ $reservation->promoCode?->code }}"
                                   class="h-14 min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-4 text-[0.95rem] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-300 focus:ring-4 focus:ring-violet-100 max-[375px]:h-12 max-[375px]:px-3 max-[375px]:text-[0.9rem] sm:h-16 sm:px-5 sm:text-[1rem]"
                                   style="text-transform:uppercase">
                            <button type="button" id="apply-promo"
                                    class="inline-flex h-14 min-w-[92px] shrink-0 items-center justify-center whitespace-nowrap rounded-2xl border border-slate-200 bg-[#f7f7fb] px-5 text-[0.95rem] font-medium text-slate-700 transition hover:bg-slate-50 max-[375px]:h-12 max-[375px]:min-w-[80px] max-[375px]:px-4 max-[375px]:text-[0.88rem] sm:h-16 sm:px-7 sm:text-[1rem]"
                                    aria-label="Apply promo code">
                                Apply
                            </button>
                        </div>
                        <div id="promo-result" class="mt-3 text-sm" aria-live="polite" role="status"></div>
                    </div>

                    <div class="hidden items-center justify-center gap-3 pt-3 text-[1rem] text-slate-500 lg:flex">
                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4Z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9.5 12 1.7 1.7 3.8-4"></path>
                        </svg>
                        <span>Secure checkout</span>
                    </div>
                </div>

                <div id="mobile-continue-bar"
                     class="mobile-checkout-cta fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/95 px-4 pt-4 shadow-[0_-18px_40px_rgba(15,23,42,0.08)] backdrop-blur transition duration-300 lg:hidden max-[375px]:px-3 sm:px-6">
                    <div class="mx-auto max-w-[1480px]">
                        <button id="mobile-continue-btn" type="button" aria-controls="contact-section"
                                onclick="window.location.hash='contact-section';document.getElementById('contact-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });"
                                class="inline-flex h-16 w-full items-center justify-center rounded-2xl bg-[linear-gradient(90deg,#7c3aed,#8b5cf6)] px-6 text-[1.02rem] font-medium text-white shadow-[0_18px_45px_rgba(124,58,237,0.24)] max-[375px]:h-14 max-[375px]:text-[0.95rem] sm:h-[70px] sm:text-[1.08rem]"
                                style="color:#ffffff !important;">
                            Continue to Payment
                        </button>

                        <div class="mt-3 text-center text-[0.95rem] text-slate-400 max-[375px]:text-[0.85rem]">
                            Secure checkout | Tickets delivered instantly
                        </div>
                    </div>
                </div>

                <div class="mt-7 rounded-[28px] border border-slate-200 bg-white px-6 py-7 text-center shadow-[0_10px_30px_rgba(15,23,42,0.04)] max-[375px]:px-4 max-[375px]:py-5 lg:hidden">
                    <div class="flex items-center justify-center gap-3 text-[1rem] font-semibold text-slate-700 max-[375px]:text-[0.9rem]">
                        <svg class="h-8 w-8 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10V7a5 5 0 0 1 10 0v3"></path>
                            <rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8"></rect>
                        </svg>
                        <span>Secure Payment</span>
                    </div>
                    <!-- <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <div class="flex h-16 w-[92px] items-center justify-center rounded-2xl border border-slate-200 text-[0.9rem] font-extrabold text-[#2346b7]">VISA</div>
                        <div class="flex h-16 w-[74px] items-center justify-center rounded-2xl border border-slate-200">
                            <span class="h-6 w-6 rounded-full bg-[#ee6b5a] opacity-95"></span>
                            <span class="-ml-2 h-6 w-6 rounded-full bg-[#f3b15d] opacity-95"></span>
                        </div>
                        <div class="flex h-16 w-[94px] items-center justify-center rounded-2xl bg-[#2563eb] text-[0.9rem] font-extrabold text-white">AMEX</div>
                        <div class="flex h-16 w-[104px] items-center justify-center rounded-2xl border border-slate-200 text-[0.9rem] font-semibold text-slate-800">G Pay</div>
                        <div class="flex h-16 w-[70px] items-center justify-center rounded-2xl bg-black text-[0.9rem] font-semibold text-white">Pay</div>
                    </div> -->
                </div>

            </aside>
        </div>
    </div>
    <div id="payment-processing-overlay" class="hidden fixed inset-0 z-50 bg-white/95 backdrop-blur-sm">
        <div class="h-full w-full flex items-center justify-center px-4" role="status" aria-live="polite">
            <div class="text-center max-w-md">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(139,92,246,0.2));border:2px solid rgba(99,102,241,0.4)">
                    <svg class="w-10 h-10 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Processing Your Payment</h2>
                <p class="text-slate-600">Please wait, this usually takes a few seconds.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const promoApplyUrl = '{{ route('checkout.promo', $reservation->token) }}';
    const intentUrl  = '{{ route('checkout.intent', $reservation->token) }}';
    const statusUrl  = '{{ route('checkout.poll', $reservation->token) }}';
    const checkoutSuccessUrl = '{{ route('checkout.success', $reservation->token) }}';
    const eventPageUrl = '{{ route('events.show', $reservation->event->slug) }}';
    const eventUrl   = @js($ticketSelectionUrl);
    const releaseUrl = '{{ route('reservation.release', $reservation->token) }}';
    const csrfToken  = document.querySelector('meta[name=csrf-token]').content;
    const checkoutActiveTokenKey = 'ticketly:checkout-active-token';
    const checkoutSuccessUrlKey = 'ticketly:checkout-success-url';
    const checkoutCompleteTokenKey = 'ticketly:checkout-complete-token';
    const checkoutCompleteRedirectKey = 'ticketly:checkout-complete-redirect';
    let stripe, elements, cardElement;
    let cardMounted = false;
    let intentReady = false;
    let paymentInProgress = false;
    let lockPayButton = false;
    let currentIntentId = null;
    let currentClientSecret = null;
    let resolvedBillingProfile = null;
    let isExpired = false;
    let promoCode = @js((string) optional($reservation->promoCode)->code);
    let promoCodeType = @js(optional($reservation->promoCode)->type);
    let promoCodeValue = @js($reservation->promoCode ? (float) $reservation->promoCode->value : null);
    let discountAmount = {{ $initialDiscount }};
    let currentTotal = {{ (float) $initialTotal }};
    const subtotal = {{ (float) $reservation->subtotal }};
    const feePct = {{ (float) ticketly_setting('service_fee_percentage', config('ticketly.service_fee_percentage', 5)) }};
    const portalFeePct = {{ (float) ticketly_setting('portal_fee_percentage', config('ticketly.portal_fee_percentage', 10)) }};
    const currencySymbol = @js(ticketly_currency_symbol());
    const stripePublishableKey = @js((string) config('services.stripe.key'));
    const currentTheme = () => document.documentElement.getAttribute('data-theme-lock') === 'light'
        || document.documentElement.getAttribute('data-theme') === 'light'
        ? 'light'
        : 'dark';

    function cardElementStyle(theme) {
        if (theme === 'light') {
            return {
                base: {
                    color: '#0f172a',
                    fontSize: '16px',
                    fontFamily: 'Inter, sans-serif',
                    fontSmoothing: 'antialiased',
                    '::placeholder': {
                        color: '#94a3b8',
                    },
                },
                invalid: {
                    color: '#be123c',
                    iconColor: '#be123c',
                },
            };
        }

        return {
            base: {
                color: '#f8fafc',
                fontSize: '16px',
                fontFamily: 'Inter, sans-serif',
                fontSmoothing: 'antialiased',
                '::placeholder': {
                    color: '#94a3b8',
                },
            },
            invalid: {
                color: '#fecaca',
                iconColor: '#fecaca',
            },
        };
    }

    function ensureStripeCardMounted() {
        const mountPoint = document.getElementById('card-element');

        if (!stripePublishableKey) {
            intentReady = false;
            setPaymentPlaceholder('Card form is temporarily unavailable. Please refresh and try again.');
            showError('Stripe publishable key is missing.');
            return false;
        }

        if (typeof window.Stripe !== 'function') {
            intentReady = false;
            setPaymentPlaceholder('Stripe.js did not load. Please refresh and try again.');
            showError('Unable to load Stripe card form right now.');
            return false;
        }

        if (!mountPoint) {
            intentReady = false;
            showError('Card input container is missing on the page.');
            return false;
        }

        if (!stripe) {
            stripe = window.Stripe(stripePublishableKey);
        }

        if (!stripe) {
            intentReady = false;
            setPaymentPlaceholder('Unable to start Stripe card form. Please refresh and try again.');
            showError('Stripe could not be initialized.');
            return false;
        }

        if (!elements) {
            elements = stripe.elements();
        }

        if (!cardElement) {
            cardElement = elements.create('card', {
                hidePostalCode: true,
                style: cardElementStyle(currentTheme()),
            });

            cardElement.on('change', (event) => {
                if (event.error?.message) {
                    showError(event.error.message);
                } else {
                    hideError();
                }
            });
        }

        if (!cardMounted) {
            cardElement.mount('#card-element');
            cardMounted = true;
        }

        setPaymentPlaceholder('');
        return true;
    }

    function updateCountdownDisplays(seconds) {
        const safeSeconds = Math.max(0, seconds);
        const m = String(Math.floor(safeSeconds / 60)).padStart(2, '0');
        const s = String(safeSeconds % 60).padStart(2, '0');
        document.querySelectorAll('[data-countdown-display]').forEach((el) => {
            el.textContent = m + ':' + s;
            el.setAttribute('aria-label', m + ' minutes ' + s + ' seconds remaining');
        });
    }

    let secs = {{ $reservation->secondsRemaining() }};
    updateCountdownDisplays(secs);
    const tick = setInterval(() => {
        if (isExpired) return;
        if (secs <= 0) {
            clearInterval(tick);
            updateCountdownDisplays(0);
            handleExpiry('Your 10-minute hold has expired. Tickets are now being released.');
            return;
        }
        secs--;
        updateCountdownDisplays(secs);
    }, 1000);

    function setTermsError(message) {
        const termsCheckbox = document.getElementById('terms-checkbox');
        const errorEl = document.getElementById('terms-checkbox-error');
        if (!termsCheckbox || !errorEl) return;

        if (message) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            termsCheckbox.setAttribute('aria-invalid', 'true');
        } else {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            termsCheckbox.removeAttribute('aria-invalid');
        }
    }

    function setFieldError(fieldId, message, options = {}) {
        const { markInvalid = true, feedbackType = message ? 'error' : '' } = options;
        const input = document.getElementById(fieldId);
        const errorEl = document.getElementById(fieldId + '-error');
        if (!input || !errorEl) return;

        if (message) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            input.classList.add('border-rose-400');
            errorEl.dataset.feedbackType = feedbackType;

            if (markInvalid) {
                input.setAttribute('aria-invalid', 'true');
            } else {
                input.removeAttribute('aria-invalid');
            }
        } else {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            input.classList.remove('border-rose-400');
            input.removeAttribute('aria-invalid');
            delete errorEl.dataset.feedbackType;
        }
    }

    function getFieldValidationError(fieldId, requireValue = true) {
        const value = document.getElementById(fieldId)?.value.trim() || '';
        let error = '';

        if (fieldId === 'customer-name') {
            const fullNamePattern = /^[A-Za-z .']+$/;
            if (requireValue && !value) error = 'Full name is required.';
            else if (value.length > 100) error = 'Full name may not be greater than 100 characters.';
            else if (value && !fullNamePattern.test(value)) error = 'Full name may only contain letters, spaces, dots, and apostrophes.';
        }

        if (fieldId === 'customer-email') {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (requireValue && !value) error = 'Email address is required.';
            else if (value.length > 100) error = 'Email address may not be greater than 100 characters.';
            else if (value && !emailPattern.test(value)) error = 'Please enter a valid email address.';
        }

        if (fieldId === 'customer-phone') {
            const phonePattern = /^\d{11}$/;
            if (requireValue && !value) error = 'Phone number is required.';
            else if (value && !value.startsWith('07')) error = 'Phone number must start with 07';
            else if (value && !phonePattern.test(value)) error = 'Phone Number Must Be Exactly 11 digits';
        }

        return error;
    }

    function validateField(fieldId, requireValue = true) {
        const error = getFieldValidationError(fieldId, requireValue);
        setFieldError(fieldId, error);
        return !error;
    }

    function applyFieldLimitNotice(fieldId) {
        const input = document.getElementById(fieldId);
        const errorEl = document.getElementById(fieldId + '-error');
        const maxLength = Number(input?.getAttribute('maxlength') || 0);
        const fieldLimitMessages = {
            'customer-name': 'Full name maximum limit reached.',
            'customer-email': 'Email address maximum limit reached.',
        };
        const limitMessage = fieldLimitMessages[fieldId];

        if (!input || !errorEl || !limitMessage || maxLength <= 0) {
            return;
        }

        if (getFieldValidationError(fieldId, false)) {
            return;
        }

        if (input.value.length >= maxLength) {
            setFieldError(fieldId, limitMessage, {
                markInvalid: false,
                feedbackType: 'notice',
            });
            return;
        }

        if (errorEl.dataset.feedbackType === 'notice') {
            setFieldError(fieldId, '');
        }
    }

    function validateCustomerDetails(showInlineErrors = true, requireComplete = true) {
        const fields = ['customer-name', 'customer-email', 'customer-phone'];
        let valid = true;

        fields.forEach((fieldId) => {
            const fieldValid = validateField(fieldId, requireComplete);
            if (!fieldValid) valid = false;
            if (!showInlineErrors) {
                setFieldError(fieldId, '');
            }
        });

        return valid;
    }

    function hasValidCustomerDetails() {
        return ['customer-name', 'customer-email', 'customer-phone'].every((fieldId) => {
            return !getFieldValidationError(fieldId, true);
        });
    }

    function getCustomerDetails() {
        return {
            name: document.getElementById('customer-name')?.value.trim() || '',
            email: document.getElementById('customer-email')?.value.trim() || '',
            phone: document.getElementById('customer-phone')?.value.trim() || '',
        };
    }

    function applyServerValidationErrors(errors = {}) {
        const fieldMap = {
            name: 'customer-name',
            email: 'customer-email',
            phone: 'customer-phone',
        };

        Object.entries(fieldMap).forEach(([key, fieldId]) => {
            setFieldError(fieldId, errors?.[key]?.[0] || '');
        });
    }

    async function fetchIntent(options = {}) {
        const { showLoading = true, showInlineErrors = true } = options;
        const customer = getCustomerDetails();

        if (isExpired) {
            handleExpiry('Your hold has already expired.');
            return;
        }

        if (paymentInProgress) {
            return;
        }

        if (!validateCustomerDetails(showInlineErrors, true)) {
            refreshPayButtonState();
            return null;
        }

        if (showLoading) {
            document.getElementById('pay-btn-text').classList.add('hidden');
            document.getElementById('pay-spinner').classList.remove('hidden');
        }

        try {
            const payload = {
                promo_code: promoCode,
                name: customer.name,
                email: customer.email,
                phone: customer.phone,
            };

            const res = await fetch(intentUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            let data = {};
            try {
                data = await res.json();
            } catch (e) {
                setPaymentPlaceholder('Card form could not load right now. Please refresh and try again.');
                showError('Unable to load card form right now. Please refresh and try again.');
                return null;
            }

            if (data.error) {
                if (data.expired) {
                    handleExpiry(data.error || 'Your ticket hold has expired.', data.redirect_to);
                    return;
                }
                setPaymentPlaceholder('Card form is temporarily unavailable. Please try again.');
                showError(data.error);
                return;
            }
            if (!res.ok) {
                applyServerValidationErrors(data?.errors || {});
                const firstValidationError = data?.errors
                    ? Object.values(data.errors)[0]?.[0]
                    : null;
                setPaymentPlaceholder('Card form could not be prepared right now. Please try again.');
                showError(firstValidationError || data.message || 'Unable to prepare payment details right now.');
                return;
            }
            if (data.free && data.redirect) {
                showProcessingOverlay();
                window.location.replace(data.redirect);
                return;
            }

            applyServerValidationErrors({});
            discountAmount = data.discount || 0;
            updateSummaryUI(subtotal, discountAmount, data.portal_fee, data.service_fee, data.amount);
            resolvedBillingProfile = data.billing_profile || resolvedBillingProfile;
            currentIntentId = data.intent_id || currentIntentId;
            currentClientSecret = data.client_secret || currentClientSecret;
            intentReady = !!currentClientSecret;
            return data;
        } catch (e) {
            setPaymentPlaceholder('Unable to load card form right now. Please refresh and try again.');
            showError(e?.message || 'Unable to load payment right now. Please try again.');
            return null;
        } finally {
            if (showLoading) {
                document.getElementById('pay-btn-text').classList.remove('hidden');
                document.getElementById('pay-spinner').classList.add('hidden');
            }
            refreshPayButtonState();
        }
    }

    function refreshPayButtonState() {
        const payBtn = document.getElementById('pay-btn');
        const termsCheckbox = document.getElementById('terms-checkbox');
        if (!payBtn) return;
        if (lockPayButton) {
            payBtn.disabled = true;
            return;
        }
        const termsAccepted = !!termsCheckbox?.checked;
        const customerDetailsValid = hasValidCustomerDetails();
        payBtn.disabled = isExpired || paymentInProgress || !termsAccepted || !customerDetailsValid;
    }

    let detailsTimer;
    const phoneInput = document.getElementById('customer-phone');

    if (phoneInput) {
        const sanitizePhoneValue = (value) => value.replace(/\D/g, '').slice(0, 11);

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
            validateField('customer-phone', false);
        });

        phoneInput.addEventListener('input', function () {
            const sanitizedValue = sanitizePhoneValue(this.value);
            if (this.value !== sanitizedValue) {
                this.value = sanitizedValue;
            }

            validateField('customer-phone', false);
        });
    }

    ['customer-name', 'customer-email', 'customer-phone'].forEach((id) => {
        const field = document.getElementById(id);
        if (!field) return;

        field.addEventListener('input', () => {
            validateField(id, false);
            applyFieldLimitNotice(id);
            refreshPayButtonState();
        });

        field.addEventListener('blur', () => {
            validateField(id, true);
            applyFieldLimitNotice(id);
            refreshPayButtonState();
            if (!hasValidCustomerDetails() || isExpired || paymentInProgress) return;
            clearTimeout(detailsTimer);
            detailsTimer = setTimeout(() => fetchIntent({ showLoading: false }), 300);
        });
    });

    document.getElementById('terms-checkbox')?.addEventListener('change', (event) => {
        if (event.target.checked) {
            setTermsError('');
            ensureStripeCardMounted();
        }
        refreshPayButtonState();
    });

    document.getElementById('apply-promo')?.addEventListener('click', async () => {
        if (isExpired) {
            handleExpiry('Your hold has expired. Promo code can no longer be applied.');
            return;
        }

        const applyPromoBtn = document.getElementById('apply-promo');
        const promoInput = document.getElementById('promo-input');
        const code = promoInput?.value.trim().toUpperCase() || '';
        const resultEl = document.getElementById('promo-result');

        if (applyPromoBtn) {
            applyPromoBtn.disabled = true;
        }

        try {
            const res = await fetch(promoApplyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ promo_code: code }),
            });

            const data = await res.json();

            if (data.expired) {
                handleExpiry(data.error || 'Your hold has expired.', data.redirect_to);
                return;
            }

            if (!res.ok || data.valid === false) {
                const promoError = data?.errors?.promo_code?.[0] || data.message || 'Unable to apply promo code right now.';
                resultEl.textContent = 'Payment discount unavailable: ' + promoError;
                resultEl.className = 'mt-3 text-sm text-rose-600';
                return;
            }

            promoCode = data.code || '';
            promoCodeType = data.promo_type || null;
            promoCodeValue = data.promo_value ?? null;
            if (promoInput) {
                promoInput.value = promoCode;
            }

            discountAmount = data.discount || 0;
            currentTotal = data.amount || 0;
            updateSummaryUI(subtotal, discountAmount, data.portal_fee, data.service_fee, currentTotal);

            resultEl.textContent = promoCode ? 'Applied: ' + data.message : data.message;
            resultEl.className = 'mt-3 text-sm text-emerald-600';

            if (hasValidCustomerDetails()) {
                await fetchIntent({ showLoading: false, showInlineErrors: false });
            }
        } catch (error) {
            resultEl.textContent = 'Payment discount unavailable: ' + (error?.message || 'Unable to apply promo code right now.');
            resultEl.className = 'mt-3 text-sm text-rose-600';
        } finally {
            if (applyPromoBtn) {
                applyPromoBtn.disabled = false;
            }
        }
    });

    document.getElementById('promo-input')?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        document.getElementById('apply-promo')?.click();
    });

    const mobileContinueBar = document.getElementById('mobile-continue-bar');
    const contactSection = document.getElementById('contact-section');
    const paymentSection = document.getElementById('payment-section');

    document.getElementById('mobile-continue-btn')?.addEventListener('click', () => {
        contactSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    function refreshMobileContinueBarVisibility() {
        if (!mobileContinueBar || !paymentSection) return;

        if (window.innerWidth >= 1024) {
            mobileContinueBar.classList.add('translate-y-full', 'opacity-0', 'pointer-events-none');
            return;
        }

        const paymentSectionTop = paymentSection.getBoundingClientRect().top;
        const shouldHide = paymentSectionTop <= Math.max(window.innerHeight * 0.7, 320);

        mobileContinueBar.classList.toggle('translate-y-full', shouldHide);
        mobileContinueBar.classList.toggle('opacity-0', shouldHide);
        mobileContinueBar.classList.toggle('pointer-events-none', shouldHide);
    }

    refreshMobileContinueBarVisibility();
    window.addEventListener('scroll', refreshMobileContinueBarVisibility, { passive: true });
    window.addEventListener('resize', refreshMobileContinueBarVisibility);

    document.getElementById('pay-btn')?.addEventListener('click', async () => {
        if (lockPayButton) return;
        lockPayButton = true;
        refreshPayButtonState();

        if (isExpired) {
            handleExpiry('Payment is disabled because your hold has expired.');
            lockPayButton = false;
            refreshPayButtonState();
            return;
        }

        if (!document.getElementById('terms-checkbox')?.checked) {
            setTermsError('Please accept Terms of Service and Privacy Policy before checkout.');
            lockPayButton = false;
            refreshPayButtonState();
            return;
        }
        setTermsError('');

        if (!validateCustomerDetails(true, true)) {
            lockPayButton = false;
            refreshPayButtonState();
            return;
        }

        if (!ensureStripeCardMounted()) {
            lockPayButton = false;
            refreshPayButtonState();
            return;
        }

        const intentData = await fetchIntent({ showLoading: false, showInlineErrors: true });
        if (!intentData || !intentReady || !cardMounted || !cardElement || !currentClientSecret) {
            showError('Card form is not ready yet. Please wait a moment and try again.');
            lockPayButton = false;
            refreshPayButtonState();
            return;
        }

        paymentInProgress = true;
        refreshPayButtonState();

        document.getElementById('pay-btn-text').classList.add('hidden');
        document.getElementById('pay-spinner').classList.remove('hidden');
        hideError();
        showProcessingOverlay();

        try {
            const customerDetails = getCustomerDetails();
            const billingDetails = {};
            const fallbackBillingName = resolvedBillingProfile?.name || 'Guest Customer';
            const fallbackBillingAddress = resolvedBillingProfile?.address || null;
            billingDetails.name = customerDetails.name || fallbackBillingName;
            if (customerDetails.email) billingDetails.email = customerDetails.email;
            if (customerDetails.phone) billingDetails.phone = customerDetails.phone;
            if (fallbackBillingAddress) billingDetails.address = fallbackBillingAddress;

            const confirmPayload = {
                payment_method: {
                    card: cardElement,
                    billing_details: billingDetails,
                },
            };

            const { error, paymentIntent } = await stripe.confirmCardPayment(currentClientSecret, confirmPayload, {
                handleActions: true,
            });

            document.getElementById('pay-btn-text').classList.remove('hidden');
            document.getElementById('pay-spinner').classList.add('hidden');
            paymentInProgress = false;
            refreshPayButtonState();

            if (error) {
                clearCheckoutTransitionState();
                showError(error.message || 'Payment could not be processed. Please try again.');
                hideProcessingOverlay();
                lockPayButton = false;
                refreshPayButtonState();
                return;
            }

            if (paymentIntent?.status === 'succeeded' || paymentIntent?.status === 'processing') {
                await resolveSuccessfulPaymentRedirect();
                return;
            }

            if (paymentIntent?.status === 'requires_payment_method' || paymentIntent?.status === 'canceled') {
                clearCheckoutTransitionState();
                showError('Payment could not be completed. Please check your card details and try again.');
                hideProcessingOverlay();
                lockPayButton = false;
                refreshPayButtonState();
                return;
            }

            await resolveSuccessfulPaymentRedirect();
        } catch (e) {
            document.getElementById('pay-btn-text').classList.remove('hidden');
            document.getElementById('pay-spinner').classList.add('hidden');
            paymentInProgress = false;
            lockPayButton = false;
            refreshPayButtonState();
            clearCheckoutTransitionState();
            hideProcessingOverlay();
            showError(e?.message || 'We could not confirm your payment right now. Please try again.');
        }
    });

    function showError(msg) {
        const el = document.getElementById('payment-message');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideError() {
        const el = document.getElementById('payment-message');
        if (!el) return;
        el.textContent = '';
        el.classList.add('hidden');
    }

    function setPaymentPlaceholder(message) {
        const placeholder = document.getElementById('payment-placeholder');
        const element = document.getElementById('card-element');
        if (!placeholder || !element) return;

        if (message) {
            placeholder.textContent = message;
            placeholder.classList.remove('hidden');
            if (!cardMounted) element.classList.add('hidden');
        } else {
            placeholder.textContent = '';
            placeholder.classList.add('hidden');
            element.classList.remove('hidden');
        }
    }

    function showProcessingOverlay() {
        document.getElementById('payment-processing-overlay')?.classList.remove('hidden');
    }

    function hideProcessingOverlay() {
        document.getElementById('payment-processing-overlay')?.classList.add('hidden');
    }

    function getCheckoutTransitionItem(key) {
        try {
            return sessionStorage.getItem(key) || '';
        } catch (error) {
            return '';
        }
    }

    function setCheckoutTransitionItem(key, value) {
        try {
            sessionStorage.setItem(key, value);
        } catch (error) {
            // Ignore storage access failures.
        }
    }

    function removeCheckoutTransitionItem(key) {
        try {
            sessionStorage.removeItem(key);
        } catch (error) {
            // Ignore storage access failures.
        }
    }

    function markCheckoutProcessingActive() {
        setCheckoutTransitionItem(checkoutActiveTokenKey, '{{ $reservation->token }}');
        setCheckoutTransitionItem(checkoutSuccessUrlKey, checkoutSuccessUrl);
    }

    function markCheckoutProcessingComplete(redirectUrl) {
        markCheckoutProcessingActive();
        setCheckoutTransitionItem(checkoutCompleteTokenKey, '{{ $reservation->token }}');
        setCheckoutTransitionItem(checkoutCompleteRedirectKey, redirectUrl);
    }

    function clearCheckoutTransitionState() {
        if (getCheckoutTransitionItem(checkoutActiveTokenKey) === '{{ $reservation->token }}') {
            removeCheckoutTransitionItem(checkoutActiveTokenKey);
        }
        if (getCheckoutTransitionItem(checkoutCompleteTokenKey) === '{{ $reservation->token }}') {
            removeCheckoutTransitionItem(checkoutCompleteTokenKey);
        }
        if (getCheckoutTransitionItem(checkoutSuccessUrlKey) === checkoutSuccessUrl) {
            removeCheckoutTransitionItem(checkoutSuccessUrlKey);
        }
        removeCheckoutTransitionItem(checkoutCompleteRedirectKey);
    }

    async function resolveSuccessfulPaymentRedirect() {
        markCheckoutProcessingActive();
        showProcessingOverlay();

        try {
            const res = await fetch(statusUrl + '?attempt=1', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (data.status === 'paid' && data.redirect) {
                markCheckoutProcessingComplete(data.redirect);
                window.location.replace(data.redirect);
                return true;
            }

            if (data.status === 'failed') {
                clearCheckoutTransitionState();
                showError(data.message || 'Payment could not be completed. Please check your card details and try again.');
                hideProcessingOverlay();
                lockPayButton = false;
                refreshPayButtonState();
                return false;
            }

            if (data.status === 'expired') {
                clearCheckoutTransitionState();
                handleExpiry(data.message || 'Your hold has expired.', data.redirect_to);
                return false;
            }
        } catch (error) {
            // Fall back to the dedicated processing page.
        }

        window.location.replace(checkoutSuccessUrl);
        return true;
    }

    function normalizeRedirect(redirectTo) {
        if (!redirectTo) return eventUrl;
        return redirectTo === eventPageUrl ? eventUrl : redirectTo;
    }

    function setCheckoutDisabled(disabled) {
        const payBtn = document.getElementById('pay-btn');
        const applyPromoBtn = document.getElementById('apply-promo');
        const continueBtn = document.getElementById('mobile-continue-btn');
        const editableIds = ['customer-name', 'customer-email', 'customer-phone', 'promo-input', 'terms-checkbox'];

        if (payBtn) payBtn.disabled = disabled;
        if (applyPromoBtn) applyPromoBtn.disabled = disabled;
        if (continueBtn) continueBtn.disabled = disabled;
        editableIds.forEach((id) => {
            const input = document.getElementById(id);
            if (input) input.disabled = disabled;
        });
    }

    async function releaseHoldSilently() {
        try {
            await fetch(releaseUrl, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
        } catch (e) {
            // Best-effort release; backend also releases on expiry checks.
        }
    }

    function handleExpiry(message, redirectTo = eventUrl) {
        if (isExpired) return;
        isExpired = true;
        clearInterval(tick);
        setCheckoutDisabled(true);

        const alertEl = document.getElementById('hold-expired-alert');
        const msgEl = document.getElementById('hold-expired-message');
        const returnLink = document.getElementById('hold-expired-return-link');
        const fallbackMsg = 'Your hold has expired. Tickets will be released and payment will not be processed.';

        const finalRedirect = normalizeRedirect(redirectTo);

        updateCountdownDisplays(0);
        if (msgEl) msgEl.textContent = message || fallbackMsg;
        if (returnLink) returnLink.href = finalRedirect;
        if (alertEl) alertEl.classList.remove('hidden');

        showError('Your hold expired. Payment is disabled and no payment will be taken.');
        clearCheckoutTransitionState();
        releaseHoldSilently();
        setTimeout(() => { window.location.href = finalRedirect; }, 4000);
    }

    async function syncReservationStatus() {
        if (isExpired) return;
        try {
            const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (data.status === 'expired') {
                handleExpiry(data.message || 'Your hold has expired.', data.redirect_to);
                return;
            }

            if (typeof data.expires === 'number') {
                secs = Math.max(0, data.expires);
                updateCountdownDisplays(secs);
            }
        } catch (e) {
            showError('We could not verify your reservation status right now. Please check your connection and continue checkout promptly.');
        }
    }

    function updateSummaryUI(sub, disc, portalFee, serviceFee, total) {
        const resolvedTotal = Number.isFinite(total) ? total : sub;

        document.querySelectorAll('[data-summary="subtotal"]').forEach((el) => {
            el.textContent = currencySymbol + sub.toFixed(2);
        });
        document.querySelectorAll('[data-summary="portal-fee"]').forEach((el) => {
            el.textContent = currencySymbol + (portalFee || 0).toFixed(2);
        });
        document.querySelectorAll('[data-summary="service-fee"]').forEach((el) => {
            el.textContent = currencySymbol + (serviceFee || 0).toFixed(2);
        });
        document.querySelectorAll('[data-summary="total"]').forEach((el) => {
            el.textContent = currencySymbol + resolvedTotal.toFixed(2);
        });
        document.querySelectorAll('[data-discount-row]').forEach((row) => {
            row.classList.toggle('hidden', !(disc > 0));
        });
        document.querySelectorAll('[data-discount-value]').forEach((el) => {
            el.textContent = '-' + currencySymbol + disc.toFixed(2);
        });
        document.querySelectorAll('[data-discount-label]').forEach((el) => {
            el.textContent = formatDiscountLabel(promoCode, promoCodeType, promoCodeValue);
        });

        const finalTotal = document.getElementById('final-total');
        if (finalTotal) {
            finalTotal.textContent = resolvedTotal.toFixed(2);
        }
    }

    function formatDiscountLabel(code, type, value) {
        if (!code) {
            return 'Discount';
        }

        let promoValueLabel = '';
        const numericValue = Number(value);

        if (type === 'percentage' && Number.isFinite(numericValue)) {
            promoValueLabel = Number.isInteger(numericValue)
                ? `${numericValue}%`
                : `${numericValue.toFixed(2).replace(/\.?0+$/, '')}%`;
        } else if (Number.isFinite(numericValue)) {
            promoValueLabel = currencySymbol + numericValue.toFixed(2);
        }

        return promoValueLabel
            ? `Discount (${code} - ${promoValueLabel})`
            : `Discount (${code})`;
    }

    function updateTotals() {
        const portalFee = parseFloat((subtotal * portalFeePct / 100).toFixed(2));
        const serviceFee = parseFloat((subtotal * feePct / 100).toFixed(2));
        const grossTotal = parseFloat((subtotal + portalFee + serviceFee).toFixed(2));
        const total = Math.max(0, parseFloat((grossTotal - discountAmount).toFixed(2)));
        currentTotal = total;
        updateSummaryUI(subtotal, discountAmount, portalFee, serviceFee, total);
    }

    updateTotals();
    ensureStripeCardMounted();
    refreshPayButtonState();
    if (hasValidCustomerDetails()) {
        fetchIntent({ showLoading: false, showInlineErrors: false });
    }
    syncReservationStatus();
    setInterval(syncReservationStatus, 5000);

    window.addEventListener('ticketly:theme-changed', function () {
        if (!cardElement) return;
        try {
            cardElement.update({ style: cardElementStyle(currentTheme()) });
        } catch (e) {
            // Card element styling updates are best-effort only.
        }
    });
});
</script>
@endsection
