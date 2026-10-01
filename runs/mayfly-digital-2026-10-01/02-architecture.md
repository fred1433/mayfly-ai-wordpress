# Site architecture

Decided by the build agent, inside the limit the orchestrator set (five pages, nothing invented).

| Page | Job | Content from |
|---|---|---|
| Home `/` | Say who Mayfly is and what it does in one screen, then route to the four other pages | M |
| Services `/services/` | The four services, the cross-channel summary, one way to ask about each | M |
| Process `/process/` | The three steps, as a real sequence | M |
| About `/about/` | The agency, then Liam: his role, method, books | M, L, C |
| Contact `/contact/` | One action: email the production team | M |

Not built, and why:
- Case studies, team, client logos, testimonials, blog: nothing public to build them from. They are questions in
  `questions-for-mayfly.md`, not placeholders in the site.
- A contact form: it would need a form plugin and somewhere to send to. The current site sends every call to
  action to one email address, so the new one does the same until Mayfly says otherwise.

## WordPress choices (build agent)
- **Block theme (Full Site Editing), no page builder, no plugin.** The two existing sites run on Divi and Avada.
  A block theme is plain text and JSON (`theme.json`, HTML templates, PHP patterns): an AI step can write it, and the
  next AI step or a developer can read and diff it. It needs no paid builder licence, it installs from a git
  repository, and Mayfly's team edits every page in the standard WordPress editor.
- **Content lives in theme patterns** placed by page templates (`templates/page-services.html` and so on). The site
  needs no database import to run, which is what lets it boot in WordPress Playground from one link. On a real
  install the same patterns become editable pages.
- **Schema**: one `ProfessionalService` JSON-LD block from `functions.php`, built only from facts in `01-brief.md`.
- **Fonts and images inside the theme**: no request to a font service, nothing that breaks offline.
