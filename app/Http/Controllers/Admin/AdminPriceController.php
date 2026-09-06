<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePriceRowRequest;
use App\Http\Requests\StorePriceSectionRequest;
use App\Models\PriceRow;
use App\Models\PriceSection;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion complète de la grille tarifaire publique : titre général,
 * sections, lignes, ordre et activation. Aucune valeur de prix n'est codée
 * dans les vues — tout provient d'ici.
 */
class AdminPriceController extends Controller
{
    public function index(): View
    {
        $sections = PriceSection::query()->ordered()->with('rows')->get();

        $settings = [
            'pricing_enabled' => (bool) Setting::get('pricing_enabled', true),
            'pricing_title' => Setting::get('pricing_title', 'Nos tarifs'),
            'pricing_intro' => Setting::get('pricing_intro'),
        ];

        return view('admin.pricing.index', compact('sections', 'settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pricing_title' => ['required', 'string', 'max:150'],
            'pricing_intro' => ['nullable', 'string', 'max:400'],
            'pricing_enabled' => ['sometimes', 'boolean'],
        ], [], [
            'pricing_title' => 'titre de la grille',
            'pricing_intro' => 'texte d’introduction',
        ]);

        Setting::set('pricing_title', $validated['pricing_title'], 'string');
        Setting::set('pricing_intro', $validated['pricing_intro'] ?? null, 'string');
        Setting::set('pricing_enabled', $request->boolean('pricing_enabled'), 'boolean');

        $this->forgetPublicCache();

        return back()->with('success', 'La grille tarifaire a été mise à jour.');
    }

    public function storeSection(StorePriceSectionRequest $request): RedirectResponse
    {
        PriceSection::create([
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->forgetPublicCache();

        return back()->with('success', 'La section a été ajoutée.');
    }

    public function updateSection(StorePriceSectionRequest $request, PriceSection $section): RedirectResponse
    {
        $section->update([
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->forgetPublicCache();

        return back()->with('success', 'La section a été mise à jour.');
    }

    public function toggleSection(PriceSection $section): RedirectResponse
    {
        $section->update(['is_active' => ! $section->is_active]);
        $this->forgetPublicCache();

        return back()->with('success', $section->is_active ? 'Section réaffichée.' : 'Section masquée.');
    }

    public function destroySection(PriceSection $section): RedirectResponse
    {
        $section->delete(); // cascade sur les lignes (FK cascadeOnDelete)
        $this->forgetPublicCache();

        return back()->with('success', 'La section a été supprimée.');
    }

    public function storeRow(StorePriceRowRequest $request, PriceSection $section): RedirectResponse
    {
        $section->rows()->create([
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->forgetPublicCache();

        return back()->with('success', 'La ligne a été ajoutée.');
    }

    public function updateRow(StorePriceRowRequest $request, PriceRow $row): RedirectResponse
    {
        $row->update([
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->forgetPublicCache();

        return back()->with('success', 'La ligne a été mise à jour.');
    }

    public function toggleRow(PriceRow $row): RedirectResponse
    {
        $row->update(['is_active' => ! $row->is_active]);
        $this->forgetPublicCache();

        return back()->with('success', $row->is_active ? 'Ligne réaffichée.' : 'Ligne masquée.');
    }

    public function destroyRow(PriceRow $row): RedirectResponse
    {
        $row->delete();
        $this->forgetPublicCache();

        return back()->with('success', 'La ligne a été supprimée.');
    }

    /**
     * La grille est lue via Setting::getCached() côté public — on purge après
     * chaque écriture pour ne jamais servir un ancien titre/intro.
     */
    private function forgetPublicCache(): void
    {
        foreach (['pricing_enabled', 'pricing_title', 'pricing_intro'] as $key) {
            Setting::forgetCached($key);
        }
    }
}
