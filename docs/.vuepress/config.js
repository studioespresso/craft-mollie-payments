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
            ['/template', 'Templating'],
            ['/events', 'Events'],
            ['/webhook', 'Webhook'],
            ['/element', 'Elements & concepts'],
            ['/settings', 'Plugin settings'],
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