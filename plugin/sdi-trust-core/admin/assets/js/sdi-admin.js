/**
 * SDI Trust Core — wp-admin behavior.
 *
 * Confirmation prompts for destructive actions only. No AJAX here — every
 * admin action is a plain, nonce-verified form POST or a nonce'd link, so
 * this file only adds a safety confirm() in front of them.
 */
( function () {
	'use strict';

	function confirmDestructive( selector, message ) {
		document.querySelectorAll( selector ).forEach( function ( el ) {
			el.addEventListener( 'click', function ( event ) {
				if ( ! window.confirm( message ) ) {
					event.preventDefault();
				}
			} );
		} );
	}

	function init() {
		confirmDestructive( '.sdi-admin-reject', 'Reject this referral? No points will be awarded.' );

		document.querySelectorAll( 'form.sdi-admin-form' ).forEach( function ( form ) {
			var submit = form.querySelector( 'button[type="submit"]' );
			if ( ! submit || ! form.querySelector( '[name="amount"]' ) ) {
				return;
			}
			form.addEventListener( 'submit', function ( event ) {
				var amount = form.querySelector( '[name="amount"]' );
				if ( amount && parseInt( amount.value, 10 ) < 0 ) {
					if ( ! window.confirm( 'This will deduct points from the member\'s balance. Continue?' ) ) {
						event.preventDefault();
					}
				}
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
