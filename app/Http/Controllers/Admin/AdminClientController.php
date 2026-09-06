<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminClientController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();

        $clients = Client::query()
            ->withCount('appointments')
            ->withMax('appointments', 'appointment_date')
            ->when($q, function ($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.clients.index', compact('clients', 'q'));
    }

    public function show(Client $client): View
    {
        $client->load(['appointments' => function ($query) {
            $query->with('lissageService')->orderByDesc('appointment_date')->orderByDesc('start_time');
        }]);

        return view('admin.clients.show', compact('client'));
    }
}
