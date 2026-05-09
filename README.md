# JB WP Beheer Plugin

Professioneel klantdashboard voor WordPress websites, beheerd door Joshua Bink.

- **Plugin name (zichtbaar):** JB WP Beheer Plugin
- **Plugin slug (folder + textdomain):** `jb-wp-beheer-plugin`
- **Hoofdbestand:** `jb-wp-beheer-plugin.php`
- **Huidige versie:** `4.0.2`
- **Update-bron:** GitHub Releases — [`joshuabink/jb-wp-beheer-plugin`](https://github.com/joshuabink/jb-wp-beheer-plugin)
- **Branch:** `main`

---

## Auto-update via GitHub Releases

Deze plugin wordt **niet** via wordpress.org gedistribueerd. In plaats daarvan
gebruiken we de [YahnisElsts plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker)
library, die meegeleverd wordt in `lib/plugin-update-checker/`. Zodra de plugin
op een testsite geïnstalleerd is, zal WordPress automatisch nieuwe versies
detecteren via GitHub Releases en de gebruikelijke update-knop tonen onder
**Plugins → Geïnstalleerde plugins**.

### Hoe weet de plugin uit welke repo updates komen?

Alles wordt geconfigureerd via constanten in `jb-wp-beheer-plugin.php`:

```php
define( 'JBWP_PLUGIN_VERSION', '4.0.0' );
define( 'JBWP_PLUGIN_SLUG',    'jb-wp-beheer-plugin' );
define( 'JBWP_PLUGIN_FILE',    __FILE__ );
define( 'JBWP_GITHUB_REPO',    'https://github.com/joshuabink/jb-wp-beheer-plugin/' );
define( 'JBWP_GITHUB_BRANCH',  'main' );
```

Het bootstrap-bestand `includes/updater.php` leest deze constanten en
initialiseert de update-checker. Deze code is volledig defensief: als de
library ontbreekt, of als de GitHub-API niet bereikbaar is, blijft de plugin
gewoon werken — er worden alleen geen updates getoond.

---

## Releaseproces

### 1. Bump de versie

Werk **drie** plekken bij in `jb-wp-beheer-plugin.php`:

1. De plugin header (`Version: 4.0.1`)
2. De constante (`define( 'JBWP_PLUGIN_VERSION', '4.0.1' );`)
3. Optioneel: het versiecommentaar bovenin `assets/css/admin.css` en
   `assets/js/admin.js`

> Volg [semantische versionering](https://semver.org/lang/nl/):
> `MAJOR.MINOR.PATCH` — bumps de PATCH bij bugfixes, MINOR bij nieuwe
> features die backwards-compatible zijn, en MAJOR bij breaking changes.

### 2. Commit en push naar `main`

```bash
git add -A
git commit -m "Release v4.0.1 — korte beschrijving"
git push origin main
```

### 3. Maak een GitHub Release aan

Maak een **tag** aan die exact overeenkomt met de versie in de plugin header,
voorafgegaan door `v`:

| Plugin header | Git tag  |
|---------------|----------|
| `4.0.1`       | `v4.0.1` |
| `4.1.0`       | `v4.1.0` |

Op GitHub:

1. Ga naar **Releases → Draft a new release**
2. **Choose a tag:** `v4.0.1` (Create new tag on publish, target = `main`)
3. **Release title:** `v4.0.1`
4. **Describe this release:** changelog
5. **Attach binaries:** upload `jb-wp-beheer-plugin-v4.0.1.zip` als asset
   (zie hieronder hoe je deze bouwt). De updater is al geconfigureerd om
   release-assets te prefereren boven het auto-gegenereerde source-archief.
6. Klik op **Publish release**

### 4. Bouw de distributie-ZIP

De ZIP moet één topfolder bevatten genaamd `jb-wp-beheer-plugin/` met alle
plugin-bestanden er direct in (dus géén extra wrapper-folder ertussen):

```bash
cd /pad/naar/parent
zip -r jb-wp-beheer-plugin-v4.0.1.zip jb-wp-beheer-plugin \
  -x "jb-wp-beheer-plugin/.git/*" \
  -x "jb-wp-beheer-plugin/.github/*" \
  -x "jb-wp-beheer-plugin/.DS_Store"
```

### 5. Klant / tester ontvangt update

Binnen ~12 uur (of direct na een handmatige check via **Dashboard → Updates →
Opnieuw controleren**) verschijnt de nieuwe versie als reguliere
plugin-update in WordPress. De gebruiker hoeft niets te doen behalve op
**Nu bijwerken** klikken.

---

## Mappen­structuur

```
jb-wp-beheer-plugin/
├── jb-wp-beheer-plugin.php       # Hoofdbestand met plugin header + identiteit-constanten
├── uninstall.php                 # Cleanup bij plugin verwijderen
├── README.md                     # Dit bestand
├── assets/
│   ├── css/admin.css             # Admin styling
│   └── js/admin.js               # Admin JavaScript
├── includes/
│   └── updater.php               # GitHub Releases auto-updater bootstrap
└── lib/
    └── plugin-update-checker/    # YahnisElsts library (vendored, niet aanpassen)
```

---

## Toekomstige uitbreidingen

De updater is bewust gebouwd met een filter-hook zodat we later kunnen
overstappen naar een private repo of een eigen update-server zonder de
hoofdcode aan te raken:

```php
add_filter( 'jbwp_update_checker', function ( $checker ) {
    $checker->setAuthentication( 'ghp_xxxx_personal_access_token' );
    return $checker;
} );
```

---

## Backwards-compatibility

De plugin draaide eerder onder de naam *De Webmaatjes Client Dashboard* met
de prefix `dwmcd`. Bij de hernoeming naar **JB WP Beheer Plugin (v4.0.0)**
zijn de volgende interne namen bewust ongewijzigd gelaten zodat bestaande
installaties hun instellingen, rollen en cache behouden:

- DB-optie: `dwmcd_settings`
- Capability: `manage_dewebmaatjes_dashboard`
- CSS/JS handles: `dwmcd-admin`

Deze worden in een toekomstige release stapsgewijs gemigreerd.

### ⚠️ Belangrijk bij upgraden van v3.x

Omdat de oude plugin (`dewebmaatjes-client-dashboard/`) en de nieuwe plugin
(`jb-wp-beheer-plugin/`) tijdelijk dezelfde `dwmcd_*` functienamen delen,
moet je **eerst de oude plugin volledig deactiveren én verwijderen** voordat
je de nieuwe activeert. Anders gooit PHP een
`Cannot redeclare function dwmcd_defaults()` fatal.

De nieuwe plugin detecteert deze situatie wel en weigert zichzelf te laden
met een duidelijke admin-notice in plaats van de site te crashen, maar de
schoonste workflow is:

1. **Plugins → Geïnstalleerde plugins**
2. *De Webmaatjes Client Dashboard* → **Deactiveren**
3. *De Webmaatjes Client Dashboard* → **Verwijderen** (instellingen blijven
   bewaard in de database)
4. **Plugin uploaden** → kies `jb-wp-beheer-plugin-v4.0.0.zip` → **Activeren**

Alle bestaande instellingen, beheerders en GA4-koppelingen blijven werken
omdat de DB-optie `dwmcd_settings` en de capability
`manage_dewebmaatjes_dashboard` ongewijzigd zijn.
