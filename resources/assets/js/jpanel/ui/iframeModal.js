import $ from 'jquery';

export default {
    show() {
        const $iframe = $('<iframe frameborder="0" />').css({
            margin: 0,
            padding: 0,
            border: 0,
            width: '100%',
            height: '100%',
            position: 'fixed',
            opacity: 1,
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            'z-index': 2147483647,
        })

        $iframe.appendTo('body')

        return new Promise((resolve) => {
            $iframe.on('load', () => resolve($iframe))

            const srcDoc = `
                <html>
                <head>
                <meta name="csrf-token" content="${window.axios.defaults.headers.common['X-CSRF-TOKEN']}">
                <link rel="stylesheet" href="${window.Givecloud.settings.jpanel_assets_url}dist/css/vendor.css" />
                <link rel="stylesheet" href="${window.Givecloud.settings.jpanel_assets_url}dist/css/app.css" />
                <link rel="stylesheet" href="${window.Givecloud.settings.jpanel_assets_url}css/jpanel.css" />
                <link rel="stylesheet" href="${window.Givecloud.settings.jpanel_assets_url}dist/css/tailwind.css" />
                <script charset="utf-8" src="https://cdn.givecloud.co/npm/jquery@3.3.1/dist/jquery.min.js"></script>
                </head>
                <body class="antialiased" style="background:transparent!important">
                <script>
                    var Givecloud = window.Givecloud || {};
                    Givecloud.settings = ${JSON.stringify(window.Givecloud.settings)};
                </script>
                <script charset="utf-8" src="${window.Givecloud.settings.jpanel_assets_url}dist/js/vendor.js"></script>
                <script charset="utf-8" src="${window.Givecloud.settings.jpanel_assets_url}dist/js/app.js"></script>
                </body>
                </html>
            `.trim()

            $iframe[0].contentWindow.document.open('text/html', 'replace');
            $iframe[0].contentWindow.document.write(srcDoc);
            $iframe[0].contentWindow.document.close();
        })
    }
}
