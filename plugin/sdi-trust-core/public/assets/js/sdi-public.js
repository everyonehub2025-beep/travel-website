/**
 * SDI Trust Core — public behavior.
 *
 * Progressive enhancement only: the dashboard's tab links are real URLs
 * (?sdi_tab=...) that work with JavaScript disabled. This script just
 * avoids the full page reload when JS is available.
 */
( function () {
	'use strict';

	function initDashboardTabs() {
		var dashboards = document.querySelectorAll( '.sdi-dashboard' );

		dashboards.forEach( function ( dashboard ) {
			var tabs   = dashboard.querySelectorAll( '[data-sdi-tab]' );
			var panels = dashboard.querySelectorAll( '[data-sdi-panel]' );

			tabs.forEach( function ( tab ) {
				tab.addEventListener( 'click', function ( event ) {
					var target = tab.getAttribute( 'data-sdi-tab' );

					if ( ! target ) {
						return;
					}

					event.preventDefault();

					tabs.forEach( function ( t ) {
						var isActive = t === tab;
						t.classList.toggle( 'is-active', isActive );
						t.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
					} );

					panels.forEach( function ( panel ) {
						panel.classList.toggle( 'is-active', panel.getAttribute( 'data-sdi-panel' ) === target );
					} );

					dashboard.setAttribute( 'data-active-tab', target );

					if ( window.history && window.history.replaceState ) {
						var url = new URL( window.location.href );
						url.searchParams.set( 'sdi_tab', target );
						window.history.replaceState( null, '', url.toString() );
					}
				} );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initDashboardTabs );
	} else {
		initDashboardTabs();
	}
} )();
