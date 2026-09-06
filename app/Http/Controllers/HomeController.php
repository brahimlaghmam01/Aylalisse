<?php

namespace App\Http\Controllers;

use App\Models\BeforeAfterResult;
use App\Models\LissageService;
use App\Models\PriceSection;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Page d'accueil éditoriale AylaLisse.
     *
     * Le contenu dynamique (résultats avant/après, témoignages, grille
     * tarifaire, image Hero, bandeau) provient de la base et de la table
     * "settings". Les tableaux ci-dessous ne servent plus que de repli
     * quand aucune donnée n'a encore été saisie côté admin — le design
     * premium reste identique dans les deux cas.
     */
    public function index()
    {
        // Repli utilisé uniquement si aucune prestation active n'existe en base.
        $experiencesFallback = [
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

        $temoignagesFallback = [
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

        // ---- Contenu dynamique ----

        // Prestations réellement proposées : la présentation publique doit
        // toujours refléter ce que la cliente peut réserver (mêmes
        // prestations que le formulaire de réservation).
        try {
            $services = LissageService::query()->active()->ordered()->get();
        } catch (QueryException) {
            $services = collect();
        }

        // Résultats avant/après publiés — on écarte silencieusement ceux dont
        // un fichier image est manquant pour ne jamais afficher d'image cassée.
        //
        // Tout ce bloc est tolérant aux tables absentes : sur un déploiement
        // fraîchement mis en ligne mais pas encore migré, la page d'accueil
        // doit s'afficher (avec ses contenus de repli) plutôt que de renvoyer
        // une erreur 500 — même philosophie que Setting::get().
        try {
            $results = BeforeAfterResult::query()
                ->published()
                ->ordered()
                ->get()
                ->filter->has_both_images
                ->values();
        } catch (QueryException) {
            $results = collect();
        }

        try {
            $temoignages = Testimonial::query()
                ->published()
                ->ordered()
                ->get();
        } catch (QueryException) {
            $temoignages = collect();
        }

        // Grille tarifaire : sections actives ayant au moins une ligne active.
        try {
            $priceSections = Setting::get('pricing_enabled', true)
                ? PriceSection::query()
                    ->active()
                    ->ordered()
                    ->with('activeRows')
                    ->get()
                    ->filter(fn (PriceSection $section) => $section->activeRows->isNotEmpty())
                    ->values()
                : collect();
        } catch (QueryException) {
            $priceSections = collect();
        }

        $pricing = [
            'title' => Setting::getCached('pricing_title', 'Nos tarifs'),
            'intro' => Setting::getCached('pricing_intro'),
        ];

        $heroImage = $this->publicImageUrl(Setting::getCached('hero_image'));

        return view('home', compact(
            'services', 'experiencesFallback', 'methode', 'atouts', 'faq',
            'results', 'temoignages', 'temoignagesFallback',
            'priceSections', 'pricing', 'heroImage',
        ));
    }

    /**
     * URL publique d'un fichier stocké sur le disque "public", ou null si le
     * chemin est vide ou si le fichier n'existe plus (jamais d'image cassée).
     */
    private function publicImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->exists($path) ? $disk->url($path) : null;
    }
}
