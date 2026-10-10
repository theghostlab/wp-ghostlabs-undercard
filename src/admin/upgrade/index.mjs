/**
 * Settings → Undercard's stylesheet entry, and nothing else.
 *
 * 🔴 This file used to drive the in-place upgrade's states: a key form, a REST
 * call and an activation redirect. WordPress.org refused that flow on 2026-10-08
 * (Guideline 8), and the panel is now a native `<details>` with two links. It
 * needs no script. The entry stays because webpack emits the stylesheet,
 * `build/admin/style-upgrade.css`, from it.
 */
import './style.scss';
