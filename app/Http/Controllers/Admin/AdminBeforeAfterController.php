<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeforeAfterRequest;
use App\Http\Requests\UpdateBeforeAfterRequest;
use App\Models\BeforeAfterResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminBeforeAfterController extends Controller
{
    private const DISK = 'public';

    private const DIRECTORY = 'before-after';

    public function index(): View
    {
        $results = BeforeAfterResult::query()->ordered()->paginate(15);

        return view('admin.results.index', compact('results'));
    }

    public function create(): View
    {
        return view('admin.results.create');
    }

    public function store(StoreBeforeAfterRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['before_image', 'after_image']);
        $data['before_image'] = $request->file('before_image')->store(self::DIRECTORY, self::DISK);
        $data['after_image'] = $request->file('after_image')->store(self::DIRECTORY, self::DISK);
        $data['is_published'] = $request->boolean('is_published', true);

        BeforeAfterResult::create($data);

        return redirect()->route('admin.results.index')->with('success', 'Le résultat a été publié.');
    }

    public function edit(BeforeAfterResult $result): View
    {
        return view('admin.results.edit', compact('result'));
    }

    public function update(UpdateBeforeAfterRequest $request, BeforeAfterResult $result): RedirectResponse
    {
        $data = $request->safe()->except(['before_image', 'after_image']);
        $data['is_published'] = $request->boolean('is_published', true);

        $oldBefore = $result->before_image;
        $oldAfter = $result->after_image;

        if ($request->hasFile('before_image')) {
            $data['before_image'] = $request->file('before_image')->store(self::DIRECTORY, self::DISK);
        }
        if ($request->hasFile('after_image')) {
            $data['after_image'] = $request->file('after_image')->store(self::DIRECTORY, self::DISK);
        }

        $result->update($data);

        // On ne supprime l'ancien fichier qu'une fois le remplacement confirmé en base.
        if (isset($data['before_image'])) {
            Storage::disk(self::DISK)->delete($oldBefore);
        }
        if (isset($data['after_image'])) {
            Storage::disk(self::DISK)->delete($oldAfter);
        }

        return redirect()->route('admin.results.index')->with('success', 'Le résultat a été mis à jour.');
    }

    public function togglePublished(BeforeAfterResult $result): RedirectResponse
    {
        $result->update(['is_published' => ! $result->is_published]);

        return back()->with('success', $result->is_published ? 'Le résultat est maintenant publié.' : 'Le résultat a été masqué.');
    }

    public function destroy(BeforeAfterResult $result): RedirectResponse
    {
        Storage::disk(self::DISK)->delete([$result->before_image, $result->after_image]);
        $result->delete();

        return back()->with('success', 'Le résultat a été supprimé.');
    }
}
