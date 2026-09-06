<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLissageServiceRequest;
use App\Http\Requests\UpdateLissageServiceRequest;
use App\Models\LissageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminLissageServiceController extends Controller
{
    private const DISK = 'public';

    private const DIRECTORY = 'services';

    public function index(): View
    {
        $services = LissageService::query()->ordered()->get();

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(StoreLissageServiceRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store(self::DIRECTORY, self::DISK);
        }

        LissageService::create($data);

        return redirect()->route('admin.services.index')->with('success', 'La prestation a été enregistrée.');
    }

    public function edit(LissageService $lissageService): View
    {
        return view('admin.services.edit', ['service' => $lissageService]);
    }

    public function update(UpdateLissageServiceRequest $request, LissageService $lissageService): RedirectResponse
    {
        $data = $request->safe()->except('image');

        $oldImage = $lissageService->image;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store(self::DIRECTORY, self::DISK);
        }

        $lissageService->update($data);

        // L'ancien fichier n'est supprimé qu'une fois le remplacement confirmé en base.
        if (isset($data['image']) && $oldImage) {
            Storage::disk(self::DISK)->delete($oldImage);
        }

        return redirect()->route('admin.services.index')->with('success', 'La prestation a été mise à jour.');
    }

    /**
     * Active/désactive une prestation. Une prestation désactivée disparaît
     * immédiatement du booking public (scope "active") mais reste
     * intacte dans l'historique des rendez-vous déjà pris.
     */
    public function toggleActive(LissageService $lissageService): RedirectResponse
    {
        $lissageService->update(['is_active' => ! $lissageService->is_active]);

        return back()->with('success', $lissageService->is_active
            ? 'La prestation a été réactivée.'
            : 'La prestation a été désactivée.');
    }
}
