'use strict';

/**
 * Quote Form component behavior: submit-state handling only. Validation
 * is entirely native HTML5 constraint validation (required/pattern) —
 * this script only runs once native validation has already passed,
 * since the browser never dispatches 'submit' for an invalid form.
 */
( function () {

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target.closest( '.lvjcb-quote-form' );
		if ( ! form ) {
			return;
		}

		var submitButton = form.querySelector( '.lvjcb-quote-form__submit' );
		if ( submitButton ) {
			submitButton.disabled = true;
		}

		form.setAttribute( 'aria-busy', 'true' );
	} );

	// bfcache restore: if a visitor submits, then navigates back before
	// the destination finishes loading, some browsers restore this exact
	// page (disabled button, aria-busy) from cache rather than reloading
	// it. Reset the state so a page with no submission actually in
	// flight doesn't show one.
	window.addEventListener( 'pageshow', function ( event ) {
		if ( ! event.persisted ) {
			return;
		}

		document.querySelectorAll( '.lvjcb-quote-form[aria-busy="true"]' ).forEach( function ( form ) {
			form.removeAttribute( 'aria-busy' );
			var submitButton = form.querySelector( '.lvjcb-quote-form__submit' );
			if ( submitButton ) {
				submitButton.disabled = false;
			}
		} );
	} );
} )();
