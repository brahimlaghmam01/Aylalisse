<?php

namespace Tests\Feature;

use App\Models\BeforeAfterResult;
use App\Models\LissageService;
use App\Models\PriceSection;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * La page d'accueil publique doit refléter le contenu administrable :
 * résultats avant/après, grille tarifaire, bandeau supérieur, image Hero.
 */
class HomePageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_still_loads_without_any_dynamic_content(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_the_services_section_reflects_active_services(): void
    {
        LissageService::factory()->create(['name' => 'Lissage indien / brésilien', 'price' => 0, 'price_courts' => 80, 'price_mi_longs' => 90, 'price_longs' => 100, 'is_active' => true]);
        LissageService::factory()->create(['name' => 'Prestation masquée', 'price' => 200, 'is_active' => false]);

        $html = $this->get('/')
            ->assertOk()
            ->assertSee('Lissage indien / brésilien')
            ->assertSee('À partir de 80')
            ->assertDontSee('Prestation masquée')
            ->getContent();

        // Section "prestations" pilotée par la base : le repli codé en dur
        // (« Un protocole pour chaque chevelure. » suivi des 4 démos) laisse
        // place aux prestations réelles.
        $this->assertStringNotContainsString('Le plus demandé', $html);
    }

    public function test_the_length_pricing_grid_shows_per_length_amounts_on_the_booking_page(): void
    {
        LissageService::factory()->create([
            'name' => 'Lissage indien', 'price' => 0,
            'price_courts' => 80, 'price_mi_longs' => 90, 'price_longs' => 100,
            'is_active' => true,
        ]);

        $this->get('/reservation')->assertOk()->assertSee('has_length_pricing', false);
    }

    public function test_the_services_section_falls_back_when_no_active_service_exists(): void
    {
        // Aucune prestation en base : le contenu de démonstration reste affiché.
        $this->get('/')->assertOk()->assertSee('Lissage Signature Soyeux');
    }

    public function test_published_before_after_results_appear_on_the_public_page(): void
    {
        Storage::fake('public');

        $result = BeforeAfterResult::create([
            'title' => 'Transformation Naïma',
            'before_image' => UploadedFile::fake()->image('before.jpg')->store('before-after', 'public'),
            'after_image' => UploadedFile::fake()->image('after.jpg')->store('before-after', 'public'),
            'hair_type' => 'Ondulés épais',
            'lissage_type' => 'Lissage indien',
            'is_published' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Transformation Naïma')
            ->assertSee(Storage::disk('public')->url($result->before_image), false)
            ->assertSee(Storage::disk('public')->url($result->after_image), false);
    }

    public function test_unpublished_results_are_not_shown(): void
    {
        Storage::fake('public');

        BeforeAfterResult::create([
            'title' => 'Résultat brouillon',
            'before_image' => UploadedFile::fake()->image('b.jpg')->store('before-after', 'public'),
            'after_image' => UploadedFile::fake()->image('a.jpg')->store('before-after', 'public'),
            'is_published' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('Résultat brouillon');
    }

    public function test_a_result_with_a_missing_image_file_never_breaks_the_page(): void
    {
        Storage::fake('public');

        BeforeAfterResult::create([
            'title' => 'Résultat sans fichier',
            'before_image' => 'before-after/deleted-before.jpg',
            'after_image' => 'before-after/deleted-after.jpg',
            'is_published' => true,
        ]);

        // La page répond 200 et n'affiche simplement pas ce résultat cassé.
        $this->get('/')->assertOk()->assertDontSee('Résultat sans fichier');
    }

    public function test_results_respect_the_admin_defined_order(): void
    {
        Storage::fake('public');

        $second = BeforeAfterResult::create([
            'title' => 'Deuxième affichée',
            'before_image' => UploadedFile::fake()->image('b1.jpg')->store('before-after', 'public'),
            'after_image' => UploadedFile::fake()->image('a1.jpg')->store('before-after', 'public'),
            'is_published' => true,
            'sort_order' => 2,
        ]);
        $first = BeforeAfterResult::create([
            'title' => 'Première affichée',
            'before_image' => UploadedFile::fake()->image('b2.jpg')->store('before-after', 'public'),
            'after_image' => UploadedFile::fake()->image('a2.jpg')->store('before-after', 'public'),
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Deuxième affichée'),
            strpos($html, 'Première affichée'),
            'Le résultat sort_order=1 doit précéder le sort_order=2 dans le HTML.'
        );
    }

    public function test_the_pricing_grid_reflects_the_database(): void
    {
        $section = PriceSection::create(['title' => 'Lissage indien / brésilien', 'sort_order' => 1, 'is_active' => true]);
        $section->rows()->createMany([
            ['label' => 'Cheveux courts', 'price' => 80, 'sort_order' => 1, 'is_active' => true],
            ['label' => 'Cheveux mi-longs', 'price' => 90, 'sort_order' => 2, 'is_active' => true],
            ['label' => 'Ligne masquée', 'price' => 999, 'sort_order' => 3, 'is_active' => false],
        ]);

        Setting::set('pricing_enabled', true, 'boolean');
        Setting::set('pricing_title', 'Nos tarifs lissage', 'string');

        $this->get('/')
            ->assertOk()
            ->assertSee('Nos tarifs lissage')
            ->assertSee('Lissage indien / brésilien')
            ->assertSee('Cheveux courts')
            ->assertSee('80 €')
            ->assertDontSee('Ligne masquée');
    }

    public function test_disabling_the_pricing_grid_hides_it(): void
    {
        $section = PriceSection::create(['title' => 'Grille cachée', 'sort_order' => 1, 'is_active' => true]);
        $section->rows()->create(['label' => 'Cheveux courts', 'price' => 80, 'is_active' => true]);

        Setting::set('pricing_enabled', false, 'boolean');

        $this->get('/')->assertOk()->assertDontSee('Grille cachée');
    }

    public function test_a_price_row_without_a_price_shows_its_free_text_note(): void
    {
        $section = PriceSection::create(['title' => 'Prestations sur mesure', 'sort_order' => 1, 'is_active' => true]);
        $section->rows()->create(['label' => 'Cheveux très longs', 'price' => null, 'note' => 'sur devis', 'is_active' => true]);

        Setting::set('pricing_enabled', true, 'boolean');

        $this->get('/')->assertOk()->assertSee('sur devis');
    }

    public function test_the_top_banner_text_comes_from_settings(): void
    {
        Setting::set('top_banner_enabled', true, 'boolean');
        Setting::set('top_banner_text', 'Offre découverte — 10% sur le premier lissage', 'string');

        $this->get('/')->assertOk()->assertSee('Offre découverte — 10% sur le premier lissage');
    }

    public function test_disabling_the_top_banner_removes_it(): void
    {
        Setting::set('top_banner_enabled', false, 'boolean');
        Setting::set('top_banner_text', 'Bandeau désactivé à ne pas afficher', 'string');

        $this->get('/')->assertOk()->assertDontSee('Bandeau désactivé à ne pas afficher');
    }

    public function test_the_hero_image_setting_is_rendered_when_the_file_exists(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('hero.jpg')->store('hero', 'public');
        Setting::set('hero_image', $path, 'string');

        $this->get('/')
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($path), false);
    }

    public function test_a_missing_hero_file_falls_back_without_breaking(): void
    {
        Storage::fake('public');
        Setting::set('hero_image', 'hero/deleted.jpg', 'string');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('hero/deleted.jpg', false);
    }
}
