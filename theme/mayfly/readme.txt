=== Mayfly ===
Block theme for Mayfly Digital. No plugin, no page builder.

Structure
- theme.json: colours, type, spacing. Change a colour here and it changes everywhere.
- templates/: front-page.html, page.html, index.html, 404.html. Each one places the Page's own content
  (core/post-content), so text is edited in Pages, in the standard editor, never in the theme.
- parts/: header.html, footer.html.
- patterns/: header, footer, the mayfly mark at the top of pages, the 404 text, and one reusable section
  ("Start the conversation") an editor can insert from the block inserter.
- inc/mark.php: the mayfly mark, redrawn as SVG lines.
- assets/css/site.css: the axis layout (the only custom CSS).
- schema.json: the organisation's JSON-LD, printed by functions.php.

Page content is not in this theme. It is in content/mayfly/ in the repository and is written into
WordPress Pages once by kit/seed/seed.php. Updating the theme never touches the Pages.

Fonts: Jost and Newsreader, SIL Open Font License, bundled in assets/fonts.
Images: the Mayfly Digital wordmark and the "AI Does the Work" cover belong to their owners.
