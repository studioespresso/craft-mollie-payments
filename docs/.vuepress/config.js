module.exports = {
    base: '/craft-mollie-payments/',
    title: 'Studio Espresso',
    ga: 'UA-79200406-2',
    head: [
        ['link', { rel: 'icon', href: `/favicon.png` }],
        ['meta', { name: 'theme-color', content: '#3b68b5' }],
    ],
    themeConfig: {
        logo: '/icon-vuepress.svg',
        search: true,
        searchMaxSuggestions: 5,
        docsRepo: 'studioespresso/craft-mollie-payments',
        docsDir: 'docs',
        docsBranch: 'develop',
        editLinks: true,
        editLinkText: 'See something wrong? Let us know!',
        sidebarDepth: 1,
        displayAllHeaders: true,
        sidebar: [
            ['/', 'Introduction'],
            ['/general', 'Getting started'],
            ['/settings', 'Plugin settings'],
            ['/events', 'Events'],
            ['/webhook', 'Webhook'],
            ['/element', 'Core concepts'],
        ],
        nav: [
            {
                text: 'Buy now',
                link: 'https://plugins.craftcms.com/mollie-payments',
            },
            {
                text: 'Issues?',
                link: 'https://github.com/studioespresso/craft-mollie-payments/issues'
            },
            {
                text: 'Questions? Get in touch!',
                link: 'https://www.studioespresso.co/en/contact',
            }
        ]
    }
}