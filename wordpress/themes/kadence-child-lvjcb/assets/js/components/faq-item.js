'use strict';

/**
 * FAQ Accordion Item behavior: single delegated listener handles any
 * number of instances on a page. Single-open coordination is scoped to
 * each item's own parent element, so the parent section's markup — not
 * this script — defines what counts as one accordion "group".
 */
( function () {

	document.addEventListener( 'click', function ( event ) {
		var toggle = event.target.closest( '.lvjcb-faq-item__toggle' );
		if ( ! toggle ) {
			return;
		}

		var item  = toggle.closest( '.lvjcb-faq-item' );
		var panel = document.getElementById( toggle.getAttribute( 'aria-controls' ) );
		if ( ! item || ! panel ) {
			return;
		}

		var isOpen = 'true' === toggle.getAttribute( 'aria-expanded' );

		if ( isOpen ) {
			closeItem( item, toggle, panel );
			return;
		}

		var group = item.parentElement;
		if ( group ) {
			group.querySelectorAll( '.lvjcb-faq-item.is-open' ).forEach( function ( openItem ) {
				if ( openItem === item ) {
					return;
				}
				var openToggle = openItem.querySelector( '.lvjcb-faq-item__toggle' );
				var openPanel  = document.getElementById( openToggle.getAttribute( 'aria-controls' ) );
				if ( openToggle && openPanel ) {
					closeItem( openItem, openToggle, openPanel );
				}
			} );
		}

		openItem( item, toggle, panel );
	} );

	function openItem( item, toggle, panel ) {
		panel.hidden = false;
		// Single forced reflow, required so the browser commits the
		// display:none -> display:grid change before the class toggle
		// changes grid-template-rows — otherwise the two changes can
		// coalesce and the transition never visibly plays.
		void panel.offsetHeight;
		item.classList.add( 'is-open' );
		toggle.setAttribute( 'aria-expanded', 'true' );
	}

	function closeItem( item, toggle, panel ) {
		item.classList.remove( 'is-open' );
		toggle.setAttribute( 'aria-expanded', 'false' );

		var duration = parseFloat( getComputedStyle( panel ).transitionDuration ) || 0;

		if ( 0 === duration ) {
			panel.hidden = true;
			return;
		}

		panel.addEventListener( 'transitionend', function hideAfterTransition( event ) {
			panel.removeEventListener( 'transitionend', hideAfterTransition );

			if ( event.target !== panel || item.classList.contains( 'is-open' ) ) {
				return;
			}

			panel.hidden = true;
		} );
	}
} )();
