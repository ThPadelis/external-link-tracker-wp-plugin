/**
 * External Link Tracker – frontend outbound link tracking.
 * Tracks: left click, Ctrl/Cmd+click, middle mouse (auxclick).
 * Note: "Open link in new tab" from the context menu (right-click) cannot be
 * detected from the page; the browser does not fire an event for that action.
 */
(function () {
	'use strict';

	if ( typeof window.elt === 'undefined' || ! window.elt.rest_url || ! window.elt.nonce ) {
		return;
	}

	var restUrl = window.elt.rest_url;
	var nonce = window.elt.nonce;
	var sourceUrl = window.elt.source_url || (window.location.origin + window.location.pathname + window.location.search);
	var sourcePostId = window.elt.source_post_id || 0;

	var SKIP_PROTOCOLS = ['javascript:', 'mailto:', 'tel:', '#'];
	function isOutbound(link) {
		try {
			var href = (link.getAttribute('href') || '').trim();
			if (!href) return false;
			var lower = href.toLowerCase();
			for (var i = 0; i < SKIP_PROTOCOLS.length; i++) {
				if (lower === SKIP_PROTOCOLS[i] || lower.indexOf(SKIP_PROTOCOLS[i]) === 0) {
					return false;
				}
			}
			if (lower.indexOf('#') === 0) return false;
			var linkHost = link.hostname;
			var pageHost = window.location.hostname;
			return linkHost !== pageHost;
		} catch (e) {
			return false;
		}
	}

	function getAnchorText(link) {
		try {
			var text = (link.innerText || link.textContent || '').trim();
			return text.slice(0, 500);
		} catch (e) {
			return '';
		}
	}

	function sendClick(linkUrl, anchorText) {
		var payload = JSON.stringify({
			link_url: linkUrl,
			anchor_text: anchorText,
			source_url: sourceUrl,
			source_post_id: sourcePostId
		});
		var blob = new Blob([payload], { type: 'application/json' });
		if (navigator.sendBeacon && navigator.sendBeacon(restUrl, blob)) {
			return;
		}
		fetch(restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce
			},
			body: payload,
			keepalive: true
		}).catch(function () {});
	}

	function findOutboundLink(target) {
		var link = target;
		while (link && link !== document && link.nodeName !== 'A') {
			link = link.parentElement;
		}
		if (!link || link.nodeName !== 'A' || !link.href || !isOutbound(link)) return null;
		return link;
	}

	function trackLinkClick(e) {
		var link = findOutboundLink(e.target);
		if (!link) return;
		sendClick(link.href, getAnchorText(link));
	}

	// Left click, Ctrl/Cmd+click (open in new tab)
	document.addEventListener('click', trackLinkClick, true);

	// Middle mouse button (roll button) – opens in new tab in most browsers
	document.addEventListener('auxclick', function (e) {
		if (e.button !== 1) return; // 1 = middle button
		trackLinkClick(e);
	}, true);
})();
