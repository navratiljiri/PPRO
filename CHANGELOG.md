# Záznam změn (Changelog)

Všechny významné změny v tomto projektu jsou zaznamenávány v tomto souboru.

Formát vychází ze standardu [Keep a Changelog](https://keepachangelog.com/cs/1.0.0/)
a projekt dodržuje [Sémantické verzování](https://semver.org/lang/cs/).

---

## [Unreleased]

### Přidáno (Added)
- **Formulář pro vystavení osvědčení** (`/admin/certificates/create`, tlačítko *Vystavit osvědčení*): výběr způsobilé přihlášky (jen stav *Zaregistrováno* bez osvědčení), živý náhled docházky, schválení lektorem a administrátorská výjimka s povinným odůvodněním. Vystavení probíhá přes `CertificateService` – číslo (`AT-YYYY-XXXXX`) a datum vydání se generují automaticky, nikdy se nezadávají ručně; pravidlo 70% docházky zůstává vynuceno a chyby služby se zobrazují přímo u polí formuláře.
- **Seznam absolventů termínu** (`/admin/course-terms/{id}/graduates`, tlačítko *Absolventi* v detailu i v tabulce termínů): pro každého studenta docházka (počet lekcí a %), stav přihlášky, výsledek (*Úspěšný absolvent* / *Splňuje podmínky* / *Nesplňuje docházku*), číslo a datum osvědčení; filtr podle výsledku a akce *Vystavit osvědčení* přímo ze seznamu. Data poskytuje `CertificateService::getGraduationSummary()`.
- **Stránka Statistiky** (`/admin/statistics`, menu *Přehledy*) – požadavek klienta č. 8:
  - naplněnost termínů (počty i procenta, náhradníci v pořadníku),
  - odučené hodiny lektorů ve zvoleném období (filtr *Od–Do*, výchozí aktuální rok),
  - studenti přihlášení v daném roce na více než jeden kurz (filtr *Rok*).
  Widgety `LecturerTaughtHoursTable` a `MultiCourseStudentsTable` čerpají data ze `StatisticService`.
- Middleware `SetAdminLocale` – administrace Filament je vždy česky (i pro Livewire požadavky), nezávisle na `APP_LOCALE` v lokálním `.env`.
- Trait `App\Filament\Concerns\MapsServiceValidationErrors` – převádí chyby obchodních pravidel ze služeb na chyby konkrétních polí formuláře Filament.
- Accessor `Course::formatted_duration` a `Course::formatHours()` – hodinový rozsah se správným českým skloňováním (1 vyuč. hodina, 2–4 vyuč. hodiny, 5+ vyuč. hodin); použito ve veřejném katalogu i v administraci kurzů.
- **Demo data:** `CourseTermSeeder` (volaný z `DatabaseSeeder`) přidává 9 termínů ve všech stavech – 2 dokončené (absolventi s osvědčeními i student pod hranicí 70 %), 2 probíhající (částečně zapsaná docházka, střídání dvou lektorů), 4 v zápisu (z toho 1 plně obsazený s pořadníkem), 1 zrušený (přihlášky zachovány se stornem). Dále 2 noví lektoři a 18 syntetických studentů, část z nich na více kurzech. Seeder je idempotentní.
- České jazykové soubory `lang/cs/` (`validation.php` včetně českých názvů atributů, `auth.php`, `passwords.php`, `pagination.php`).
- Testy: `FilamentLocalizationTest`, `FilamentCertificateCreateTest`, `AdminIntegrityAndReportsTest` (kapacita, absolventi, statistiky). Celkem **55 testů, 289 assercí**.

### Změněno (Changed)
- **Veřejný web je pouze pro čtení (Rozhodnutí 6):** zůstaly jen přehledy a detaily kurzů, termínů a lektorů (`index`, `show`). Odstraněny routy, šablony `create`/`edit`, `LessonController`, všechny Form Requesty, tlačítka *Nový kurz*, *Upravit*, *Smazat* a formulář pro přidání lekce. Texty „Správa …“ přepsány na „Přehled …“, z patičky odstraněn text „Třívrstvá architektura (PPRO Minimum)“. Veškerá správa probíhá výhradně ve Filamentu.
- **Filament ukládá přes aplikační vrstvu:** vytváření a úpravy kurzů (`CourseService`), termínů (`CourseTermService`), lektorů (`LecturerService`) a vytváření lekcí (`CourseTermService::addLessonToTerm`) dříve zapisovaly přímo do modelů a obcházely obchodní pravidla (unikátní kód, normalizace e-mailu, kontrola dat apod.).
- **Přihlášky nelze editovat:** odstraněna stránka `EditRegistration` a tlačítko *Upravit*; stav se mění jen akcemi přes `RegistrationService` (zrušit, přijmout/odmítnout nabídku, vystavit osvědčení). Model `Registration` navíc blokuje změnu `course_term_id` / `student_id` – přesunem by šlo obejít kapacitu a Rozhodnutí 3.
- **Kapacita termínu:** `CourseTermService::updateTerm` (se zámkem řádku) nepovolí snížit kapacitu pod počet obsazených míst; po navýšení kapacity nabídne uvolněná místa náhradníkům v pořadí FIFO (Rozhodnutí 2). `CourseTermService` nově závisí na `RegistrationService`.
- **Lektor s lekcemi nelze smazat ani ve Filamentu:** mazání jde přes `LecturerService::deleteLecturer`, při porušení pravidla se zobrazí notifikace. Hromadné mazání lektorů odstraněno (kaskádou by smazalo jejich lekce i docházku).
- **Osvědčení:** detail je pouze pro čtení (odstraněna nefunkční stránka `EditCertificate` a tlačítka Upravit/Smazat), seznam má tlačítko *Vystavit osvědčení*.
- **Lokalizace do češtiny:** výchozí jazyk `cs` (`config/app.php`, `.env.example`, `docker-compose.yml`), faker `cs_CZ`; přeloženy všechny detailní pohledy administrace, místo interních ID se zobrazují názvy a jména, stavy jako české štítky.
- `Dockerfile`: doinstalováno PHP rozšíření `intl` (`libicu-dev`), které Filament vyžaduje – bez něj selhával `composer install` v kontejneru.
- Testy webového CRUD (`CourseManagementTest`, `CourseTermManagementTest`, `LecturerManagementTest`) převedeny na testy administrace Filament a doplněny o ověření, že veřejný web žádnou správu nenabízí (404/405).

### Opraveno (Fixed)
- `StatisticService::getLecturerTaughtHours()` – hodiny za zvolené období se ukládaly do atributu `total_taught_hours`, který přepisoval stejnojmenný accessor modelu `Lecturer` (součet za celou dobu), takže filtr období neměl žádný efekt. Výsledek je nově v `period_taught_hours`; test ověřuje i omezení obdobím.
- `/lecturers/{id}` s nečíselným parametrem (např. `/lecturers/create`) vracelo chybu 500 – parametr je omezen na čísla (`whereNumber`), nyní 404.
- Filament se při lokálním spuštění s `APP_LOCALE=en` zobrazoval anglicky.

### Odstraněno (Removed)
- `app/Http/Controllers/LessonController.php`, `app/Http/Requests/*` (7 Form Requestů), šablony `courses|terms|lecturers/create.blade.php` a `edit.blade.php`, stránky Filament `EditCertificate` a `EditRegistration`.

### Známá omezení (zatím neřešeno)
- `docker compose up` na čistém klonu nespustí funkční aplikaci: image nespouští `composer install`, nevytváří `.env` / `APP_KEY` a nespouští migrace ani seed.
- Cizí klíče `course_terms`, `registrations` a `lessons` mají `cascadeOnDelete` – smazání kurzu, termínu nebo studenta ve Filamentu fyzicky smaže i navázané přihlášky, což je v rozporu s pravidlem auditovatelnosti.
- Propadlé nabídky z pořadníku (24 h) se automaticky neposouvají (chybí plánovaná úloha); odůvodnění administrátorské výjimky u osvědčení se zatím neukládá.

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
