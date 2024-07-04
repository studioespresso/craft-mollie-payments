import{_ as e}from"./chunks/paymentform.IiyupwWH.js";import{_ as t,c as a,o as s,a1 as i}from"./chunks/framework.6kAX4a8T.js";const n="/craft-mollie-payments/assets/transaction.DntwOcWo.png",g=JSON.parse('{"title":"Getting Started - Mollie payments","description":"","frontmatter":{"title":"Getting Started - Mollie payments","prev":false,"next":false},"headers":[],"relativePath":"element.md","filePath":"element.md"}'),o={name:"element.md"},l=i('<h1 id="core-concepts" tabindex="-1">Core concepts <a class="header-anchor" href="#core-concepts" aria-label="Permalink to &quot;Core concepts&quot;">​</a></h1><h2 id="payment-elements" tabindex="-1">Payment elements <a class="header-anchor" href="#payment-elements" aria-label="Permalink to &quot;Payment elements&quot;">​</a></h2><p>Each payment is a full Craft element, which means you can add custom fields to it (which you do through the <a href="/craft-mollie-payments/general.html#_2-create-a-payment-form">form</a> it&#39;s linked to). Apart from those custom fields, each payment has the following required properties</p><ul><li>email</li><li>amount (the amount to me payed. This has to be passed on a hidden field with the <a href="https://docs.craftcms.com/v3/dev/filters.html#hash" target="_blank" rel="noreferrer">hash</a> filter)</li><li>formId (the ID of the <a href="#payment-forms">payment form</a> it is links to)</li></ul><p>In the CP, payments are grouped by form and can be search on email, amount or any of the searchable custom fields you&#39;ve added.</p><h2 id="payment-forms" tabindex="-1">Payment forms <a class="header-anchor" href="#payment-forms" aria-label="Permalink to &quot;Payment forms&quot;">​</a></h2><p>Payment forms are where you define the field layout for the your payment, as well as the following properties:</p><ul><li>the title &amp; handle of the form, currently only visible in the CP</li><li>the currency the payment will be processes in</li><li>the description of the payment in Mollie. This field works like the dynamic title field on an Entrytype, so you can use custom fields in it as well. Will default to <code>Order #number</code>.</li></ul><img src="'+e+'"><p>Which form the payment is for is determined by passing the following hidden field along in your payment form template:</p><div class="language-html vp-adaptive-theme"><button title="Copy Code" class="copy"></button><span class="lang">html</span><pre class="shiki shiki-themes github-light github-dark vp-code" tabindex="0"><code><span class="line"><span style="--shiki-light:#24292E;--shiki-dark:#E1E4E8;">&lt;</span><span style="--shiki-light:#22863A;--shiki-dark:#85E89D;">input</span><span style="--shiki-light:#6F42C1;--shiki-dark:#B392F0;"> type</span><span style="--shiki-light:#24292E;--shiki-dark:#E1E4E8;">=</span><span style="--shiki-light:#032F62;--shiki-dark:#9ECBFF;">&quot;hidden&quot;</span><span style="--shiki-light:#6F42C1;--shiki-dark:#B392F0;"> name</span><span style="--shiki-light:#24292E;--shiki-dark:#E1E4E8;">=</span><span style="--shiki-light:#032F62;--shiki-dark:#9ECBFF;">&quot;form&quot;</span><span style="--shiki-light:#6F42C1;--shiki-dark:#B392F0;"> value</span><span style="--shiki-light:#24292E;--shiki-dark:#E1E4E8;">=</span><span style="--shiki-light:#032F62;--shiki-dark:#9ECBFF;">&quot;{{ 3|hash }}&quot;</span><span style="--shiki-light:#24292E;--shiki-dark:#E1E4E8;">&gt;</span></span></code></pre></div><p><strong>Note:</strong> the form ID has to passed using the <a href="https://docs.craftcms.com/v3/dev/filters.html#hash" target="_blank" rel="noreferrer">hash</a> function, otherwise the payment will not be submitted.</p><h2 id="payment-transactions" tabindex="-1">Payment transactions <a class="header-anchor" href="#payment-transactions" aria-label="Permalink to &quot;Payment transactions&quot;">​</a></h2><p>Once a payment is submitted to the <code>mollie-payments/payment/pay</code> controller and the Payment element is validated, a new Payment transaction will be created throught the Mollie api.</p><p>The ID, amount and status are saved with the transaction, as well as a relation to the Payment element. The list of transactions related to a Payment element is visible on a tab on the element:</p><img src="'+n+'">',16),r=[l];function h(m,p,d,c,y,f){return s(),a("div",null,r)}const _=t(o,[["render",h]]);export{g as __pageData,_ as default};
