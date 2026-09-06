<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function mentions()
    {
        return view('legal.page', [
            'titre' => 'Mentions légales',
            'contenu' => 'Le contenu détaillé des mentions légales sera précisé ultérieurement. AylaLisse — spécialiste du lissage des cheveux, Paris.',
        ]);
    }

    public function privacy()
    {
        return view('legal.page', [
            'titre' => 'Politique de confidentialité',
            'contenu' => 'Les informations collectées lors de la réservation (coordonnées et diagnostic capillaire) sont utilisées uniquement pour la gestion de votre rendez-vous et ne sont jamais cédées à des tiers.',
        ]);
    }

    public function terms()
    {
        return view('legal.page', [
            'titre' => 'Conditions de réservation',
            'contenu' => 'Un acompte est demandé pour confirmer chaque rendez-vous. Toute annulation doit être signalée au moins 48 heures à l’avance.',
        ]);
    }
}
