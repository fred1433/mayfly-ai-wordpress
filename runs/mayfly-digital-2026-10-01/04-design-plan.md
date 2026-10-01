# Design plan

Written by the build agent before any theme code, then reviewed against the generic defaults (section "Review").

## Where the direction comes from
Mayfly's wordmark already holds a layout: the mayfly's body is the vertical bar between MAYFLY and DIGITAL, one word
set against each side of it. The mark is drawn in single-weight orange lines. Liam's book cover, the newest thing
he has published, is navy with fine lines on a grid and a serif title. The site takes all three: a line, a navy
ink, a serif to read.

**Concept: the axis.** One orange hairline runs down every page, the mayfly's body extended. Headings stand to its
left, set flush against it as MAYFLY is in the wordmark; text reads to its right, as DIGITAL does. At the top of
the home page the axis begins as the mayfly itself, drawn large in line.

## Colour (sampled where it exists)
| Name | Hex | Role |
|---|---|---|
| Mist | `#F5F6F3` | page background, cool, not cream |
| Navy | `#17213A` | text and buttons, from the book cover |
| Graphite | `#404041` | the wordmark grey, secondary text |
| Mayfly orange | `#FF8A00` | the mark, the axis, focus rings. Lines only: never text, it fails contrast on Mist |
| Reed | `#D3D7CF` | hairlines between rows |

## Type
- **Jost** (geometric sans, 300 and 400) for headings and navigation: close kin to the thin geometric capitals of
  the wordmark, used in sentence case.
- **Newsreader** (text serif, 400 and italic) for reading: the operating-guide voice of the book.
- Scale 1.25 from an 18 px body: 18, 22.5, 28, 35, 44, 55, up to 72 for the home headline. Body line height 1.6,
  measure under 68 characters.

## Layout
```
 [MAYFLY|DIGITAL]                         Services  Process  About  Contact
                    ____\   /^\   /____
                    \____\__|_|__/____/        the mark, drawn in line, ~520 px
                            \|/
                             |
      A digital partner to   |   Digital marketing and AI consulting,
       ambitious Irish brands.|   from Dublin since 2012.
                             |   [Book a consultation]
                             |
                    About    |   Mayfly Digital is an online marketing agency...
                             |
                 Services    |   Integrated Digital Marketing Strategy
                             |   Developing cohesive, cross-channel ...
                             |   ------------------------------------
                             o   (axis ends in a point above the footer)
```
- Axis at 40 % of a 1200 px frame on desktop. Left column right-aligned, right column left-aligned.
- Under 782 px the axis moves to the left gutter; everything is left-aligned to its right.
- Process steps are a real sequence: they sit on the axis as numbered points, 1 to 3. Nothing else is numbered.

## Principles
1. One memorable thing: the mayfly drawn in line at the top of the home page, becoming the axis. Everything else
   is quiet.
2. One motion: on load, the mark draws itself once and the axis grows down. Nothing else animates. Off under
   reduced motion.
3. No labels above headings, no cards, no shadows, no gradients, no arrows on links.
4. Every sentence comes from `03-content-map.md`, which cites its source.

## Review against the generic defaults (build agent, before coding)
- First idea was an ivory page with a serif display and the orange as accent. That is the commonest generated look
  (cream, high-contrast serif, warm accent). Changed: cool Mist background, geometric sans for display, the orange
  kept to lines only.
- First layout idea was a split hero (text left, picture right) followed by a three-column service grid: the
  default agency page. Changed: the axis, which comes from Mayfly's own wordmark and could not be moved to another
  client without looking wrong.
- Considered a faint drafting grid behind the hero, after the book cover. Dropped: two devices compete, the axis
  is enough.
- Kept, knowingly: hairline rules between service rows. They carry structure (one service per row), not decoration.
