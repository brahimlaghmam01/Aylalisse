<?php

namespace App\Enums;

/**
 * Statuts possibles d'un rendez-vous.
 *
 * pending / confirmed bloquent le créneau.
 * cancelled libère le créneau.
 * completed / no_show concernent des rendez-vous passés et ne gênent
 * plus aucune disponibilité future.
 */
enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    /**
     * Statuts qui occupent un créneau et doivent être pris en compte
     * par le moteur de disponibilité.
     *
     * @return array<int, self>
     */
    public static function blocking(): array
    {
        return [self::Pending, self::Confirmed];
    }

    public function blocksSlot(): bool
    {
        return in_array($this, self::blocking(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Confirmed => 'Confirmé',
            self::Completed => 'Terminé',
            self::Cancelled => 'Annulé',
            self::NoShow => 'Absente',
        };
    }

    /**
     * Classes Tailwind (fond + texte) pour le badge de statut admin.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-800 border-amber-200',
            self::Confirmed => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            self::Completed => 'bg-sand text-cocoa border-nude/60',
            self::Cancelled => 'bg-red-50 text-red-700 border-red-200',
            self::NoShow => 'bg-ink/5 text-ink/50 border-ink/10',
        };
    }

    /**
     * Statuts que ce statut peut légitimement devenir, depuis
     * l'administration. Empêche par exemple qu'un rendez-vous annulé
     * "revienne" automatiquement à confirmé ou terminé.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Completed, self::Cancelled, self::NoShow],
            self::Completed, self::Cancelled, self::NoShow => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
