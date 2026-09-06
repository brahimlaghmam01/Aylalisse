<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLissageServiceRequest;
use App\Http\Requests\UpdateLissageServiceRequest;
use App\Models\LissageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminLissageServiceController extends Controller
{
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
        LissageService::create($request->validated());

        return redirect()->route('admin.services.index')->with('success', 'La prestation a été enregistrée.');
    }

    public function edit(LissageService $lissageService): View
    {
        return view('admin.services.edit', ['service' => $lissageService]);
    }

    public function update(UpdateLissageServiceRequest $request, LissageService $lissageService): RedirectResponse
    {
        $lissageService->update($request->validated());

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
