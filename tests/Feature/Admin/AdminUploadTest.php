<?php

namespace Tests\Feature\Admin;

use App\Models\BeforeAfterResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminUploadTest extends AdminFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_an_invalid_file_is_rejected(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.results.store'), [
            'title' => 'Transformation test',
            'before_image' => UploadedFile::fake()->create('malware.php', 10, 'application/x-php'),
            'after_image' => UploadedFile::fake()->image('after.jpg'),
        ]);

        $response->assertSessionHasErrors('before_image');
        $this->assertDatabaseCount('before_after_results', 0);
    }

    public function test_an_svg_file_is_rejected(): void
    {
        // Les SVG peuvent embarquer du JavaScript exécutable — jamais
        // acceptés, seuls jpg/jpeg/png/webp le sont.
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.testimonials.store'), [
            'client_name' => 'Test',
            'content' => 'Un témoignage.',
            'rating' => 5,
            'image' => UploadedFile::fake()->create('avatar.svg', 5, 'image/svg+xml'),
        ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_a_valid_image_is_accepted_and_stored(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.results.store'), [
            'title' => 'Transformation test',
            'before_image' => UploadedFile::fake()->image('before.jpg'),
            'after_image' => UploadedFile::fake()->image('after.jpg'),
            'hair_type' => 'Ondulés',
            'lissage_type' => 'Lissage Signature Soyeux',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.results.index'));
        $this->assertDatabaseCount('before_after_results', 1);

        $result = BeforeAfterResult::first();
        Storage::disk('public')->assertExists($result->before_image);
        Storage::disk('public')->assertExists($result->after_image);
    }

    public function test_replacing_an_image_removes_the_previous_file(): void
    {
        $admin = $this->admin();

        $result = BeforeAfterResult::create([
            'title' => 'Transformation test',
            'before_image' => UploadedFile::fake()->image('before.jpg')->store('before-after', 'public'),
            'after_image' => UploadedFile::fake()->image('after.jpg')->store('before-after', 'public'),
            'is_published' => true,
        ]);

        $oldBeforeImage = $result->before_image;
        Storage::disk('public')->assertExists($oldBeforeImage);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.results.update', $result), [
            'title' => $result->title,
            'before_image' => UploadedFile::fake()->image('new-before.jpg'),
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.results.index'));

        $result->refresh();
        Storage::disk('public')->assertMissing($oldBeforeImage);
        Storage::disk('public')->assertExists($result->before_image);
        $this->assertNotSame($oldBeforeImage, $result->before_image);
    }
}
