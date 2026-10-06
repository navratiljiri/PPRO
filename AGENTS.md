# AGENTS.md – Zásady a provozní pravidla AI asistenta (Antigravity)

Tento dokument definuje závazná pravidla chování, pracovní postupy a standardy pro AI asistenta při vývoji semestrálního projektu předmětu **Pokročilé programování (PPRO)** na FIM UHK (ZS 2026/2027).

---

## 1. Kontext projektu
- **Téma:** Akademie Trutnov – Kurzy vzdělávacího centra (Zadání C).
- **Klient:** Ing. Lucie Bartošová.
- **Cíl:** Plně funkční, třívrstvá aplikace s relační databází v Dockeru, migracemi, automatizovanými testy a snadným spuštěním přes `docker compose up`.
- **Role AI:** Párový programátor, softwarový architekt a technický dokumentátor.

---

## 2. Závazná pravidla pro Git

### 2.1 Zprávy a přepínač `-m` u commitů
- **VŽDY** provádět commity s přepínačem `-m` a věcným popisem provedených změn (doporučen standard Conventional Commits, např. `feat:`, `fix:`, `docs:`, `refactor:`, `test:`, `chore:`).
- Nikdy neprovádět prázdné nebo nepopisné commity.

### 2.2 Zákaz svévolného `git push`
- **Agent NIKDY nesmí samostatně provést `git push`.**
- Operace `git push` je povolena **VÝHRADNĚ po explicitním pokynu uživatele** v chatu (např. *„pushni to do repozitáře“*, *„proveď push“*).

### 2.3 Kontrola a aktualizace dokumentace před commitem
- Před každým commitem agent zkontroluje, zda provedené změny mají vliv na architekturu, datový model, rozhodnutí nebo funkčnost popsanou v `README.md`.
- V případě jakékoliv změny agent **nejprve aktualizuje `README.md`** a až následně provede commit.
- Dokumentace musí zůstávat 100% aktuální v každém bodě historie projektu.

---

## 3. Technická dokumentace a Changelog

- [README.md](file:///c:/Develop/PPRO/README.md) je jediným autoritativním zdrojem pravdy pro architekturu, byznys pravidla a rozhodnutí.
  - Obsahuje:
    1. Kompletní zadání klienta a jeho rozbor.
    2. Obchodní pravidla (business constraints).
    3. **Oddíl Rozhodnutí:** Záznam všech architektonických a procesních rozhodnutí (zejména k 5 otevřeným bodům ze zadání) včetně jejich věcného zdůvodnění.
    4. Technický návrh (vrstvy, datový model, schémata).
    5. Návod na zprovoznění a testování (`docker compose up`).
- [CHANGELOG.md](file:///c:/Develop/PPRO/CHANGELOG.md) slouží k vedení podrobné historie verzí a úprav kódu podle standardu [Keep a Changelog](https://keepachangelog.com/cs/1.0.0/) a Sémantického verzování.
- Pokud klient (vyučující na cvičení) specifikuje novou změnu, tato změna se nejprve zaeviduje do [README.md](file:///c:/Develop/PPRO/README.md) v sekci Rozhodnutí a následně promítne do [CHANGELOG.md](file:///c:/Develop/PPRO/CHANGELOG.md).

---

## 4. Architektonické standardy projektu (PPRO Minimum)

Agent při jakýchkoliv návrzích a implementaci důsledně dodržuje následující principy:

1. **Striktní třívrstvá architektura:**
   - **Prezentační vrstva:** UI / Web / API kontrolery.
   - **Aplikační / Byznys vrstva:** Doménové modely, aplikační služby, validace a vynucení obchodních pravidel.
   - **Datová / Perzistentní vrstva:** Repozitáře, ORM mapování, přístup k databázi.
   - *Závislosti směřují výhradně jedním směrem.*
2. **Kontejnerizace & Relační databáze:**
   - Databáze běží v Docker kontejneru.
   - Veškeré změny databázového schématu jsou prováděny výhradně pomocí verzovaných migrací.
3. **Doménový model:**
   - Nejméně 5 plnohodnotných entit.
   - Alespoň jedna vazba M:N (např. přiřazení lektorů k termínům/lekcím nebo studentů ke kurzům).
4. **Vynucení obchodních pravidel klienta:**
   - Kapacitu termínu nelze překročit za žádných okolností (ani souběžnými požadavky).
   - Pořadí v pořadníku (waiting list) se nesmí přeskakovat.
   - Čísla osvědčení jsou unikátní a vydaná osvědčení jsou neměnná.
   - Zrušené přihlášky se fyzicky nemažou (uchování pro auditní účely).
5. **Kvalita a testy:**
   - Klíčová byznys pravidla (kapacita, posun v pořadníku, dokončení kurzu, certifikace) musí být pokryta automatizovanými testy.
6. **Jednoduché spuštění:**
   - Aplikace a databáze musí být bezproblémově spustitelné pomocí `docker compose up`.
7. **Syntetická data:**
   - Žádná reálná osobní data ani autentizační údaje v repozitáři.
8. **Jazyk kódu – striktně angličtina:**
   - Veškeré proměnné, metody, třídy, databázové tabulky a sloupce, parametry a routy MUSÍ být pojmenovány výhradně v angličtině (např. `Course`, `duration_hours`, `is_accredited`, `code`). Čeština je vyhrazena pouze pro texty uživatelského rozhraní (UI), zadání a dokumentaci.

---

## 5. Pre-commit hook
V repozitáři je aktivní git pre-commit hook (ve složce `.githooks/pre-commit` a v `.git/hooks/pre-commit`), který před vytvořením každého commitu striktně kontroluje:
- existenci a validitu [README.md](file:///c:/Develop/PPRO/README.md) a [CHANGELOG.md](file:///c:/Develop/PPRO/CHANGELOG.md),
- zda nedošlo ke commitu citlivých souborů (např. `.env`),
- **Striktní režim (`exit 1`):** Pokud commit obsahuje změny v kódu či konfiguraci projektu, musí obsahovat také aktualizaci `CHANGELOG.md` a/nebo `README.md`. V opačném případě je commit zablokován.

