<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        return Inertia::render('Admin/Settings/Index', [
            'company' => $company,
        ]);
    }

    public function updateCompany(Request $request)
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        if (! $company) {
            return back()->with('error', 'Company not found.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trading_name' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'vat_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'default_currency' => ['required', 'string', 'size:3'],
            'fiscal_year_start' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $company->update($validated);

        return back()->with('success', 'Company settings updated successfully.');
    }

    public function updateLogo(Request $request)
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        if (! $company) {
            return back()->with('error', 'Company not found.');
        }

        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        // Delete old logo
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $path = $request->file('logo')->store('company-logos', 'public');
        $company->update(['logo' => $path]);

        return back()->with('success', 'Company logo updated successfully.');
    }

    public function paymentMethods(Request $request): Response
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        $paymentSettings = $company->settings['payments'] ?? [
            'cash' => ['enabled' => true, 'currencies' => ['USD', 'ZWL']],
            'ecocash' => ['enabled' => false, 'merchant_id' => ''],
            'mukuru' => ['enabled' => false, 'api_key' => ''],
            'card' => ['enabled' => false],
        ];

        return Inertia::render('Admin/Settings/PaymentMethods', [
            'paymentSettings' => $paymentSettings,
        ]);
    }

    public function updatePaymentMethods(Request $request)
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        $validated = $request->validate([
            'payments' => ['required', 'array'],
        ]);

        $settings = $company->settings ?? [];
        $settings['payments'] = $validated['payments'];
        $company->update(['settings' => $settings]);

        return back()->with('success', 'Payment settings updated successfully.');
    }

    public function taxSettings(Request $request): Response
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        $taxSettings = $company->settings['tax'] ?? [
            'default_rate' => 15,
            'inclusive' => true,
            'zimra_enabled' => false,
        ];

        return Inertia::render('Admin/Settings/Tax', [
            'taxSettings' => $taxSettings,
        ]);
    }

    public function updateTaxSettings(Request $request)
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        $validated = $request->validate([
            'default_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'inclusive' => ['boolean'],
            'zimra_enabled' => ['boolean'],
        ]);

        $settings = $company->settings ?? [];
        $settings['tax'] = $validated;
        $company->update(['settings' => $settings]);

        return back()->with('success', 'Tax settings updated successfully.');
    }

    public function receiptSettings(Request $request): Response
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        $receiptSettings = $company->settings['receipt'] ?? [
            'header' => $company->name,
            'footer' => 'Thank you for shopping with us!',
            'show_logo' => true,
            'show_vat' => true,
            'paper_size' => '80mm',
        ];

        return Inertia::render('Admin/Settings/Receipt', [
            'receiptSettings' => $receiptSettings,
            'company' => $company,
        ]);
    }

    public function updateReceiptSettings(Request $request)
    {
        $user = $request->user();
        $company = Company::find($user->tenant_id);

        $validated = $request->validate([
            'header' => ['nullable', 'string', 'max:255'],
            'footer' => ['nullable', 'string', 'max:255'],
            'show_logo' => ['boolean'],
            'show_vat' => ['boolean'],
            'paper_size' => ['required', 'in:58mm,80mm'],
        ]);

        $settings = $company->settings ?? [];
        $settings['receipt'] = $validated;
        $company->update(['settings' => $settings]);

        return back()->with('success', 'Receipt settings updated successfully.');
    }
}
