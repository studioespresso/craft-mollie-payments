(window.webpackJsonp=window.webpackJsonp||[]).push([[10],{194:function(t,e,a){"use strict";a.r(e);var s=a(0),o=Object(s.a)({},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[a("h1",{attrs:{id:"payment-status-updates"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#payment-status-updates","aria-hidden":"true"}},[t._v("#")]),t._v(" Payment status updates")]),t._v(" "),a("p",[t._v("The plugin uses webhooks to update payment status and fires an event bases on those.")]),t._v(" "),a("p",[t._v("When the user completes a payment, he/she is redirect back to the site (to the redirect url you provided). That url has a query parameters with the payment's UID and the status we get back from Mollie. Use this status to determine which content should be displayed on the confirmation page.")]),t._v(" "),a("p",[t._v("However, the "),a("router-link",{attrs:{to:"/events.html#event-after-transaction-update"}},[t._v("EVENT_AFTER_TRANSACTION_UPDATE")]),t._v(" is not fired upon this redirect. That happens when Mollie calls our webhook URL.")],1),t._v(" "),a("p",[t._v("More information on this webhook and what it contains can be found "),a("a",{attrs:{href:"https://docs.mollie.com/guides/webhooks",target:"_blank",rel:"noopener noreferrer"}},[t._v("here"),a("OutboundLink")],1),t._v(". Note that to test this locally you'll have to fake to post request, with the right content, for the event to fire and the status to be updated in the CP.")])])},[],!1,null,null,null);e.default=o.exports}}]);