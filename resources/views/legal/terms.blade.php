@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
<section class="bg-slate-50 px-4 py-12 text-slate-800 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] sm:p-10">
        <div class="border-b border-slate-200 pb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Legal</p>
            <h1 class="mt-3 text-3xl font-bold tracking-[-0.03em] text-slate-900 sm:text-4xl">Terms of Service</h1>
            <p class="mt-4 text-base leading-8 text-slate-600">
                These Terms of Service govern your access to and use of Ticketly by you or the organization you represent. By using Ticketly to browse events, purchase tickets, create events, or manage bookings, you agree to these terms.
            </p>
            <p class="mt-4 text-sm text-slate-500">Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <div class="mt-8 space-y-8 text-[1rem] leading-8 text-slate-700">
            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">1. Ticketly Services</h2>
                <p class="mt-3">
                    Ticketly provides tools that allow customers to discover events, reserve and purchase tickets, and receive booking confirmations. Ticketly also provides organiser-facing tools for creating event listings, managing inventory, promoting events, and communicating with attendees. We may update, improve, suspend, or discontinue portions of the service from time to time.
                </p>
                <p class="mt-3">
                    You may use Ticketly only for lawful business or personal purposes that are consistent with these terms and with any posted instructions, policies, or technical requirements that apply to the specific Ticketly feature you are using.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">2. Eligibility and Account Responsibilities</h2>
                <p class="mt-3">
                    You must provide accurate, complete, and current information when using Ticketly. If you create an account or submit booking details, you are responsible for maintaining the confidentiality of your credentials and for all activity that occurs under your account or booking session.
                </p>
                <p class="mt-3">
                    If you use Ticketly on behalf of a business, venue, organiser, or other entity, you represent that you are authorized to bind that entity to these terms. You must promptly notify Ticketly if you believe your account, booking session, or credentials have been accessed without authorization.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">3. Restrictions on Use</h2>
                <p class="mt-3">
                    You may not use Ticketly to engage in fraudulent, deceptive, abusive, harmful, or unlawful conduct. You may not interfere with the operation of Ticketly, attempt to gain unauthorized access to systems or data, misuse promotional tools, circumvent security or technical limitations, or use automated tools in a way that disrupts the platform.
                </p>
                <p class="mt-3">
                    You may not reproduce, resell, scrape, mirror, reverse engineer, decompile, or create derivative works from Ticketly except where applicable law permits such activity notwithstanding this restriction.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">4. Event Listings and Organiser Content</h2>
                <p class="mt-3">
                    Organisers are responsible for the accuracy, legality, and completeness of their event listings, schedules, pricing, policies, venue information, images, descriptions, and any other materials they publish through Ticketly. Ticketly may review, remove, suspend, or refuse any content or event listing that violates these terms, infringes third-party rights, or creates platform, legal, or reputational risk.
                </p>
                <p class="mt-3">
                    By submitting content to Ticketly, you confirm that you have all rights and permissions needed to use and share that content and to allow Ticketly to host, display, distribute, and process it in connection with operating, improving, and promoting the service.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">5. Tickets, Orders, and Payments</h2>
                <p class="mt-3">
                    Ticket purchases placed through Ticketly are subject to availability, pricing, taxes, fees, fraud screening, and successful payment authorization. A booking is not complete until Ticketly confirms the order. Ticketly may cancel, limit, or reject a transaction if pricing, inventory, event status, eligibility, or payment information is inaccurate or cannot be verified.
                </p>
                <p class="mt-3">
                    Organisers are responsible for their event-specific refund, cancellation, rescheduling, admission, and transfer policies unless Ticketly expressly states otherwise. If an event is changed, postponed, or cancelled, the applicable organiser policy and any required law will govern the available remedy.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">6. Privacy and Data Use</h2>
                <p class="mt-3">
                    Your use of Ticketly is also subject to our Privacy Policy. You agree that Ticketly may collect, use, store, and share information as described there in order to provide the platform, process bookings, communicate with users, prevent fraud, enforce these terms, comply with legal obligations, and improve the service.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">7. Intellectual Property</h2>
                <p class="mt-3">
                    Ticketly and its licensors own all rights, title, and interest in the Ticketly platform, branding, software, designs, interfaces, and related materials, except for content that users or organisers submit. No rights are granted except the limited right to use Ticketly in accordance with these terms.
                </p>
                <p class="mt-3">
                    If you provide feedback, suggestions, or feature requests, Ticketly may use them without restriction or obligation to you.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">8. Suspension and Termination</h2>
                <p class="mt-3">
                    Ticketly may suspend, restrict, or terminate access to the platform or specific features at any time if we reasonably believe there is a violation of these terms, a security issue, suspected fraud, legal risk, payment risk, or other misuse of the service. You may stop using Ticketly at any time, but obligations that by their nature should survive termination will continue after access ends.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">9. Disclaimers</h2>
                <p class="mt-3">
                    Ticketly is provided on an &ldquo;as is&rdquo; and &ldquo;as available&rdquo; basis to the maximum extent permitted by law. Ticketly does not guarantee uninterrupted availability, error-free operation, or that every event listing, third-party integration, or organiser submission will always be accurate, complete, or current.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">10. Limitation of Liability</h2>
                <p class="mt-3">
                    To the maximum extent permitted by law, Ticketly will not be liable for indirect, incidental, special, consequential, exemplary, or punitive damages, or for loss of profits, revenue, goodwill, data, or business opportunity arising out of or relating to your use of the platform. To the extent a limitation is permitted, Ticketly&rsquo;s total liability for any claim arising from these terms or the services will not exceed the amount you paid to Ticketly in connection with the transaction giving rise to the claim during the twelve months before the event that gave rise to the liability.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">11. Indemnity</h2>
                <p class="mt-3">
                    You will defend, indemnify, and hold harmless Ticketly and its affiliates, officers, directors, employees, and agents from and against claims, liabilities, damages, losses, and expenses arising out of or related to your content, your events, your misuse of Ticketly, your violation of these terms, or your infringement of any third-party rights.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">12. Changes to These Terms</h2>
                <p class="mt-3">
                    Ticketly may update these Terms of Service from time to time. When we do, we will post the revised version on this page and update the effective date above. Your continued use of Ticketly after the updated terms become effective means you accept the revised terms.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">13. Contact</h2>
                <p class="mt-3">
                    If you have questions about these Terms of Service, please contact Ticketly at <a href="mailto:{{ config('ticketly.support_email') }}" class="font-medium text-violet-600 hover:text-violet-700">{{ config('ticketly.support_email') }}</a>.
                </p>
            </section>
        </div>
    </div>
</section>
@endsection
