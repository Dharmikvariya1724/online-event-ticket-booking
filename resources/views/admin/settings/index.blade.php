@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configure platform defaults and integrations')

@section('content')
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
  <form method="POST" action="{{ route('admin.settings.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-5">
    @csrf

    <div>
      <label class="text-xs text-gray-400 uppercase">Service Fee %</label>
      <input type="number" step="0.01" name="service_fee_percentage" value="{{ $settings['service_fee_percentage'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div>
      <label class="text-xs text-gray-400 uppercase">Platform Fee %</label>
      <input type="number" step="0.01" name="portal_fee_percentage" value="{{ $settings['portal_fee_percentage'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div>
      <label class="text-xs text-gray-400 uppercase">Settlement Days</label>
      <input type="number" name="settlement_days" value="{{ $settings['settlement_days'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div>
      <label class="text-xs text-gray-400 uppercase">Support Email</label>
      <input type="email" name="support_email" value="{{ $settings['support_email'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <!-- <div>
      <label class="text-xs text-gray-400 uppercase">Admin Email</label>
      <input type="email" name="admin_email" value="{{ $settings['admin_email'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div> -->
    <div>
      <label class="text-xs text-gray-400 uppercase">Mail From Address</label>
      <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div>
      <label class="text-xs text-gray-400 uppercase">Mail From Name</label>
      <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <!-- <div class="flex items-center gap-3 mt-6">
      <input type="checkbox" name="allow_free_events" value="1" @checked($settings['allow_free_events'])>
      <span class="text-sm text-gray-300">Allow free events</span>
    </div> -->

    <div class="md:col-span-2 border-t border-gray-800 pt-4">
      <h3 class="text-sm font-semibold text-white mb-2">Stripe Keys</h3>
    </div>
    <div>
      <label class="text-xs text-gray-400 uppercase">Publishable Key</label>
      <input type="text" name="stripe_key" value="{{ $settings['stripe_key'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div>
      <label class="text-xs text-gray-400 uppercase">Secret Key</label>
      <input type="text" name="stripe_secret" value="{{ $settings['stripe_secret'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    </div>
    <div class="md:col-span-2">
      <label class="text-xs text-gray-400 uppercase">Webhook Secret</label>
      <input type="text" name="stripe_webhook_secret" value="{{ $settings['stripe_webhook_secret'] }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
      <p class="mt-2 text-xs text-gray-500">
        Use keys from a Stripe platform account with Stripe Connect enabled. Organiser onboarding cannot start until Connect is activated in Stripe Dashboard.
      </p>
    </div>

    <div class="md:col-span-2 flex justify-end">
      <button class="px-6 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Save Settings</button>
    </div>
  </form>
</div>
@endsection
