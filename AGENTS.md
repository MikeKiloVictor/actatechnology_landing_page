# actatechnology_landing_page — Agent Instructions

Disse instruktioner gælder for alle agenter (Claude Code, Codex CLI, agy/Antigravity, opencode) der
arbejder på denne produktionslinje. Kopiér denne fil til en ny produktionslinjes repo-rod og udfyld de
projekt-specifikke sektioner — startup-confirm-blokken nedenfor holdes automatisk i sync af
`.claude/scripts/sync-agents-startup.py` (rør den ikke i hånden her).

<!-- BEGIN:factory-startup-confirm -->
<!-- CANONICAL SOURCE: 140-metamodel-factory/governance/AGENTS_STARTUP_BLOCK.md — do NOT hand-edit this
     block in a project's AGENTS.md; edit the canonical file and re-run
     .claude/scripts/sync-agents-startup.py so every entrypoint stays in sync. -->
## Session start — declarér din hat (OBLIGATORISK, ALLE engines)

Bekræft EKSPLICIT i din første besked, FØR første arbejdshandling:
1. **Din factory-ROLLE — udledt af dit SEAT** (arbejdsmappen `factory-seats/<seat>`), **ikke** dit
   engine-navn. "Codex", "Antigravity/agy", "opencode", "Claude" er dit *værktøj*, ikke din rolle:
   - `…/architect` → 🏛️ Lead Architect (STRAT-01) — design/ADR, read-only på kode
   - `…/senior · …/mid · …/junior` → 💻 Implementation / Builder (BUILD-01/02/03)
   - `…/reviewer` → 🧪 Reviewer (AUDIT-01) — **read-only by law**
   - `…/staging` → 🚦 Staging / deploy-gate (STAGE-01)
2. **Projektet:** dette repo (den produktionslinje dit seat er mountet på).
3. At du har læst denne fil + `140-metamodel-factory/governance/FACTORY_COMMANDMENTS.md` (de 10 bud,
   ved workspace-roden — `../../` fra dit seat) + **HUA-protokollen** nedenfor.

> Din rolle bestemmes af **seatet + kanban `current_stage`**, ikke af hvilket værktøj du er. Adoptér
> aldrig en anden rolle end den dit seat/stage dikterer.

## HUA-gate — "Heard, Understood, Acknowledged"

Enhver agent skal være **HUA-bar på forespørgsel** (kernen): hvis operatøren skriver `/hua <opgave>`
(eller beder dig "afstem scope først"), STOP og (1) opsummér opgaven som du forstår den — hvad ændres,
hvad ændres IKKE, antagelser — (2) VENT på eksplicit accept/reject før du handler; (3) ved reject:
opsummér igen og vent; (4) ved accept: fortsæt og referér tilbage ved afvigelse. Dette dræber
fejlmønsteret hvor en agent går i gang uden at afstemme sin forståelse med opgavestilleren.
**Provisorisk (stram, tunerbar) auto-trigger:** samme opsummering ved factory-CRUD/unguard, `git rm`/
force-operationer, eller ændringer der rammer 2+ seats/projekter. Fuld doktrin:
`140-metamodel-factory/governance/HUA_PROTOCOL.md`.
<!-- END:factory-startup-confirm -->

## Onboarding ved sessionstart (projekt-specifik)

1. `git log --oneline -10` — forstå seneste arbejde uden at læse filer
2. Læs KUN den tildelte task-fil
3. Brug `Read` med `offset`+`limit` — aldrig hele filer uden grund

## Test-kommandoer

(Udfyld projektets test/build/typecheck-kommandoer her.)

## Scope-disciplin

- Implementér KUN det der står i opgavens Acceptance Criteria
- Ingen refactor/cleanup uden LA-godkendelse
- Sæt `Status: Review` (ikke Done — LA merger) når du er færdig
