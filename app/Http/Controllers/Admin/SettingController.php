<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'service_fee_percentage' => SystemSetting::getValue('service_fee_percentage', config('ticketly.service_fee_percentage')),
            'portal_fee_percentage' => SystemSetting::getValue('portal_fee_percentage', config('ticketly.portal_fee_percentage')),
            'settlement_days' => SystemSetting::getValue('settlement_days', config('ticketly.settlement_days')),
            'support_email' => SystemSetting::getValue('support_email', config('ticketly.support_email')),
            'admin_email' => SystemSetting::getValue('admin_email', ''),
            'mail_from_address' => SystemSetting::getValue('mail_from_address', config('mail.from.address')),
            'mail_from_name' => SystemSetting::getValue('mail_from_name', config('mail.from.name')),
            'allow_free_events' => SystemSetting::getValue('allow_free_events', true),
            'stripe_key' => SystemSetting::getValue('stripe_key', config('services.stripe.key')),
            'stripe_secret' => SystemSetting::getValue('stripe_secret', config('services.stripe.secret')),
            'stripe_webhook_secret' => SystemSetting::getValue('stripe_webhook_secret', config('services.stripe.webhook_secret')),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $admin = $request->attributes->get('admin');

        $validated = $request->validate([
            'service_fee_percentage' => 'required|numeric|min:0|max:100',
            'portal_fee_percentage' => 'required|numeric|min:0|max:100',
            'settlement_days' => 'required|integer|min:0|max:365',
            'support_email' => 'required|email',
            'admin_email' => 'nullable|email',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:100',
            'allow_free_events' => 'nullable|boolean',
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
        ]);

        SystemSetting::setValue('service_fee_percentage', (float) $validated['service_fee_percentage'], 'float', $admin?->id);
        SystemSetting::setValue('portal_fee_percentage', (float) $validated['portal_fee_percentage'], 'float', $admin?->id);
        SystemSetting::setValue('settlement_days', (int) $validated['settlement_days'], 'integer', $admin?->id);
        SystemSetting::setValue('support_email', $validated['support_email'], 'string', $admin?->id);
        SystemSetting::setValue('admin_email', $validated['admin_email'] ?? '', 'string', $admin?->id);
        SystemSetting::setValue('mail_from_address', $validated['mail_from_address'] ?? '', 'string', $admin?->id);
        SystemSetting::setValue('mail_from_name', $validated['mail_from_name'] ?? '', 'string', $admin?->id);
        SystemSetting::setValue('allow_free_events', $request->boolean('allow_free_events'), 'boolean', $admin?->id);
        SystemSetting::setValue('stripe_key', $validated['stripe_key'] ?? '', 'string', $admin?->id);
        SystemSetting::setValue('stripe_secret', $validated['stripe_secret'] ?? '', 'string', $admin?->id);
        SystemSetting::setValue('stripe_webhook_secret', $validated['stripe_webhook_secret'] ?? '', 'string', $admin?->id);

        return back()->with('success', 'Settings updated successfully.');
    }
}
