=== GhostLabs Undercard ===
Contributors: ghostlabs
Tags: author box, guest author, co-authors, multiple authors, author schema
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Four designed author card blocks you place anywhere. Credit guest authors and co-authors, with roles, and no user account needed.

== Description ==

Undercard renders author and profile cards as real Gutenberg blocks. Not a box injected into
your content: blocks you place where you want them, including in template parts and Query Loops,
where each card draws the author of the row it sits in.

Every card is a projection of one author record. Change a job title once and every card showing
that person changes with it.

= Four card families, none of them paywalled =

Design quality is not the upgrade. All four families and every layout ship in this plugin.

* **Meridian** — a typographic business card. Vertical, horizontal, row, chip.
* **Cobalt** — built around a duotone portrait, with an accent color and adjustable framing.
* **Aurora** — effects-led: frosted panel, gradient accents, a stat strip. Adds a mini layout.
* **NFC Profile** — a digital business card with a contact download, a profile QR code and
  the Web Share API.

Each has light, dark and system schemes where the design allows one, and every card is
container-query driven, so it adapts to the space it is given rather than to the viewport.

= Authors, not just accounts =

* **Guest authors** — credit somebody with no WordPress account. They get a real profile URL.
* **Co-authors** — as many as a post needs, in the order you choose.
* **Roles** — author, co-author, editor, reviewer, fact-checker.
* **Bylines** — assigned in the post sidebar, and the theme's own byline can follow them.

These are free. Several plugins charge for them; a free tier that could not credit two people
on one post would not be worth installing.

= Author identity fields =

Job title, employer, education, areas of expertise, contact details and social links, editable
on the user profile screen and on each guest. Cards display what an author filled in and omit
what they did not — an empty row looks broken, a missing one looks designed.

= Contact download =

Cards can offer a `.vcf` contact file. It is gated on what the author deliberately published,
not on a setting somebody else controls: an author with no contact details published serves no
file at all, and any author can opt out entirely.

== Undercard Pro ==

Everything above is free and stays free. Pro adds the machine-readable half:

* **JSON-LD structured data** — a connected `@graph` emitting a `Person` node per author and an
  `Article` node that references them by `@id`, so the article points to each credited person's
  full `Person` node, with the credentials they filled in, rather than carrying only a name.
* **Credentials as structured data** — `jobTitle`, `worksFor`, `alumniOf` and `knowsAbout`. The
  fields are free to fill in and free to display; publishing them to a machine is what Pro adds.
* **Yoast and Rank Math integration** — enriching the graph those plugins already emit, rather
  than competing with it.
* **Author profile pages** — replacing the theme's author archive with a composed profile.

This plugin shows you exactly what you would publish before you buy anything. Look for
**Structured data preview** on any user profile screen: it lists, property by property, the
`Person` node Undercard Pro would publish for that profile.

== External services ==

This plugin contacts one external service, and not when you install or activate it.

**Gravatar (Automattic).** When a card needs an avatar and the author has not uploaded a photo,
the plugin asks Gravatar whether one exists for that account before falling back to initials.
WordPress itself cannot answer this — it returns an avatar URL for any address, and Gravatar
serves a generated placeholder behind it, so every author would appear to have a photo.

* **What is sent:** a request to the avatar URL WordPress itself generates, which contains a
  hash of the user's email address. No new data is created or transmitted by this plugin; it is
  the same URL core already uses to display avatars.
* **When:** only while WordPress's own "Show Avatars" setting is on, only for users with an
  account, and the answer is cached for up to a week, so this is rare.
* **Where the image comes from:** if a Gravatar exists, the visitor's browser loads it from
  Gravatar, exactly as core avatars do anywhere else on the site.

Turning off **Settings → Discussion → Show Avatars** stops both entirely, and an author who
uploads a photo is never looked up at all.

Gravatar terms: https://gravatar.com/tos — privacy: https://automattic.com/privacy/

**Links to GhostLabs — not a service.** The plugin links to two GhostLabs sites from wp-admin and
the block editor. It never contacts either one: nothing is requested from them in the background,
and the plugin sends them nothing.

* **https://theghostlab.io** — Undercard Pro's product page (`/undercard`) and this plugin's
  documentation (`/docs/undercard`). A link to the product page names the screen it was clicked
  on (for example `?from=settings`), and carries nothing else.
* **https://enter.theghostlab.io/account** — your GhostLabs account, where Undercard Pro is
  downloaded after you buy it. It is linked from **Settings → Undercard → Undercard Pro**, under
  **Have a license?**, and opens in a new tab.

Following either link is an ordinary visit to that site in your browser.

GhostLabs terms: https://theghostlab.io/terms-of-service — privacy: https://theghostlab.io/privacy-policy

Nothing else is requested from anywhere. Fonts are bundled with the plugin and served from your
own site; nothing is loaded from a CDN.

