<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Longueur des cheveux — formulaire simplifié (courts/mi-longs/longs).
     * Les anciens slugs (epaules/mi-dos/bas-du-dos) restent affichables
     * pour l'historique (voir les label maps admin) mais ne sont plus
     * proposés ni acceptés en écriture : le nouveau formulaire ne les
     * soumet jamais.
     */
    public const HAIR_LENGTHS = ['courts', 'mi-longs', 'longs'];

    public const NATURAL_TEXTURES = ['ondules', 'boucles', 'tres-frises-crepus'];

    /**
     * "Couleur des cheveux" (formulaire simplifié) — stockée dans la même
     * colonne JSON "chemical_history" que l'ancien historique chimique
     * multi-sélection, mais avec un seul élément désormais :
     *   Naturelle              -> []                       (tableau vide)
     *   Colorés                -> ['coloration']
     *   Méchés / Balayage      -> ['decoloration-balayage']
     *   Décolorés              -> ['decoloration']
     *   Autre (à préciser)     -> ['autre']
     * 'precedent-lissage' est conservé côté affichage (historique) mais
     * n'est plus proposé par le formulaire simplifié.
     */
    public const CHEMICAL_HISTORY_OPTIONS = ['coloration', 'decoloration-balayage', 'decoloration', 'autre'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lissage_service_id' => ['required', 'integer', Rule::exists('lissage_services', 'id')->where('is_active', true)],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],

            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],

            'hair_length' => ['nullable', 'string', Rule::in(self::HAIR_LENGTHS)],
            'natural_texture' => ['nullable', 'string', Rule::in(self::NATURAL_TEXTURES)],
            'chemical_history' => ['nullable', 'array'],
            'chemical_history.*' => [Rule::in(self::CHEMICAL_HISTORY_OPTIONS)],
            'hair_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'lissage_service_id' => 'prestation',
            'appointment_date' => 'date',
            'start_time' => 'créneau horaire',
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'phone' => 'téléphone',
            'email' => 'adresse e-mail',
            'hair_length' => 'longueur des cheveux',
            'natural_texture' => 'texture naturelle',
            'chemical_history' => 'couleur des cheveux',
            'hair_notes' => 'précisions complémentaires',
        ];
    }

    public function messages(): array
    {
        return [
            'lissage_service_id.exists' => "La prestation sélectionnée n'est pas disponible.",
            'appointment_date.after_or_equal' => 'La date choisie doit être aujourd’hui ou une date future.',
        ];
    }
}
