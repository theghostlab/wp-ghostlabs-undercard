/**
 * The upgrade tab: the in-place upgrade's states.
 *
 * Glide's vanilla client carrying Cycle Block's `UpgradePanel` states: opened,
 * empty key, working, activating, failed, and the one-click claim for a buyer
 * back from checkout. The markup is `Render\UpgradePanel`'s; this reads its
 * `data-undercard-*` hooks and nothing else.
 *
 * ⚠️ **Every string is PHP's.** They arrive in `ghostlabsUndercardUpgrade.strings`
 * already translated, with the edition's name composed from `TierMatrix` — so
 * this file names no edition and the isolation gate has nothing to find.
 *
 * ⚠️ **No URL is ever built here.** The REST paths and the claim token come from
 * PHP; the activation URL comes back from the server. A client-composed URL is
 * the regression the portfolio convention records as having shipped once.
 */
import apiFetch from '@wordpress/api-fetch';

import './style.scss';

( function ( config ) {
	if ( ! config ) {
		return;
	}

	const panel = document.querySelector( '[data-undercard-inplace]' );

	if ( ! panel ) {
		return;
	}

	const strings = config.strings || {};
	const toggle = panel.querySelector( '[data-undercard-inplace-toggle]' );
	const region = toggle
		? document.getElementById( toggle.getAttribute( 'aria-controls' ) )
		: null;
	const form = panel.querySelector( '[data-undercard-inplace-form]' );
	const input = panel.querySelector( '[data-undercard-inplace-key]' );
	const notice = panel.querySelector( '[data-undercard-inplace-notice]' );
	const submit = panel.querySelector( '[data-undercard-inplace-submit]' );
	const claim = panel.querySelector( '[data-undercard-inplace-claim]' );
	const status = panel.querySelector( '[data-undercard-inplace-status]' );

	if ( ! form || ! input || ! submit || ! status ) {
		return;
	}

	let busy = false;

	/**
	 * ⚠️ `aria-disabled` and `readOnly`, never `disabled`. A disabled control
	 * leaves the tab order, so the field or button a keyboard user is standing on
	 * would vanish underneath them — and `aria-disabled` only announces the
	 * refusal, so every handler checks `busy` itself.
	 */
	function setBusy( next ) {
		busy = next;
		input.readOnly = next;
		form.setAttribute( 'aria-busy', next ? 'true' : 'false' );

		for ( const button of [ submit, claim ] ) {
			if ( button ) {
				button.setAttribute( 'aria-disabled', next ? 'true' : 'false' );
			}
		}
	}

	/** The live region is the only place every outcome is said. */
	function say( message ) {
		status.textContent = message || '';
	}

	/**
	 * The one thing said about an empty key, said twice: beside the field, where
	 * it is part of the field's accessible name (the label wraps both), and into
	 * the status region, so it is actually announced.
	 */
	function missingKey( on ) {
		input.setAttribute( 'aria-invalid', on ? 'true' : 'false' );

		if ( notice ) {
			notice.textContent = on ? strings.keyRequired || '' : '';
			notice.hidden = ! on;
		}
	}

	/**
	 * `keyFault`: the licensing service refused the key itself, so the field is
	 * what is wrong and says so (3.3.1). A network failure or a gone claim is
	 * not the field's fault, and leaves it valid.
	 */
	function fail( message, keyFault = false ) {
		setBusy( false );
		say( message || strings.generic );
		input.setAttribute( 'aria-invalid', keyFault ? 'true' : 'false' );

		// Put the caret where the fix goes: the claim button for a claim, the
		// field otherwise. The status region carries the announcement.
		( claim && ! claim.hidden && region && region.hidden ? claim : input ).focus();
	}

	/**
	 * A 404 on our own route means the route is gone — the swap already
	 * happened on an earlier request, and this edition is no longer running.
	 */
	function isGone( error ) {
		return (
			!! error &&
			( error.code === 'rest_no_route' ||
				( error.data && error.data.status === 404 ) )
		);
	}

	function send( path, data ) {
		setBusy( true );
		say( strings.working );

		apiFetch( { path, method: 'POST', data } ).then(
			( result ) => {
				if ( result && result.ok && result.activationUrl ) {
					say( strings.activating );
					window.location.assign( result.activationUrl );

					return;
				}

				fail( result && result.message ? result.message : strings.generic, !! result && result.stage === 'verify' );
			},
			( error ) => {
				if ( isGone( error ) ) {
					say( strings.alreadySwapped );
					window.location.assign( config.pluginsUrl );

					return;
				}

				if ( error && error.message && error.code !== 'fetch_error' ) {
					fail( error.message );

					return;
				}

				fail( strings.network );
			}
		);
	}

	if ( toggle && region ) {
		toggle.addEventListener( 'click', () => {
			const open = region.hidden;

			region.hidden = ! open;
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

			if ( open ) {
				input.focus();
			}
		} );
	}

	input.addEventListener( 'input', () => missingKey( false ) );

	form.addEventListener( 'submit', ( event ) => {
		event.preventDefault();

		if ( busy ) {
			return;
		}

		const key = input.value.trim();

		if ( key === '' ) {
			missingKey( true );
			say( strings.keyRequired );
			input.focus();

			return;
		}

		missingKey( false );
		send( config.upgradePath, { licenseKey: key } );
	} );

	if ( claim && config.claim ) {
		claim.addEventListener( 'click', () => {
			if ( busy ) {
				return;
			}

			send( config.claimPath, { claimToken: config.claim } );
		} );
	}
} )( window.ghostlabsUndercardUpgrade );
