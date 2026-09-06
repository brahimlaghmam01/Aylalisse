# Déploiement AylaLisse

Guide de mise en production. À suivre dans l'ordre lors du premier déploiement ;
les sections « Cache » et « Queue » sont aussi à relire après chaque mise à jour.

## 1. Prérequis serveur

| Composant | Version / configuration |
|---|---|
| PHP | 8.2 ou supérieur |
| Extensions PHP | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (uploads avant/après, témoignages) |
| MySQL / MariaDB | 8.0+ / 10.4+ |
| Composer | 2.x (build uniquement — pas nécessaire à l'exécution si `vendor/` est déployé) |
| Node.js / npm | Requis uniquement pour `npm run build` (build des assets) ; jamais nécessaire en exécution |
| Serveur web | Apache ou Nginx pointant sur `public/` |
| Worker de file d'attente | Voir « Queue » ci-dessous — un vrai worker permanent (`queue:work`) OU un cron régulier selon l'hébergement |
| Tâche planifiée (cron) | Aucune tâche planifiée n'est requise par l'application elle-même (`routes/console.php` ne définit aucun `schedule()`). Un cron est en revanche nécessaire uniquement pour faire tourner la file d'attente si l'hébergement ne permet pas de worker permanent — voir « Hostinger » |

## 2. Installation

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
cp .env.example .env          # puis renseigner toutes les valeurs (section 3)
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan db:seed --class=Database\\Seeders\\LissageServiceSeeder --force   # si base neuve
php artisan db:seed --class=Database\\Seeders\\BusinessHourSeeder --force     # si base neuve
php artisan db:seed --class=Database\\Seeders\\SettingSeeder --force          # si base neuve
```

Le compte administrateur (`AdminSeeder`) lit `ADMIN_EMAIL` / `ADMIN_PASSWORD`
depuis `.env` — **définir un vrai mot de passe fort avant de lancer ce seeder
en production**, puis exécuter `php artisan db:seed --class=Database\\Seeders\\AdminSeeder --force`.

`npm install` / `npm run build` ne sont nécessaires **que sur la machine qui
construit les assets** (poste de build ou étape CI) — le serveur de
production n'a besoin que du résultat, `public/build/`, et n'exécute jamais
Node.js.

## 3. Variables d'environnement (.env)

Ne jamais committer de vrais secrets. Ce tableau documente les clés
nécessaires — les valeurs ci-dessous sont des exemples, pas des vrais
identifiants.

### Application

| Variable | Production |
|---|---|
| `APP_NAME` | `AylaLisse` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` — **jamais `true` en production** (fuiterait stack traces, requêtes SQL, chemins serveur) |
| `APP_URL` | `https://votre-domaine.fr` (avec le vrai schéma HTTPS) |
| `APP_KEY` | Générée par `php artisan key:generate`, jamais copiée d'un autre environnement |

### Base de données

| Variable | Exemple |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | nom de la base fournie par l'hébergeur |
| `DB_USERNAME` / `DB_PASSWORD` | fournis par l'hébergeur, jamais codés en dur |

### Courrier électronique (SMTP)

| Variable | Rôle |
|---|---|
| `MAIL_MAILER` | `smtp` en production (`log` en local, écrit dans `storage/logs/laravel.log` au lieu d'envoyer réellement) |
| `MAIL_HOST` | Hôte SMTP du fournisseur (ex. Hostinger, SendGrid, Mailgun...) |
| `MAIL_PORT` | `587` (STARTTLS) ou `465` (TLS implicite) selon le fournisseur |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | Identifiants SMTP réels — jamais dans le code, uniquement dans `.env` |
| `MAIL_SCHEME` | `smtp` (STARTTLS, port 587) ou `smtps` (TLS implicite, port 465). **Remplace l'ancienne variable `MAIL_ENCRYPTION`** dans ce projet (Laravel 12 utilise Symfony Mailer, configuré par schéma d'URL plutôt que par un champ `encryption` séparé) |
| `MAIL_FROM_ADDRESS` | Adresse d'expédition (ex. `contact@aylalisse.fr`) |
| `MAIL_FROM_NAME` | `AylaLisse` |

### Adresse admin (réception des nouvelles demandes)

L'adresse qui reçoit l'e-mail "Nouvelle demande de rendez-vous" **n'est pas
codée en dur** : c'est le paramètre `brand_email`, modifiable depuis
`/admin/parametres` (section « Informations générales »), avec `MAIL_FROM_ADDRESS`
comme valeur de repli tant qu'aucune valeur n'a été enregistrée en base.

### Compte administrateur de démonstration

| Variable | Rôle |
|---|---|
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Lues par `AdminSeeder` uniquement — changer impérativement avant tout seed en production |

### File d'attente

| Variable | Production |
|---|---|
| `QUEUE_CONNECTION` | `database` (déjà la valeur par défaut du projet — aucune infrastructure supplémentaire type Redis n'est requise) |

### Session / cookies

| Variable | Production |
|---|---|
| `SESSION_DRIVER` | `database` (déjà la valeur par défaut) |
| `SESSION_SECURE_COOKIE` | `true` — le site étant servi en HTTPS, empêche l'envoi du cookie de session en clair |
| `SESSION_DOMAIN` | Domaine réel si nécessaire (sous-domaines), sinon laisser vide |

## 4. Cache (à exécuter après chaque déploiement)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Après toute modification de `.env`, de `routes/*.php` ou des vues, relancer
`config:cache` / `route:cache` / `view:cache` — sinon Laravel continue de
servir l'ancienne configuration/les anciennes routes mises en cache.

Pour vider ces caches (dépannage) : `php artisan optimize:clear`.

**Important — ce qui n'est jamais mis en cache** : `AppointmentAvailabilityService`
et `Setting::get()` (utilisé pour `booking_interval`, `minimum_booking_notice_hours`,
`maximum_booking_days`, `default_buffer_minutes`) lisent toujours la base de
données en temps réel, sans aucun cache applicatif — modifier un horaire, une
date bloquée ou un réglage de réservation depuis `/admin` a un effet
immédiat sur `/reservation`. Seuls les réglages d'affichage pur (téléphone,
adresse, réseaux sociaux — utilisés par le pied de page, les données
structurées et le bouton WhatsApp) sont mis en cache via `Setting::getCached()`,
et ce cache est explicitement invalidé à chaque enregistrement depuis
`/admin/parametres`.

## 5. File d'attente (notifications e-mail)

Les e-mails (nouvelle demande, confirmation, annulation, reprogrammation)
sont envoyés via des `Notification` Laravel mises en file d'attente
(`ShouldQueue`, connexion `database`) : la réservation ou l'action admin
réussit immédiatement, l'envoi réel se fait ensuite, dans un processus
séparé. **Sans worker actif, les e-mails s'accumulent dans la table `jobs`
sans jamais partir.**

Worker permanent (VPS, serveur dédié) :

```bash
php artisan queue:work --tries=3 --max-time=3600
```

À superviser avec un gestionnaire de process (Supervisor, systemd) pour
qu'il redémarre automatiquement en cas d'arrêt. Après un déploiement de code,
exécuter `php artisan queue:restart` pour que les workers rechargent le
nouveau code.

Jobs échoués :

```bash
php artisan queue:failed          # lister
php artisan queue:retry <id>      # rejouer un job précis
php artisan queue:retry all       # tout rejouer
php artisan queue:flush           # vider l'historique des échecs
```

## 6. Tâche planifiée (cron)

Aucune tâche planifiée n'est requise par l'application elle-même. Un cron
n'est nécessaire **que** comme alternative à un worker permanent — voir
section Hostinger ci-dessous.

## 7. Hébergement mutualisé Hostinger

Un hébergement mutualisé type Hostinger ne permet généralement pas de faire
tourner un processus permanent (`queue:work` en démon). Cette section
documente une configuration réaliste pour ce contexte — **ne pas supposer
qu'un worker permanent fonctionne s'il n'est pas explicitement supporté par
l'offre souscrite.**

- **Racine du document / dossier public** : pointer le "document root" du
  domaine sur le dossier `public/` de l'application (jamais sur la racine du
  projet — `app/`, `.env`, `storage/` ne doivent pas être accessibles depuis
  le web).
