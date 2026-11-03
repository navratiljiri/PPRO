# Záznam změn (Changelog)

Všechny významné změny v tomto projektu jsou zaznamenávány v tomto souboru.

Formát vychází ze standardu [Keep a Changelog](https://keepachangelog.com/cs/1.0.0/)
a projekt dodržuje [Sémantické verzování](https://semver.org/lang/cs/).

---

## [Unreleased]
### Změněno (Changed)
- **Lokalizace do češtiny:** Výchozí jazyk aplikace přepnut na `cs` (`config/app.php`, `.env.example`, `docker-compose.yml`), faker na `cs_CZ`. Vestavěné texty Filament PHP (tlačítka, přihlášení, filtry, stránkování, notifikace) se tak zobrazují česky.
- Přeloženy všechny detailní pohledy (infolisty) administrace – kurzy, termíny, lektoři, lekce, studenti, přihlášky, docházka a osvědčení. Místo interních ID se zobrazují názvy kurzů a jména studentů a lektorů; stavy se zobrazují jako české štítky.

### Přidáno (Added)
- České jazykové soubory `lang/cs/` (`validation.php` včetně českých názvů atributů, `auth.php`, `passwords.php`, `pagination.php`).
- Test `FilamentLocalizationTest` ověřující české locale, český přihlašovací formulář a české popisky všech detailních stránek administrace (celkem 37 testů, 167 assercí).

---

## [0.3.0] - 2026-11-03
### Přidáno (Added)
- **Kompletní doménový a datový model (všech 8 entit ze Zadání C):**
  - Entita `Student` (`students`): evidence studentů (jméno, příjmení, unikátní e-mail, telefon) a vazba na přihlášky.
  - Entita `Registration` (`registrations`): přihlášky studentů na termíny, stavový automat (`registered`, `waiting_list`, `offered`, `cancelled`, `completed`), FIFO pořadí náhradníků (`waiting_order`), časový zámek nabídky (`offer_expires_at`) a auditní důvod storna (`cancellation_reason`).
  - Entita `AttendanceRecord` (`attendance_records`): detailní evidence přítomnosti studentů na jednotlivých lekcích termínu včetně poznámek lektora.
  - Entita `Certificate` (`certificates`): vydaná osvědčení o absolvování s garantovaným unikátním formátem čísla `AT-YYYY-XXXXX`, datem vydání a formálním schválením lektora. Zajištěna striktní neměnnost a zákaz smazání na úrovni modelu.
  - Verzované migrace pro všech 8 entit a vazeb (`2026_10_08_100000_create_students_table.php`, `2026_10_08_100100_create_registrations_table.php`, `2026_10_08_100200_create_attendance_records_table.php`, `2026_10_08_100300_create_certificates_table.php`).
- **Aplikační a byznys vrstva (Services) vynucující obchodní pravidla:**
  - `RegistrationService`:
    - Striktní nepřekročitelnost kapacity termínu ani při souběhu požadavků (uzamykání řádků transakcemi).
    - Automatické zařazení do FIFO pořadníku při vyčerpání kapacity se sekvenčním číslováním.
    - Zákaz souběžných aktivních přihlášek na stejný kurz v různých termínech (Rozhodnutí 3).
    - Systémový zákaz přihlášení po zahájení termínu s administrativní výjimkou omezenou na maximálně 20 % výuky (Rozhodnutí 4).
    - Auditovatelnost: Zrušené přihlášky se fyzicky nemažou z databáze; zaznamenává se důvod storna.
    - Poloautomatický posun z pořadníku (Rozhodnutí 2): při uvolnění místa systém automaticky nabídne místo prvnímu náhradníkovi s 24hodinovým časovým zámkem.
  - `AttendanceService`:
    - Zápis a hromadná aktualizace docházky pro konkrétní lekci.
    - Výpočet procentuální docházky studenta a kontrola nutné podmínky 70% účasti (Rozhodnutí 1).
  - `CertificateService`:
    - Dvoustupňový model certifikace: ověření 70% docházky (nebo schválené administrátorské výjimky) + pedagogické potvrzení lektora.
    - Automatické generování unikátního sekvenčního čísla osvědčení v daném roce.
    - Změna stavu přihlášky na `completed` a trvalé uzamčení certifikátu proti změnám.
  - `StatisticService`:
    - Přehled naplněnosti jednotlivých termínů (v procentech i počtech).
    - Souhrn odučených hodin za jednotlivé externí lektory ve zvoleném období.
    - Detekce a výpis studentů zapsaných v daném roce na více než jeden kurz (Požadavek 8).
- **Kompletní administrace ve Filament PHP (`/admin`):**
  - Implementován dedikovaný administrační panel s vizuální identitou Akademie Trutnov (Emerald barvy, české popisky a navigace).
  - 8 plnohodnotných Resource modulů:
    - `CourseResource`: Správa nabídky kurzů, akreditace MŠMT/MPSV, cen a stavu.
    - `CourseTermResource`: Správa termínů, kapacity, učeben, vizualizace obsazenosti a pořadníku náhradníků.
    - `LecturerResource`: Evidence externích lektorů a statistiky odučených hodin.
    - `LessonResource`: Rozvrhování výukových bloků se střídáním lektorů u vícedenních kurzů (Rozhodnutí 5).
    - `StudentResource`: Evidence účastníků a historie jejich přihlášek.
    - `RegistrationResource`: Správa přihlášek, auditní zrušení přihlášky s odůvodněním, přijetí/odmítnutí nabídky z pořadníku, vystavení osvědčení, zákaz fyzického mazání.
    - `AttendanceRecordResource`: Evidence docházky a omluvenek po lekcích.
    - `CertificateResource`: Matrika vydaných osvědčení v read-only režimu (garance neměnnosti).
  - Dashboard widgety: `AkademieStatsOverview` (karty metrik) a `TermOccupancyTableWidget` (přehled naplněnosti termínů).
  - Navigační propojení mezi veřejným katalogem a administrací.
- **Syntetická data a administrátorský účet (`DatabaseSeeder`):**
  - Vytvoření administrátorského účtu: `admin@akademietrutnov.cz` (heslo: `admin123`).
  - Vzorové kurzy, externí lektoři, termíny s malou i velkou kapacitou, lekce se střídáním lektorů, studenti, aktivní registrace, FIFO náhradníci v pořadníku i dokončený kurz s vydaným osvědčením.
- **Automatizované testy:**
  - Nové testovací sady: `RegistrationBusinessRulesTest`, `CertificateAndAttendanceTest`, `StatisticServiceTest`, `FilamentAdminTest`.
  - Celkem 34 automatizovaných testů (114 assercí), všechny úspěšně procházejí (`php artisan test`).

---

## [0.2.0] - 2026-10-06
### Přidáno (Added)
- **Kostra Laravel 11/12 aplikace:** Zprovoznění čistého PHP 8.4 backendu bez Livewire.
- **Frontend & Design:** Čisté Blade šablony a Tailwind CSS v4 (typografie Plus Jakarta Sans, responsivní karty, filtry, badge).
- **Čistá MVC architektura se servisní vrstvou pro entitu `Kurz`:**
  - *Prezentační vrstva:* `CourseController` (využívá Route Model Binding a Dependency Injection), `StoreCourseRequest`, `UpdateCourseRequest`, Blade komponenty (`index`, `show`, `create`, `edit`).
  - *Aplikační vrstva:* `CourseService` zapouzdřující byznys invarianty (unikátnost kódu, nezáporná cena, $\ge 1$ hodina, filtry a výpočet statistik).
  - *Datová vrstva:* Eloquent model `Course` s přímými Query Scopes (`active`, `accredited`, `search`) a migrace `courses`. Bez nadbytečných repozitářů a rozhraní.
- **Automatizované testy:** 8 Feature testů v `CourseManagementTest.php` a `ExampleTest.php` (24 assercí, všechny procházejí).
- **Syntetická data:** `CourseSeeder` se vzorovými rekvalifikačními kurzy (účetnictví, programování, management).
- **Kontejnerizace & Docker:** `Dockerfile` (PHP 8.4 Apache) a `docker-compose.yml` (aplikace + MySQL 8.0 databáze).

---

## [0.1.0] - 2026-09-30
### Přidáno (Added)
- Inicializace git repozitáře a propojení na vzdálený repozitář `git@github.com:navratiljiri/PPRO.git`.
- [AGENTS.md](AGENTS.md) – Provozní pravidla pro AI asistenta (přísná pravidla commitů s `-m`, zákaz `git push` bez explicitního povelu, dokumentace jako priorita).
- [README.md](README.md) – Centrální technická dokumentace, rozbor Zadání C (Akademie Trutnov), plnění povinného minima PPRO.
- Oddíl **Rozhodnutí** v `README.md` obsahující závazná řešení k 5 otevřeným bodům klienta (70% docházka vs lektor, čekací listina, zákaz souběhu termínů, výjimky po zahájení, střídání lektorů a evidence hodin).
- Návrh ERD doménového modelu s 8 entitami a M:N vazbami.
- [.gitignore](.gitignore) chránící před commitem `.env` souborů, build artefaktů a systémového smetí.
- Striktní Git pre-commit hook ([.githooks/pre-commit](.githooks/pre-commit)) blokující commity při opomenutí aktualizace dokumentace či commitu citlivých dat.
