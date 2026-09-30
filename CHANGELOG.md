# Záznam změn (Changelog)

Všechny významné změny v tomto projektu jsou zaznamenávány v tomto souboru.

Formát vychází ze standardu [Keep a Changelog](https://keepachangelog.com/cs/1.0.0/)
a projekt dodržuje [Sémantické verzování](https://semver.org/lang/cs/).

---

## [Nenasazeno] (Unreleased)

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
