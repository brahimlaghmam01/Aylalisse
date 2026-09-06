<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBannerRequest;
use App\Http\Requests\UpdateHeroRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * « Apparence du site » : bandeau supérieur et image de la section Hero de
 * la page d'accueil. Ces deux blocs sont pilotés par des clés Setting et
 * lus côté public via Setting::getCached() — le cache est purgé ici après
 * chaque écriture.
 */
class AdminSiteContentController extends Controller
{
    private const DISK = 'public';

    private const HERO_DIRECTORY = 'hero';

    private const CACHED_KEYS = [
        'top_banner_enabled', 'top_banner_text', 'top_banner_subtext', 'hero_image',
    ];

    public function index(): View
    {
        $settings = [
            'top_banner_enabled' => (bool) Setting::get('top_banner_enabled', true),
            'top_banner_text' => Setting::get('top_banner_text'),
            'top_banner_subtext' => Setting::get('top_banner_subtext'),
            'hero_image' => Setting::get('hero_image'),
        ];

        $heroImageUrl = filled($settings['hero_image']) && Storage::disk(self::DISK)->exists($settings['hero_image'])
            ? Storage::disk(self::DISK)->url($settings['hero_image'])
            : null;

        return view('admin.appearance.index', compact('settings', 'heroImageUrl'));
    }

    public function updateBanner(UpdateBannerRequest $request): RedirectResponse
    {
        Setting::set('top_banner_enabled', $request->boolean('top_banner_enabled'), 'boolean');
        Setting::set('top_banner_text', $request->validated('top_banner_text'), 'string');
        Setting::set('top_banner_subtext', $request->validated('top_banner_subtext') ?? null, 'string');

        $this->forgetCache();

        return back()->with('success', 'Le bandeau supérieur a été mis à jour.');
    }

    public function updateHero(UpdateHeroRequest $request): RedirectResponse
    {
        $previous = Setting::get('hero_image');

        $path = $request->file('hero_image')->store(self::HERO_DIRECTORY, self::DISK);
        Setting::set('hero_image', $path, 'string');

        // L'ancien fichier n'est supprimé qu'une fois le nouveau enregistré.
        if ($previous && $previous !== $path) {
            Storage::disk(self::DISK)->delete($previous);
        }

        $this->forgetCache();

        return back()->with('success', 'L’image de la section Hero a été remplacée.');
    }

    public function destroyHero(): RedirectResponse
    {
        $previous = Setting::get('hero_image');

        if ($previous) {
            Storage::disk(self::DISK)->delete($previous);
        }

        Setting::set('hero_image', null, 'string');
        $this->forgetCache();

        return back()->with('success', 'L’image de la section Hero a été retirée. Le visuel par défaut est réaffiché.');
    }

    private function forgetCache(): void
    {
        foreach (self::CACHED_KEYS as $key) {
            Setting::forgetCached($key);
        }
    }
}
