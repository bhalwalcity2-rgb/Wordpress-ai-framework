'use strict';

/**
 * Hero component behavior: reveals the embedded Quote Form.
 *
 * Two triggers:
 *  - Hero's own "Instant Offer" button toggles the form open/closed.
 *  - Header dispatches 'lvjcb:reveal-quote-form' (see header.js) when its
 *    own Instant Offer trigger is clicked; Hero listens and always opens
 *    (never closes) in response, then scrolls the form into view. Header
 *    has no direct reference to Hero's DOM — this event is the only
 *    connection between the two components.
 */
( function () {

	var toggleBtn = document.getElementById( 'lvjcb-hero-instant-offer' );
	var slot      = document.getElementById( 'lvjcb-quote-form-hero' );

	if ( ! toggleBtn || ! slot ) {
		return;
	}

	var FOCUSABLE_SELECTOR = 'input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])';

	function openForm( options ) {
		options = options || {};

		slot.hidden = false;
		toggleBtn.setAttribute( 'aria-expanded', 'true' );

		if ( options.scroll ) {
			slot.scrollIntoView( {
				behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth',
				block: 'center',
			} );
		}

		var firstField = slot.querySelector( FOCUSABLE_SELECTOR );
		if ( firstField ) {
			firstField.focus();
		} else {
			slot.focus();
		}
	}

	function closeForm() {
		slot.hidden = true;
		toggleBtn.setAttribute( 'aria-expanded', 'false' );
		toggleBtn.focus();
	}

	toggleBtn.addEventListener( 'click', function () {
		if ( slot.hidden ) {
			openForm( { scroll: false } );
		} else {
			closeForm();
		}
	} );

	document.addEventListener( 'lvjcb:reveal-quote-form', function () {
		openForm( { scroll: true } );
	} );
} )();
