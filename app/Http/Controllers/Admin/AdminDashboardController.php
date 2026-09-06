<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Services\RevenueReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', 'in:'.implode(',', array_keys(RevenueReport::periodOptions()))],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $period = $validated['period'] ?? 'month';
        $revenue = new RevenueReport(
            $period,
            isset($validated['from']) ? Carbon::parse($validated['from']) : null,
            isset($validated['to']) ? Carbon::parse($validated['to']) : null,
        );

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

        return view('admin.dashboard', [
            'stats' => $stats,
            'todaysAppointments' => $todaysAppointments,
            'pendingRequests' => $pendingRequests,
            'revenue' => $revenue,
            'period' => $period,
            'periodOptions' => RevenueReport::periodOptions(),
            'customFrom' => $validated['from'] ?? $today->copy()->startOfMonth()->toDateString(),
            'customTo' => $validated['to'] ?? $today->toDateString(),
        ]);
    }
}
