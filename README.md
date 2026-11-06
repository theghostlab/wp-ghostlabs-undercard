# Undercard — source repository

This is the source of **Undercard**, the free WordPress plugin submitted to WordPress.org as
`ghostlabs-undercard`. It is the source, not the product page: to read what the plugin does, see
[`readme.txt`](readme.txt).

Author profile cards as native blocks — guest authors, co-authors, roles and bylines. Four designs,
up to five layouts, light and dark.

Requires **WordPress 6.6+** and **PHP 8.1+**. Licensed **GPL-2.0-or-later**.

---

## Why it exists

WordPress.org asks that a plugin's shipped JavaScript be readable rather than only minified.
Bundling the sources inside the plugin would put them on every install that will never read
them, so they live here instead, and `readme.txt` links this repository under
**== Source code ==**.

## What this repository contains

The plugin exactly as distributed at the version the newest tag names, **plus** the uncompiled
sources behind its compiled assets. The published bundles are compiled from these files, in this
layout, so a rebuild here reproduces them byte for byte rather than merely producing something
equivalent.

| Path | What it is |
|---|---|
| `ghostlabs-undercard.php`, `bootstrap.php` | plugin entry point, named for the slug |
| `inc/` | PHP — the author record, the render path, REST routes |
| `src/` | editor, block and admin sources → compiled into `build/` |
| `build/` | compiled output, as published |
| `assets/fonts/` | the card typefaces, self-hosted, under the SIL OFL (`OFL.txt`) |
| `vendor/` | Composer's autoloader and the plugin's runtime PHP dependencies |
| `package.json`, `pnpm-lock.yaml`, `pnpm-workspace.yaml`, `webpack.config.cjs` | everything needed to reproduce the compile |
| `readme.txt`, `index.php` | the WordPress.org listing text and the directory guard |

## The claim this repository makes

A clean install and a rebuild reproduce the shipped bundles byte for byte:

```sh
pnpm install --frozen-lockfile
GHOSTLABS_MINIFY=0 pnpm run build
git diff --exit-code build/
```

`GHOSTLABS_MINIFY=0` builds readable, unminified assets, which are what this repository and the
plugin submitted to WordPress.org carry. Without it the same sources build minified, and that
reproduces the assets released versions of the plugin ship, byte for byte too.

That is checked before anything is published here — an unverified claim would be worse than no
claim, because this is exactly what a reviewer checks.

Everything here builds from public packages. There are no private registries and no credentials
involved — if `pnpm install` fails for you, that is a bug and we would like to hear about it.

## What is not here

- **Tests.** They are not a build input, and shipping them drags the whole dev toolchain behind
  them. They live in the private monorepo this is generated from.
- **Prose comments.** The code is published unobfuscated and unminified, which is what the
  guideline asks for; the reasoning behind it is kept where it is read, in the monorepo. The
  paid-upgrade path — the code that makes remote calls — keeps its comments in full.
- **`node_modules`, `vendor/bin`, build tooling for the monorepo itself.**

## Contributing

This repository is generated. Commits made here are overwritten by the next release, so a pull
request against it cannot be merged in the usual sense — but an issue is welcome and will be
fixed at the source.
