<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Pages publiques réellement existantes. La page d'accueil regroupe
     * les sections "le lissage / résultats / notre méthode / témoignages /
     * FAQ / contact" en ancres (#le-lissage, #resultats...) — ce ne sont
     * pas des URLs séparées, donc pas d'entrées de sitemap distinctes pour
     * elles. /admin* est volontairement exclu.
     */
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => route('booking'), 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => route('legal.mentions'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => route('legal.privacy'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => route('legal.terms'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        $xml = view('seo.sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /reservation/confirmation',
            '',
            'Sitemap: '.route('seo.sitemap'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
