<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        $stats = [
            'today' => Appointment::query()->onDate($today)->count(),
            'pending' => Appointment::query()->where('status', AppointmentStatus::Pending)->count(),
            'confirmed' => Appointment::query()->where('status', AppointmentStatus::Confirmed)->count(),
            'clients' => Client::query()->count(),
        ];

        $todaysAppointments = Appointment::query()
            ->onDate($today)
            ->with(['client', 'lissageService'])
            ->orderBy('start_time')
            ->get();

        $pendingRequests = Appointment::query()
            ->where('status', AppointmentStatus::Pending)
            ->with(['client', 'lissageService'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'todaysAppointments', 'pendingRequests'));
    }
}
