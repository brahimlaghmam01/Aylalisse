<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
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

        $completed = $client->appointments->where('status', AppointmentStatus::Completed);

        $financials = [
            'total_appointments' => $client->appointments->count(),
            'completed_count' => $completed->count(),
            // Valeur des prestations réellement terminées — prix historiques.
            'completed_value' => round($completed->sum(fn (Appointment $a) => (float) $a->price), 2),
            // Réellement encaissé (acomptes + soldes cochés payés), tous statuts hors annulés.
            'collected' => round(
                $client->appointments
                    ->where('status', '!=', AppointmentStatus::Cancelled)
                    ->sum(fn (Appointment $a) => $a->amountCollected()),
                2
            ),
            // Restant à encaisser sur les prestations terminées.
            'outstanding' => round($completed->sum(fn (Appointment $a) => $a->amountOutstanding()), 2),
        ];

        return view('admin.clients.show', compact('client', 'financials'));
    }
}