- **Permissions** : `storage/` et `bootstrap/cache/` doivent être accessibles
  en écriture par l'utilisateur du serveur web (généralement `755` sur les
  dossiers, propriétaire = utilisateur PHP-FPM de l'hébergement).
- **`.env`** : uploadé manuellement en dehors du contrôle de version, jamais
  dans le dossier public.
- **Migrations** : exécutées via le terminal SSH si disponible
  (`php artisan migrate --force`), sinon via un script one-shot protégé
  puis supprimé immédiatement après exécution.
- **Storage link** : Hostinger **ne crée pas automatiquement** le lien
  symbolique `public/storage` → `storage/app/public`. Exécuter
  `php artisan storage:link` manuellement (SSH) ou, si les liens symboliques
  ne sont pas supportés par l'offre, copier physiquement le contenu de
  `storage/app/public` vers `public/storage` après chaque déploiement
  contenant de nouveaux fichiers.
- **Cache** : exécuter les trois commandes de la section 4 après chaque
  déploiement.
- **Queue sans worker permanent** : configurer une tâche cron (via le panneau
  Hostinger, toutes les minutes) exécutant :

  ```bash
  php /chemin/vers/le/projet/artisan queue:work --stop-when-empty --max-time=50
  ```

  `--stop-when-empty` termine le processus dès que la file est vide (au lieu
  de tourner indéfiniment, ce qu'un cron mutualisé ne supporte pas) ;
  `--max-time=50` est une sécurité pour ne jamais dépasser la fenêtre d'une
  minute entre deux exécutions du cron. Alternative plus simple si le
  panneau ne permet qu'une commande par ligne : `php artisan queue:work --once`.

## 8. Stratégie de sauvegarde

Aucun système de sauvegarde automatisé complexe n'est installé par ce
projet — la stratégie ci-dessous est à mettre en œuvre manuellement ou via
les outils fournis par l'hébergeur.

| Élément | Quoi | Fréquence recommandée | Méthode |
|---|---|---|---|
| Base de données | Toutes les tables (rendez-vous, clientes, réglages...) | Quotidienne | `mysqldump` planifié (cron) ou outil de sauvegarde du panneau d'hébergement (hPanel Hostinger propose des sauvegardes automatiques sur certaines offres) |
| Fichiers uploadés | `storage/app/public/` (images avant/après, témoignages) | Quotidienne ou hebdomadaire selon le rythme de publication | Archive (`tar`/`zip`) téléchargée hors serveur, ou synchronisation vers un stockage externe |
| Code applicatif | Le dépôt Git est la sauvegarde du code — s'assurer qu'il est poussé vers un remote (GitHub/GitLab) avant tout déploiement | À chaque déploiement | Git |

Procédure de restauration (résumé) :

1. Restaurer le dump SQL le plus récent : `mysql -u <user> -p <database> < backup.sql`.
2. Restaurer `storage/app/public/` depuis l'archive correspondante, puis
   vérifier que `public/storage` pointe toujours dessus (`php artisan storage:link`
   si le lien a été perdu).
3. Redéployer le code depuis le dépôt Git à la révision voulue.
4. Relancer les commandes de cache (section 4).

Conserver au moins 7 jours de sauvegardes quotidiennes glissantes ; pour une
activité commerciale (données de rendez-vous), une rétention de 30 jours est
recommandée si l'espace de stockage le permet.

## 9. Sécurité — points vérifiés dans le code

- **En-têtes de sécurité** (`app/Http/Middleware/SecurityHeaders.php`, appliqué
  globalement) : `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
  `Permissions-Policy`, `Content-Security-Policy`. La CSP autorise
  `'unsafe-eval'`/`'unsafe-inline'` sur `script-src` car Alpine.js (utilisé
  sur tout le site) compile ses expressions via `new Function()` et plusieurs
  vues admin utilisent encore des attributs `onsubmit=""` pour les
  confirmations de suppression — verrouiller davantage nécessiterait de
  migrer vers le build CSP d'Alpine et des nonces, chantier séparé pour ne
  pas risquer de casser le site/l'admin déjà validés.
- **HTTPS** : `URL::forceScheme('https')` appliqué automatiquement quand
  `APP_ENV=production` (voir `AppServiceProvider::boot()`).
- **Rate limiting** : `booking-availability` (60/min/IP), `booking-submit`
  (6/min/IP), `admin-login` (10/min/IP + throttling par e-mail dans
  `AdminLoginRequest`), plafond général `180/min` sur toute l'administration
  authentifiée.
- **Uploads** : MIME strictement limité à `jpg,jpeg,png,webp` (jamais `svg`,
  jamais de type exécutable), taille max 4 Mo, validation `image` Laravel
  (rejette un fichier renommé qui n'est pas réellement une image), noms de
  fichiers générés aléatoirement par Laravel (jamais le nom original conservé).
- **Erreurs** : pages personnalisées `404`/`419`/`429`/`500`
  (`resources/views/errors/`), autonomes (sans dépendance à la base de
  données), n'affichent jamais de détail technique.
- **Logs** : aucune donnée sensible (mot de passe, jeton, identifiants SMTP)
  n'est journalisée par l'application ; `SlotUnavailableException` est
  exclue des rapports d'erreur (`bootstrap/app.php`) car c'est un
  comportement métier attendu, pas une anomalie.

## 10. Checklist finale avant mise en production

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` en HTTPS
- [ ] `.env` de production renseigné (DB, SMTP, `ADMIN_EMAIL`/`ADMIN_PASSWORD` changés)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm run build` exécuté, `public/build/` déployé
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link` (ou copie manuelle si non supporté)
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Worker de file d'attente actif (démon ou cron `queue:work --stop-when-empty`)
- [ ] Test d'envoi d'e-mail réel (SMTP de production) via une vraie réservation
- [ ] `/sitemap.xml` et `/robots.txt` accessibles et corrects
- [ ] Pages d'erreur testées (`/route-inexistante` → 404 personnalisée)
- [ ] En-têtes de sécurité vérifiés (`curl -I https://votre-domaine.fr`)
- [ ] Sauvegarde initiale de la base + du dossier `storage/app/public/` effectuée
