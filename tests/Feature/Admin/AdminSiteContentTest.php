<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminSiteContentTest extends AdminFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_the_appearance_page_requires_authentication(): void
    {
        $this->get('/admin/apparence')->assertRedirect(route('admin.login'));
    }

    public function test_the_appearance_page_renders_both_blocks(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.site-content.index'))
            ->assertOk()
            ->assertSee('Bandeau supérieur')
            ->assertSee('Image de la section Hero');
    }

    public function test_the_banner_can_be_edited(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.site-content.banner.update'), [
                'top_banner_enabled' => '1',
                'top_banner_text' => 'Diagnostic capillaire offert — été 2026',
                'top_banner_subtext' => 'Sur rendez-vous uniquement',
            ])
            ->assertRedirect();

        $this->assertTrue((bool) Setting::get('top_banner_enabled'));
        $this->assertSame('Diagnostic capillaire offert — été 2026', Setting::get('top_banner_text'));
        $this->assertSame('Sur rendez-vous uniquement', Setting::get('top_banner_subtext'));

        // Le texte accentué doit ressortir intact sur le site public.
        $this->get('/')->assertOk()->assertSee('Diagnostic capillaire offert — été 2026');
    }

    public function test_disabling_the_banner_is_persisted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.site-content.banner.update'), [
                'top_banner_text' => 'Texte conservé même désactivé',
            ])
            ->assertRedirect();

        $this->assertFalse((bool) Setting::get('top_banner_enabled'));
        $this->assertSame('Texte conservé même désactivé', Setting::get('top_banner_text'));
    }

    public function test_the_banner_text_is_required(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.site-content.banner.update'), ['top_banner_enabled' => '1', 'top_banner_text' => ''])
            ->assertSessionHasErrors('top_banner_text');
    }

    public function test_a_hero_image_can_be_uploaded(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.site-content.hero.update'), [
                'hero_image' => UploadedFile::fake()->image('hero.jpg', 1200, 1500),
            ])
            ->assertRedirect();

        $path = Setting::get('hero_image');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_the_hero_image_deletes_the_previous_file(): void
    {
        $admin = $this->admin();

        $old = UploadedFile::fake()->image('old-hero.jpg')->store('hero', 'public');
        Setting::set('hero_image', $old, 'string');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.site-content.hero.update'), [
                'hero_image' => UploadedFile::fake()->image('new-hero.jpg'),
            ])
            ->assertRedirect();

        $new = Setting::get('hero_image');
        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);
    }

    public function test_removing_the_hero_image_clears_the_setting_and_file(): void
    {
        $admin = $this->admin();

        $path = UploadedFile::fake()->image('hero.jpg')->store('hero', 'public');
        Setting::set('hero_image', $path, 'string');

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.site-content.hero.destroy'))
            ->assertRedirect();

        $this->assertNull(Setting::get('hero_image'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_an_svg_hero_image_is_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.site-content.hero.update'), [
                'hero_image' => UploadedFile::fake()->create('hero.svg', 20, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors('hero_image');
    }
}
