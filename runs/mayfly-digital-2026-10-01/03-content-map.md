# Content map

Written by the build agent. Two kinds of text only:
- **Quoted**: copied from `sources/*.txt` (case and punctuation may change, words do not). `kit/scripts/qa_sources.py`
  checks every one of them against the sources after the site is built.
- **Composed**: interface words (navigation, buttons, headings) and short lines recombining sourced facts. Each one is
  listed below with what it rests on. The QA script fails on any sentence of the rendered site that is in neither
  group.

## Composed by the chain
Each line between backticks is matched exactly by the QA script.

| Text | Rests on |
|---|---|
| `Digital marketing and AI consulting, from Dublin since 2012.` | M: "Digital Marketing & AI Consulting", "based in Dublin", "Founded in 2012" |
| `Mayfly Digital` | M |
| `Services` | interface |
| `Process` | interface, links to the process section of Services |
| `About` | interface, links to the about section of Home |
| `Contact` | interface |
| `Book a consultation` | M, button label |
| `Get in touch` | M, button label |
| `Start the conversation` | M, button label |
| `How we work` | interface, links to Process |
| `What we do` | interface, links to Services |
| `Strategy` | step 2 title, written from its paragraph; question 3 |
| `Delivery and optimisation` | step 3 title, written from its paragraph; question 3 |
| `Assessment` | M |
| `The agency` | interface |
| `Liam Dennehy` | L |
| `Managing Director` | L: "Managing Director at Mayfly Digital" |
| `Books` | interface |
| `AI Does the Work` | L |
| `Local Internet Marketing For The UK & Ireland` | L |
| `The Irish SME guide to structural advantage.` | C, cover subtitle |
| `Launching November 2026.` | C, "Book launch November 2026" |
| `Keynote speaker` | L: "KEYNOTE SPEAKER" |
| `Liam on LinkedIn` | L, link label |
| `Email the production team` | M: the mailto on every button |
| `productionteam@mayflydigital.ie` | M |
| `Dublin, Ireland` | M: "based in Dublin" |
| `Mayfly Marketing Ltd, trading as Mayfly Digital.` | T: "Mayfly Marketing Ltd (T/A Mayfly Digital)" |
| `Terms of Service` | T |
| `© 2026 Mayfly Digital. All rights reserved.` | M, footer |
| `Liam is also the author of Local Internet Marketing For The UK & Ireland, and a keynote speaker.` | L: the two book titles, "KEYNOTE SPEAKER" |
| `Cover of AI Does the Work by Liam Dennehy` | image alt text |
| `Independent prototype from Mayfly's public material.` | demo notice, Playground only (not part of the theme) |
| `Page not found` | interface, 404 |
| `This address has no page.` | interface, 404 |
| `Go to the home page` | interface, 404 |

## Quoted, page by page
Home
- "A Digital Partner to Ambitious Irish Brands" (headline)
- "Mayfly Digital is an online marketing agency based in Dublin."
- "Founded in 2012, we specialize in providing results driven integrated online marketing solutions for medium and large businesses across the country."
- "We are a team of creative, self-disciplined, self-motivated professionals with a passion to provide your business with a more sophisticated data-driven approach to online marketing and advertising."
- The four service names, the three step names.

Services
- "Developing an integrated, cross-channel strategy to ensure SEO, PPC, video, social and email marketing deliver measurable ROI, while embedding AI into day-to-day business operations."
- The four services and their sentences, as on M.

Process
- The three paragraphs, as on M.

About
- The agency paragraph (M).
- "Liam's methodology is characterised by its clarity and ease of application." (L)
- "Central to Liam's ethos is the belief that effective marketing melds elegant design with simplicity and value augmentation." (L)
- "AI Does the Work is a practical operating guide for owners who want AI embedded properly, not bolted on." (L)
- "Most AI advice focuses on tools and automation." (L)

Contact
- "Reach out for expert guidance, AI integration strategies, consultation services" is from liamdennehy.com and is
  about Liam personally: not used on the agency's contact page.
