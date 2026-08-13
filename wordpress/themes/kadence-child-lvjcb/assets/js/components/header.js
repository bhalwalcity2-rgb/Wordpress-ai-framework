'use strict';

/**
 * Header component behavior:
 *  - scroll condensation via IntersectionObserver + sentinel
 *  - mobile nav drawer with focus trap (Tab cycling, Escape, focus return)
 *  - dispatches a generic 'lvjcb:reveal-quote-form' event on click of any
 *    [data-lvjcb-reveal-quote-form] trigger; Hero's own script listens for
 *    this and owns actually revealing/scrolling to its embedded Quote Form.
 *    Header does not reach into Hero's DOM directly, keeping the two
 *    components decoupled.
 */
( function () {

	var header   = document.getElementById( 'lvjcb-header' );
	var sentinel = document.getElementById( 'lvjcb-header-sentinel' );

	if ( header && sentinel && 'IntersectionObserver' in window ) {
		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				header.classList.toggle( 'is-condensed', ! entry.isIntersecting );
			} );
		} );
		observer.observe( sentinel );
	}

	document.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( '[data-lvjcb-reveal-quote-form]' ) ) {
			document.dispatchEvent( new CustomEvent( 'lvjcb:reveal-quote-form' ) );
		}
	} );

	var openBtn  = document.getElementById( 'lvjcb-menu-open' );
	var closeBtn = document.getElementById( 'lvjcb-menu-close' );
	var nav      = document.getElementById( 'lvjcb-mobile-nav' );
	var lastFocused = null;

	function getFocusable() {
		return nav.querySelectorAll(
			'a[href], button:not([disabled])'
		);
	}

	function openNav() {
		lastFocused = document.activeElement;
		nav.hidden = false;
		nav.classList.add( 'is-open' );
		openBtn.setAttribute( 'aria-expanded', 'true' );

		var focusable = getFocusable();
		if ( focusable.length ) {
			focusable[ 0 ].focus();
		}

		document.addEventListener( 'keydown', onNavKeydown );
	}

	function closeNav() {
		nav.classList.remove( 'is-open' );
		nav.hidden = true;
		openBtn.setAttribute( 'aria-expanded', 'false' );
		document.removeEventListener( 'keydown', onNavKeydown );

		if ( lastFocused ) {
			lastFocused.focus();
		}
	}

	function onNavKeydown( event ) {
		if ( 'Escape' === event.key ) {
			closeNav();
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		var focusable = getFocusable();
		if ( ! focusable.length ) {
			return;
		}

		var first = focusable[ 0 ];
		var last  = focusable[ focusable.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	if ( openBtn ) {
		openBtn.addEventListener( 'click', openNav );
	}

	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', closeNav );
	}
} )();
