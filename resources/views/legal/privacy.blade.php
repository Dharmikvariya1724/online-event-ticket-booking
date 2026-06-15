@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<section class="bg-slate-50 px-4 py-12 text-slate-800 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] sm:p-10">
        <div class="border-b border-slate-200 pb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Legal</p>
            <h1 class="mt-3 text-3xl font-bold tracking-[-0.03em] text-slate-900 sm:text-4xl">Privacy Policy</h1>
            <p class="mt-4 text-base leading-8 text-slate-600">
                This Privacy Policy explains how Ticketly collects, uses, stores, shares, and protects personal information when you browse Ticketly, purchase tickets, create events, communicate with us, or otherwise interact with our services.
            </p>
            <p class="mt-4 text-sm text-slate-500">Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <div class="mt-8 space-y-8 text-[1rem] leading-8 text-slate-700">
            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">1. Information We Collect and How We Use It</h2>
                <p class="mt-3">
                    Ticketly collects information you provide directly, information generated through your use of the platform, and information we receive from organisers, payment partners, devices, and service providers. This may include your name, email address, phone number, billing or booking details, ticket selections, transaction records, event participation details, support communications, and device or browser information.
                </p>
                <p class="mt-3">
                    We use this information to provide Ticketly services, process transactions, deliver tickets, manage reservations, operate event tools, personalize your experience, communicate with you, prevent fraud, comply with legal obligations, and improve our products and platform operations.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">2. Additional Ways We Collect, Use, and Share Information</h2>
                <p class="mt-3">
                    We may collect information through cookies, analytics tools, server logs, event-related forms, customer support channels, promotional campaigns, social media interactions, and similar technologies. We use this information to analyze platform usage, maintain security, troubleshoot issues, measure performance, detect abuse, and make our services more relevant and reliable.
                </p>
                <p class="mt-3">
                    We may share information with organisers, payment processors, technology vendors, communications providers, fraud-prevention partners, professional advisers, regulators, or other parties when reasonably necessary to operate Ticketly, complete a transaction, fulfill a legal obligation, or protect users and the platform.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">3. Legal Bases for Processing</h2>
                <p class="mt-3">
                    Depending on where you are located, Ticketly may process personal information because it is necessary to perform a contract with you, because you have consented, because we have legitimate business interests in running and improving the platform, or because processing is necessary to comply with applicable law and regulatory requirements.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">4. Your Rights and Choices</h2>
                <p class="mt-3">
                    Subject to applicable law, you may have the right to access, update, correct, delete, restrict, object to, or receive a copy of certain personal information. You may also be able to withdraw consent for specific processing activities and manage communications preferences. We may need to verify your identity before completing certain requests.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">5. Security and Retention</h2>
                <p class="mt-3">
                    Ticketly uses administrative, technical, and organizational safeguards designed to protect personal information against unauthorized access, loss, misuse, alteration, or disclosure. No system can be guaranteed completely secure, but we work to maintain reasonable protections appropriate to the nature of the information we process.
                </p>
                <p class="mt-3">
                    We retain personal information for as long as needed to provide our services, comply with legal and financial obligations, resolve disputes, enforce agreements, and maintain legitimate business records.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">6. International Transfers</h2>
                <p class="mt-3">
                    Ticketly may process or store information in countries other than the one where you live. When we do so, we take reasonable steps to ensure that personal information continues to receive an appropriate level of protection consistent with applicable law.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">7. Updates and Notifications</h2>
                <p class="mt-3">
                    We may revise this Privacy Policy from time to time. When we make material updates, we will post the revised version on this page and update the effective date above. Where required by law, we will provide additional notice or obtain consent.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">8. Jurisdiction-Specific Information</h2>
                <p class="mt-3">
                    Additional disclosures or rights may apply depending on your country, state, or region. Where local law grants more specific privacy rights or requires extra disclosures, Ticketly will honor those requirements to the extent applicable.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-slate-900">9. Contact Us</h2>
                <p class="mt-3">
                    If you have questions about this Privacy Policy or about how Ticketly handles personal information, please contact us at <a href="mailto:{{ config('ticketly.support_email') }}" class="font-medium text-violet-600 hover:text-violet-700">{{ config('ticketly.support_email') }}</a>.
                </p>
            </section>
        </div>
    </div>
</section>
@endsection
