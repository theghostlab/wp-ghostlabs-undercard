# ghostlabs/wp-kit — vendored source

Generic WordPress plumbing shared by GhostLabs plugins: a reflection-based DTO mapper, six
contracts, a polymorphic REST settings route and two small services. GPL-2.0-or-later, same as
this plugin. `LICENSE` is the upstream copy.

**Upstream:** `ghostlabs/wp-kit` **v1.0.2**.

## Why this is source and not a Composer dependency

The free edition is published to WordPress.org together with a public source repository, and that
repository has to build for anyone who clones it. `ghostlabs/wp-kit` is distributed from a private
GitHub repository — an unauthenticated fetch 404s and it is not on Packagist — so a `require` on it
would make `composer install` fail for every reader, and a repository whose sources cannot be shown
to produce the shipped binaries is decoration.

Carrying the source instead costs 13 files and removes the last credential this build needs. Free
now installs with no registry authentication of any kind, which matters beyond convenience: the
fewer credentials a build needs, the fewer reasons somebody else cannot build it.

## Do not add a PSR-4 entry for this

`composer.json` upstream declares an **empty `autoload` block on purpose**, and the copy here drops
that manifest entirely. Composer never autoloads `GhostLabs\WPKit\`; `wp-kit.php` does, through a
version gate borrowed from Action Scheduler. `Boot\Edition::libraries()` requires `wp-kit.php` at
include time so the copy registers before the election runs on `plugins_loaded` priority 0.

Several GhostLabs plugins bundle their own copy, and **the newest registered one serves all of
them**. That is why the registered version string here is still `'1.0.2'` and must stay honest: a
copy claiming a version it is not can win an election and then lack a class the real release of that
version has, which is a fatal in every plugin on the site compiled against it.

## Divergence from upstream v1.0.2

Three things, all deliberate. The first two are **docblock-only**; the third adds a guard that
changes nothing inside WordPress — no statement that runs on a request differs, so the election's
assumption that one version means one behaviour still holds.

1. **`composer.json` and the upstream `README.md` are not copied.** Nothing installs this through
   Composer, and that README is the library's own development documentation — written for people
   working on the library rather than on a plugin that carries it.
2. **Eight docblock lines rewritten** in `src/Contracts/PluginIdentityInterface.php` and
   `src/RestRoute/AbstractSettingsRoutes.php`. Both explained the split by naming the package on
   the other side of it, which a publicly distributed tier must not carry. Each is a change of
   subject, not a deletion: the mechanism is the interesting half and reads better stated without
   naming any particular consumer.
3. **A direct-access guard** (`if ( ! defined( 'ABSPATH' ) ) exit;`) at the top of `wp-kit.php`
   and `autoload.php`. Both run statements at include time, and Plugin Check reports a PHP file
   that does so without the guard as an **ERROR** (`missing_direct_file_access_protection`),
   which refuses a WordPress.org submission. Added 2026-09-25. Upstream should carry the same
   two lines so a re-sync does not drop them.

## Re-syncing

Copy `wp-kit.php`, `autoload.php`, `LICENSE` and `src/` from the new tag, drop `composer.json` and
`README.md`, then **re-apply the rewrites and the guard above** and update the version in this file.

A careless re-sync reintroduces all eight lines at once. That is caught rather than trusted —
`build-scripts/verify-tier-isolation.sh` scans the assembled build and `TierIsolationTest` scans
this directory on every `pnpm run test:unit` — but it is far cheaper to fix the two docblocks
upstream and delete this section. **Follow-up: land those rewrites in `ghostlabs/wp-kit` itself so
the next tag needs no divergence at all.**
