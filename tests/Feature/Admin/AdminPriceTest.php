<?php

namespace Tests\Feature\Admin;

use App\Models\PriceRow;
use App\Models\PriceSection;
use App\Models\Setting;

class AdminPriceTest extends AdminFeatureTestCase
{
    public function test_the_pricing_page_requires_authentication(): void
    {
        $this->get('/admin/tarifs')->assertRedirect(route('admin.login'));
    }

    public function test_the_pricing_page_renders_sections_and_rows(): void
    {
        $section = PriceSection::create(['title' => 'Lissage indien / brésilien', 'is_active' => true]);
        $section->rows()->create(['label' => 'Cheveux courts', 'price' => 80, 'is_active' => true]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.pricing.index'))
            ->assertOk()
            ->assertSee('Grille tarifaire')
            ->assertSee('Lissage indien / brésilien')
            ->assertSee('Cheveux courts');
    }

    public function test_an_admin_can_create_a_section_and_rows(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.pricing.sections.store'), ['title' => 'Lissage indien / brésilien', 'sort_order' => 1])
            ->assertRedirect();

        $section = PriceSection::firstOrFail();
        $this->assertSame('Lissage indien / brésilien', $section->title);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.pricing.rows.store', $section), [
                'label' => 'Cheveux courts',
                'price' => '80',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('price_rows', [
            'price_section_id' => $section->id,
            'label' => 'Cheveux courts',
            'price' => '80.00',
        ]);
    }

    public function test_a_french_formatted_price_is_accepted(): void
    {
        $admin = $this->admin();
        $section = PriceSection::create(['title' => 'Suppléments', 'is_active' => true]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.pricing.rows.store', $section), ['label' => 'Frais de déplacement', 'price' => '15,50'])
            ->assertRedirect();

        $this->assertSame('15.50', PriceRow::firstOrFail()->price);
    }

    public function test_a_row_can_be_updated_and_toggled(): void
    {
        $admin = $this->admin();
        $section = PriceSection::create(['title' => 'Lissage', 'is_active' => true]);
        $row = $section->rows()->create(['label' => 'Cheveux courts', 'price' => 80, 'is_active' => true]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.pricing.rows.update', $row), ['label' => 'Cheveux courts', 'price' => '85'])
            ->assertRedirect();
        $this->assertSame('85.00', $row->fresh()->price);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.pricing.rows.toggle', $row))
            ->assertRedirect();
        $this->assertFalse($row->fresh()->is_active);
    }

    public function test_deleting_a_section_cascades_to_its_rows(): void
    {
        $admin = $this->admin();
        $section = PriceSection::create(['title' => 'À supprimer', 'is_active' => true]);
        $section->rows()->create(['label' => 'Ligne', 'price' => 10, 'is_active' => true]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.pricing.sections.destroy', $section))
            ->assertRedirect();

        $this->assertDatabaseCount('price_sections', 0);
        $this->assertDatabaseCount('price_rows', 0);
    }

    public function test_general_settings_can_be_edited(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.pricing.settings.update'), [
                'pricing_title' => 'Grille tarifaire 2026',
                'pricing_intro' => 'Des prix nets et transparents.',
                'pricing_enabled' => '1',
            ])
            ->assertRedirect();

        $this->assertSame('Grille tarifaire 2026', Setting::get('pricing_title'));
        $this->assertTrue((bool) Setting::get('pricing_enabled'));
    }

    public function test_a_row_label_is_required(): void
    {
        $admin = $this->admin();
        $section = PriceSection::create(['title' => 'Lissage', 'is_active' => true]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.pricing.rows.store', $section), ['label' => '', 'price' => '80'])
            ->assertSessionHasErrors('label');
    }
}
