(window.webpackJsonp=window.webpackJsonp||[]).push([[10],{366:function(t,s,a){"use strict";a.r(s);var e=a(44),n=Object(e.a)({},(function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[a("h1",{attrs:{id:"events"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#events"}},[t._v("#")]),t._v(" Events")]),t._v(" "),a("h3",{attrs:{id:"event-before-payment-save"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#event-before-payment-save"}},[t._v("#")]),t._v(" EVENT_BEFORE_PAYMENT_SAVE")]),t._v(" "),a("p",[t._v("Before the payment element gets saved, you can listen this event:")]),t._v(" "),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("studioespresso"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("molliepayments"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("events"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("PaymentUpdateEvent")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("studioespresso"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("molliepayments"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("MolliePayments")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("studioespresso"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("molliepayments"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("services"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Payment")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("yii"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("base"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Event")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n\n"),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("Event")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("on")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("Payment")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("class")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("MolliePayments")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token constant"}},[t._v("EVENT_BEFORE_PAYMENT_SAVE")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("function")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token class-name type-declaration"}},[t._v("PaymentUpdateEvent")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$event")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// handle the event here")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])]),a("p",[t._v("The event contains the following:")]),t._v(" "),a("ul",[a("li",[t._v("The payment element")]),t._v(" "),a("li",[a("code",[t._v("isNew")]),t._v(": to see if we're saving a new versus an excisting element (right now payment elements can only be saved once so this will always be set to true. In the future when we add updating payment this will change accordingly)")])]),t._v(" "),a("h3",{attrs:{id:"event-after-transaction-update"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#event-after-transaction-update"}},[t._v("#")]),t._v(" EVENT_AFTER_TRANSACTION_UPDATE")]),t._v(" "),a("p",[t._v("When Mollie send back a post request to the plugin's webhook to update the status, the following event is fired:")]),t._v(" "),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("studioespresso"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("molliepayments"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("events"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("TransactionUpdateEvent")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("studioespresso"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("molliepayments"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("MolliePayments")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("studioespresso"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("molliepayments"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("services"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Transaction")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("use")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token package"}},[t._v("yii"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("base"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("\\")]),t._v("Event")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n\n"),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("Event")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("on")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("Transaction")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("class")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token class-name static-context"}},[t._v("MolliePayments")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("::")]),a("span",{pre:!0,attrs:{class:"token constant"}},[t._v("EVENT_AFTER_TRANSACTION_UPDATE")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("function")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token class-name type-declaration"}},[t._v("TransactionUpdateEvent")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$event")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// handle transaction status update")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])]),a("p",[t._v("The event contains:")]),t._v(" "),a("ul",[a("li",[t._v("The payment transaction record")]),t._v(" "),a("li",[t._v("The payment element")]),t._v(" "),a("li",[t._v("The payment status Mollie is sending is (string, "),a("a",{attrs:{href:"https://docs.mollie.com/payments/status-changes",target:"_blank",rel:"noopener noreferrer"}},[t._v("possible statuses"),a("OutboundLink")],1),t._v(")")])]),t._v(" "),a("p",[t._v("More information on this webhook and what it contains can be found "),a("a",{attrs:{href:"https://docs.mollie.com/guides/webhooks",target:"_blank",rel:"noopener noreferrer"}},[t._v("here"),a("OutboundLink")],1)])])}),[],!1,null,null,null);s.default=n.exports}}]);