== Installation ==

1. Install through **Plugins → Add New**, or upload the plugin folder to `/wp-content/plugins/`.
2. Activate it.
3. Add an Undercard block to a post, a template or a template part.
4. Fill in your details under **Users → Profile → Undercard — Author Identity**.
5. Optional: choose which post types carry a byline under **Settings → Undercard**.

== Frequently Asked Questions ==

= Does this inject a box into my posts automatically? =

No, and deliberately. Undercard has no content filter. You place a block where you want a card,
which means it cannot appear somewhere you did not ask for it and cannot fight your theme for
position. Put one in a template part and it appears on every post that template serves.

= Will the cards work inside a Query Loop? =

Yes. Every card block reads the post it is rendering inside, so one block in a loop draws a
different person per row.

= Can I credit somebody who has no account on my site? =

Yes. Guest authors are free. They get their own profile URL, under `/guest-author/` by default,
and are indistinguishable from registered users everywhere a card is drawn.

= Does it emit structured data? =

Not in this plugin. The **Structured data preview** panel on each profile screen shows you the
`Person` node your site would publish, so you can see exactly what you would be getting. Emitting
it is what Undercard Pro adds.

= Can I restyle the cards? =

Yes. Colors come from CSS custom properties on each card — `--ghostlabs-u-surface`,
`--ghostlabs-u-text`, `--ghostlabs-u-muted` and each design's accents — declared in a cascade
layer, so a rule of yours outside any layer overrides them. Set them on the cards themselves:
`.ghostlabs-u-card[data-uc-family="meridian"] { --ghostlabs-u-surface: #fdfaf3; }`

Every card carries its design in `data-uc-family` (`meridian`, `cobalt`, `aurora` or `nfc`) and
its layout in `data-uc-layout`, so a rule can reach one design or one layout. A few values are set
again for particular layouts — Cobalt's row and chip set `--ghostlabs-u-surface` from
`--ghostlabs-u-panel`, for example — so where an override does not take, match the card's own
selector and load your stylesheet after the plugin's. Two colors are not yours to override from a
stylesheet: Cobalt's accent is set on each block, from its color control, and NFC Profile's QR
code sets its own colors on the code itself.

No card rule uses an ID. The only `!important` declarations shorten animations and transitions
to an instant for visitors whose system asks for reduced motion, and they are meant to win.

= How do I upgrade to Undercard Pro? =

Undercard Pro is a separate plugin. Once you have bought it:

1. Download it from your GhostLabs account. **Settings → Undercard → Undercard Pro** links there
   under **Have a license?**
2. Upload it under **Plugins → Add New Plugin → Upload Plugin**.
3. Activate it, then enter the license key from your receipt email in its settings.

When Undercard Pro starts, it deactivates this plugin. Every setting, byline, guest and profile
field stays exactly as it is, because both plugins read the same data. This plugin is left
installed but inactive, and you can delete it whenever you like.

On a multisite network, a network administrator uploads the plugin.

= Does it work with a block theme? =

Yes. The cards are FSE and template-part compatible, which is the point of them being blocks.

== Screenshots ==

1. The four card families side by side.
2. A card in the editor, with its layout and scheme controls.
3. The byline panel, assigning co-authors and roles to a post.
4. The author identity fields on a user profile screen.
5. The structured data preview, showing what a site would publish.
6. One block in a Query Loop, drawing each row's own author.
7. Three credits on one post, each showing its role.

== Source code ==

The complete, unminified source of this plugin — including the block sources the compiled assets
are built from — is published at:

https://github.com/theghostlab/wp-ghostlabs-undercard

The repository's committed `build/` directory is the readable build, and `pnpm install
--frozen-lockfile && GHOSTLABS_MINIFY=0 pnpm run build` reproduces it byte for byte. Released
versions of the plugin ship the same sources built minified, which `pnpm install
--frozen-lockfile && pnpm run build` reproduces byte for byte.

== Changelog ==

= 1.0.3 =
* Fixed: the link to Gravatar's terms of service under External services, which no longer answered.

= 1.0.2 =
* Fixed: author photos on cards load a 192×192 crop, wherever WordPress can make one, in place of the larger image they loaded before.
* Changed: the structured data preview no longer marks any row as unavailable.

= 1.0.1 =
* Initial release.

== Upgrade Notice ==

= 1.0.3 =
Author photos on cards now load a small 192×192 crop, wherever WordPress can make one, in place of the larger image they loaded before, so pages with cards are lighter.

= 1.0.2 =
Author photos on cards now load a small 192×192 crop, wherever WordPress can make one, in place of the larger image they loaded before, so pages with cards are lighter.

= 1.0.1 =
First release. Four designed author card blocks you place anywhere, with guest authors, co-authors and roles included.
