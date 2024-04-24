module.exports = {
    title: 'Mollie Payments for Craft',
    base: '/craft-mollie-payments',
    themeConfig: {

        // logo: {light: '/icon-vuepress.svg', dark: '/icon-vuepress-light.svg'},
        sidebar: [
            {
                items: [
                    {text: 'Introduction', link: '/index'},
                    {text: 'Getting started', link: '/general'},
                    {text: 'Templating', link: '/template'},
                    {text: 'Events', link: '/events'},
                    {text: 'Webhook', link: '/webhook'},
                    {text: 'Element', link: '/element'},
                    {text: 'Settings', link: '/settings'},
                ]
            },


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