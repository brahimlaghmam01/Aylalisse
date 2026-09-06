<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Statistiques financières de l'activité AylaLisse sur une période donnée.
 *
 * Règles (voir la spécification "Revenus & chiffre d'affaires") :
 *  - Le chiffre d'affaires réalisé ne compte QUE les rendez-vous "completed".
 *  - Il utilise toujours le prix HISTORIQUE figé sur le rendez-vous
 *    (colonne appointments.price), jamais le prix actuel de la prestation.
 *  - "Encaissé" = uniquement les montants réellement marqués payés
 *    (deposit_paid_at / balance_paid_at). Un rendez-vous "completed" au
 *    solde non coché n'est PAS considéré comme encaissé.
 *  - La période filtre sur appointment_date (la date de la prestation).
 */
class RevenueReport
{
    public readonly Carbon $from;

    public readonly Carbon $to;

    public function __construct(
        public readonly string $periodKey = 'month',
        ?Carbon $from = null,
        ?Carbon $to = null,
    ) {
        [$this->from, $this->to] = $this->resolvePeriod($periodKey, $from, $to);
    }

    /**
     * Périodes proposées dans le sélecteur du tableau de bord.
     *
     * @return array<string, string>
     */
    public static function periodOptions(): array
    {
        return [
            'today' => "Aujourd'hui",
            'week' => 'Cette semaine',
            'month' => 'Ce mois',
            'quarter' => 'Ce trimestre',
            'year' => 'Cette année',
            'all' => 'Depuis le début',
            'custom' => 'Personnalisé',
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(string $key, ?Carbon $from, ?Carbon $to): array
    {
        $today = Carbon::today();

        return match ($key) {
            'today' => [$today->copy(), $today->copy()->endOfDay()],
            'week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'quarter' => [$today->copy()->firstOfQuarter(), $today->copy()->lastOfQuarter()->endOfDay()],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'all' => [Carbon::createFromTimestamp(0), $today->copy()->addYears(5)->endOfYear()],
            'custom' => [
                ($from ?? $today->copy()->startOfMonth())->copy()->startOfDay(),
                ($to ?? $today->copy())->copy()->endOfDay(),
            ],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };
    }

    /**
     * Rendez-vous de la période, hors annulés (les annulés ne comptent dans
     * aucune statistique financière).
     *
     * @return Collection<int, Appointment>
     */
    private function appointments(): Collection
    {
        return once(fn () => Appointment::query()
            ->betweenDates($this->from, $this->to)
            ->where('status', '!=', AppointmentStatus::Cancelled->value)
            ->with('lissageService:id,name')
            ->get());
    }

    /**
     * Chiffre d'affaires réalisé : somme des prix historiques des
     * rendez-vous "completed" de la période.
     */
    public function revenue(): float
    {
        return $this->round(
            $this->appointments()
                ->where('status', AppointmentStatus::Completed)
                ->sum(fn (Appointment $a) => (float) $a->price)
        );
    }

    public function completedCount(): int
    {
        return $this->appointments()->where('status', AppointmentStatus::Completed)->count();
    }

    /**
     * Montant réellement encaissé (acomptes + soldes cochés payés) sur la période.
     */
    public function collected(): float
    {
        return $this->round(
            $this->appointments()->sum(fn (Appointment $a) => $a->amountCollected())
        );
    }

    /**
     * Reste à encaisser : sur les rendez-vous "completed" de la période,
     * ce qui n'a pas encore été marqué payé.
     */
    public function outstanding(): float
    {
        return $this->round(
            $this->appointments()
                ->where('status', AppointmentStatus::Completed)
                ->sum(fn (Appointment $a) => $a->amountOutstanding())
        );
    }

    /**
     * Total des acomptes enregistrés (potentiel), quel que soit l'état du
     * paiement — hors annulés.
     */
    public function deposits(): float
    {
        return $this->round(
            $this->appointments()->sum(fn (Appointment $a) => (float) $a->deposit_amount)
        );
    }

    /**
     * Revenus réalisés ventilés par prestation (prix historiques).
     *
     * @return array<int, array{name: string, count: int, revenue: float}>
     */
    public function byService(): array
    {
        return $this->appointments()
            ->where('status', AppointmentStatus::Completed)
            ->groupBy(fn (Appointment $a) => $a->lissageService?->name ?? 'Prestation supprimée')
            ->map(fn (Collection $group, string $name) => [
                'name' => $name,
                'count' => $group->count(),
                'revenue' => $this->round($group->sum(fn (Appointment $a) => (float) $a->price)),
            ])
            ->sortByDesc('revenue')
            ->values()
            ->all();
    }

    /**
     * Série mensuelle du chiffre d'affaires réalisé sur les 12 derniers
     * mois (indépendante de la période sélectionnée — vision de tendance).
     *
     * @return array<int, array{label: string, month: string, revenue: float}>
     */
    public function monthlySeries(int $months = 12): array
    {
        $start = Carbon::today()->startOfMonth()->subMonths($months - 1);

        $rows = Appointment::query()
            ->where('status', AppointmentStatus::Completed->value)
            ->whereDate('appointment_date', '>=', $start->toDateString())
            ->get(['appointment_date', 'price'])
            ->groupBy(fn (Appointment $a) => $a->appointment_date->format('Y-m'))
            ->map(fn (Collection $g) => $this->round($g->sum(fn (Appointment $a) => (float) $a->price)));

        $series = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $series[] = [
                'label' => ucfirst($month->translatedFormat('M')),
                'month' => $key,
                'revenue' => (float) ($rows[$key] ?? 0.0),
            ];
        }

        return $series;
    }

    public function periodLabel(): string
    {
        if ($this->periodKey === 'all') {
            return 'Depuis le début';
        }

        if ($this->from->isSameDay($this->to)) {
            return ucfirst($this->from->translatedFormat('d F Y'));
        }

        return ucfirst($this->from->translatedFormat('d M Y')).' — '.$this->to->translatedFormat('d M Y');
    }

    private function round(float $value): float
    {
        return round($value, 2);
    }
}
