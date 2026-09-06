<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminTestimonialController extends Controller
{
    private const DISK = 'public';

    private const DIRECTORY = 'testimonials';

    public function index(): View
    {
        $testimonials = Testimonial::query()->ordered()->paginate(15);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('admin.testimonials.create');
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store(self::DIRECTORY, self::DISK);
        }

        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Le témoignage a été publié.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_published'] = $request->boolean('is_published', true);

        $oldImage = $testimonial->image;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store(self::DIRECTORY, self::DISK);
        }

        $testimonial->update($data);

        if (isset($data['image']) && $oldImage) {
            Storage::disk(self::DISK)->delete($oldImage);
        }

        return redirect()->route('admin.testimonials.index')->with('success', 'Le témoignage a été mis à jour.');
    }

    public function togglePublished(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_published' => ! $testimonial->is_published]);

        return back()->with('success', $testimonial->is_published ? 'Le témoignage est maintenant publié.' : 'Le témoignage a été masqué.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->image) {
            Storage::disk(self::DISK)->delete($testimonial->image);
        }

        $testimonial->delete();

        return back()->with('success', 'Le témoignage a été supprimé.');
    }
}
