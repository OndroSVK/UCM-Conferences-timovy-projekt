# M10 Deadlines — plná verzia

V prototype sa tento modul nerobí (backend.md, M10). Modul je v `config/modules.php` vypnutý a nemá service provider.

Plná verzia: pripomienky 7 a 1 deň pred termínom cez Laravel Scheduler, blokovanie po termíne s výnimkou pre admina (O24).

V prototype existuje iba jednoduchá kontrola dátumov priamo vo features:

- registrácia: `Conference::isRegistrationOpen()` v `Registration\Actions\CreateRegistration`,
- príspevok: `Conference::isSubmissionOpen()` v `Submissions\Actions\SubmitSubmission` a `ReplaceSubmissionFile`.
