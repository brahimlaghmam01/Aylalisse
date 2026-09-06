<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Page d'accueil éditoriale AylaLisse.
     *
     * Phase 1 : contenu de démonstration codé en dur pour valider le
     * système visuel. En Phase 2, ces données proviendront de la base
     * (prestations de lissage, résultats avant/après, témoignages).
     */
    public function index()
    {
        $experiences = [
            [
                'nom' => 'Lissage Signature Soyeux',
                'description' => 'Idéal pour cheveux ondulés à bouclés avec frisottis récalcitrants. Souplesse aérienne et hydratation profonde.',
                'duree' => '3h30',
                'prix' => 240,
                'featured' => false,
            ],
            [
                'nom' => 'Lissage Premium Miroir',
                'description' => 'Brillance intense et raideur liquide pour cheveux épais, denses ou rebelles. Effet zéro frisottis sous l’humidité.',
                'duree' => '4h00',
                'prix' => 310,
                'featured' => true,
            ],
            [
                'nom' => 'Lissage Intense & Réparation',
                'description' => 'Cheveux très texturés, fragilisés par des colorations ou décolorations. Reconstruction profonde de la fibre.',
                'duree' => '4h30',
                'prix' => 360,
                'featured' => false,
            ],
            [
                'nom' => 'Lissage Sur-Mesure Diagnostic',
                'description' => 'Cas complexes, cheveux décolorés à blanc ou extensions. Étude microscopique et protocole ajusté au millimètre.',
                'duree' => 'sur devis',
                'prix' => null,
                'featured' => false,
            ],
        ];

        $methode = [
            ['num' => '01', 'titre' => 'Analyse de vos cheveux', 'texte' => 'Compréhension de la nature capillaire, de la porosité et des besoins réels avant toute intervention.'],
            ['num' => '02', 'titre' => 'Diagnostic personnalisé', 'texte' => 'Lecture de la texture, des traitements chimiques passés et définition de la formule adaptée.'],
            ['num' => '03', 'titre' => 'Le lissage', 'texte' => 'Application maîtrisée, temps de pose calibré et scellage thermique précis pour un résultat durable.'],
            ['num' => '04', 'titre' => 'Résultat & conseils', 'texte' => 'Rituel d’entretien personnalisé pour prolonger la brillance et la douceur pendant des mois.'],
        ];

        $atouts = [
            ['titre' => 'Une expertise spécialisée', 'texte' => 'Le lissage est notre unique vocation. Une maîtrise développée sur une seule discipline.'],
            ['titre' => 'Approche sur-mesure', 'texte' => 'Chaque chevelure reçoit une formule et un protocole ajustés à ses caractéristiques.'],
            ['titre' => 'Résultats visibles', 'texte' => 'Une transformation nette dès la première séance : brillance, souplesse, facilité de coiffage.'],
            ['titre' => 'Expérience premium', 'texte' => 'Un accompagnement discret et attentif, du diagnostic à l’entretien à domicile.'],
        ];

        $temoignages = [
            ['nom' => 'Camille R.', 'note' => 5, 'texte' => 'Mes cheveux bouclés étaient impossibles à discipliner. Six mois après, ils restent lisses, brillants et faciles à coiffer.', 'prestation' => 'Lissage Signature Soyeux'],
            ['nom' => 'Sarah M.', 'note' => 5, 'texte' => 'Le diagnostic a tout changé. On m’a proposé exactement ce dont mes cheveux décolorés avaient besoin.', 'prestation' => 'Lissage Intense & Réparation'],
            ['nom' => 'Éléonore L.', 'note' => 5, 'texte' => 'Un accueil d’exception et un résultat miroir spectaculaire. Je n’avais jamais vu mes cheveux aussi sains.', 'prestation' => 'Lissage Premium Miroir'],
        ];

        $faq = [
            ['q' => 'Quel type de lissage est adapté à mes cheveux ?', 'r' => 'Cela dépend de votre texture naturelle, de votre historique chimique et de vos attentes. Le diagnostic capillaire, offert le jour du rendez-vous, permet de définir la formule idéale.'],
            ['q' => 'Combien de temps dure une séance ?', 'r' => 'Entre 3h30 et 4h30 selon la longueur, la densité et l’état de vos cheveux. Chaque créneau est réservé à une seule cliente.'],
            ['q' => 'Combien de temps dure le résultat ?', 'r' => 'En moyenne 4 à 6 mois. La tenue dépend de la nature des cheveux et du respect du rituel d’entretien recommandé.'],
            ['q' => 'Puis-je faire un lissage sur des cheveux bouclés ?', 'r' => 'Oui. Nos protocoles sont conçus pour les cheveux ondulés, bouclés, très frisés et crépus, avec un dosage adapté à chaque texture.'],
            ['q' => 'Comment entretenir mes cheveux après le lissage ?', 'r' => 'Avec des soins sans sulfates ni sel, un espacement des shampooings et une protection thermique. Un protocole détaillé vous est remis en fin de séance.'],
            ['q' => 'Dois-je faire un diagnostic avant le rendez-vous ?', 'r' => 'Le diagnostic est réalisé sur place, au début de la séance. Vous pouvez toutefois nous transmettre des photos en amont via WhatsApp.'],
            ['q' => 'Puis-je réserver directement en ligne ?', 'r' => 'Oui. Le module de réservation vous permet de choisir votre prestation, votre date et votre créneau. Un acompte confirme le rendez-vous.'],
        ];

        return view('home', compact('experiences', 'methode', 'atouts', 'temoignages', 'faq'));
    }
}
