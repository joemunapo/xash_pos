<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenant = Tenant::find($user->tenant_id);

        $availableCurrencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'ZWL', 'name' => 'Zimbabwe Dollar', 'symbol' => 'ZWL'],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R'],
            ['code' => 'BWP', 'name' => 'Botswana Pula', 'symbol' => 'P'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'ZMW', 'name' => 'Zambian Kwacha', 'symbol' => 'K'],
            ['code' => 'MZN', 'name' => 'Mozambican Metical', 'symbol' => 'MT'],
        ];

        return Inertia::render('Admin/Settings/Index', [
            'tenant' => $tenant,
            'availableCurrencies' => $availableCurrencies,
            'enabledCurrencies' => $tenant->settings['enabled_currencies'] ?? [$tenant->default_currency],
        ]);
    }

    public function updateCompany(Request $request)
    {
        $user = $request->user();
        $tenant = Tenant::find($user->tenant_id);

        if (! $tenant) {
            return back()->with('error', 'Tenant not found.');
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
            'enabled_currencies' => ['required', 'array', 'min:1'],
            'enabled_currencies.*' => ['string', 'size:3'],
            'fiscal_year_start' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        // Ensure default currency is in enabled currencies
        if (! in_array($validated['default_currency'], $validated['enabled_currencies'])) {
            $validated['enabled_currencies'][] = $validated['default_currency'];
        }

        // Store enabled currencies in settings
        $settings = $tenant->settings ?? [];
        $settings['enabled_currencies'] = $validated['enabled_currencies'];

        $tenant->update([
            'name' => $validated['name'],
            'trading_name' => $validated['trading_name'],
            'registration_number' => $validated['registration_number'],
            'vat_number' => $validated['vat_number'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'website' => $validated['website'],
            'default_currency' => $validated['default_currency'],
            'fiscal_year_start' => $validated['fiscal_year_start'],
            'settings' => $settings,
        ]);

        return back()->with('success', 'Company settings updated successfully.');
    }

    public function updateLogo(Request $request)
    {
        $user = $request->user();
        $tenant = Tenant::find($user->tenant_id);

        if (! $tenant) {
            return back()->with('error', 'Tenant not found.');
        }

        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        // Delete old logo
        if ($tenant->logo) {
            Storage::disk('public')->delete($tenant->logo);
        }

        $path = $request->file('logo')->store('tenant-logos', 'public');
        $tenant->update(['logo' => $path]);

        return back()->with('success', 'Company logo updated successfully.');
    }
}
