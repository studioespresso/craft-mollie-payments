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
                        {text: 'Templating', link: '/template'},
                        {text: 'Settings', link: '/settings'},

                    ]
            },
            {
                text: 'Customization',
                items:
                    [
                        {text: 'Payments elements', link: '/element'},
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