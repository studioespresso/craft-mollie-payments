module.exports = {
    title: 'Mollie Payments',
    base: '/craft-mollie-payments',
    head: [
        ['meta', {content: 'https://github.com/studioespresso', property: 'og:see_also',}],
        [
            'script',
            {
                defer: '',
                'data-domain': 'studioespresso.github.io',
                src: 'https://stats.studioespresso.co/js/script.tagged-events.outbound-links.js'
            }
        ],
    ],
    themeConfig: {
        logo: '/img/plugin-logo.svg',
        sidebar: [
            {
                text: 'General',
                items:
                    [
                        {text: 'Getting started', link: '/general'},
                        {text: 'Core concepts', link: '/element'},
                        {text: 'Settings', link: '/settings'},

                    ]
            },
            {
                text: 'Payments',

                items:
                    [
                        {text: 'Basic payment form', link: '/basic-payment-form'},
                        {text: 'Donation form', link: '/donation-payment-form'},
                        {text: 'Multi-step form', link: '/multistep-payment-form'},
                        {text: 'craft.payments', link: '/payment-template-function'},
                    ]
            },
            {
                text: 'Subscriptions',
                items:
                    [
                        {text: 'Getting started', link: '/subscription-getting-started'},
                        {text: 'Subscription form', link: '/subscription-form'},
                    ]
            },
            {
                text: 'Customization',
                items:
                    [
                        {text: 'Events', link: '/events'},
                        {text: 'Webhook', link: '/webhook'},
                    ]
            }

        ],
        nav: [
            {
                text: 'Buy now',
                link: 'https://plugins.craftcms.com/mollie-payments',
            },
            {
                text: 'Report an issue',
                link: 'https://github.com/studioespresso/craft-mollie-payments/issues'
            },
            {
                text: 'GitHub',
                link: 'https://github.com/studioespresso/craft-mollie-payments'
            }
        ]

    }
};