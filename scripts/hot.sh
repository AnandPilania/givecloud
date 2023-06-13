#!/bin/bash

touch public/hot

watch() {
    npx webpack --watch \
        --config webpack.config.js \
        --config resources/apps/core/webpack.config.js \
        --config resources/apps/widgets/webpack.config.js
}

admin() {
    npx openapi-typescript resources/schemas/admin-schema.yaml --output ./resources/apps/admin/schema.ts
    npx prettier --write ./resources/apps/admin/schema.ts
    npx webpack serve --config resources/apps/admin/webpack.config.js
}

donation_forms() {
    npx webpack serve --config resources/apps/donation-forms/webpack.config.js
}

embeddable_forms_donate() {
    npx webpack serve --config resources/apps/embeddable-forms/donate/webpack.config.js
}

peer_to_peer() {
    npx openapi-typescript resources/schemas/peer-to-peer-schema.yaml --output ./resources/apps/peer-to-peer/schema.ts
    npx prettier --write ./resources/apps/peer-to-peer/schema.ts
    npx webpack serve --config resources/apps/peer-to-peer/webpack.config.js
}

virtual_events() {
    npx webpack serve --config resources/apps/virtual-events/webpack.config.js
}

# use subshell group to enable ctrl+c will kill everything
(trap 'kill 0' SIGINT; watch & admin & donation_forms & embeddable_forms_donate & peer_to_peer & virtual_events; wait)

rm -Rf public/hot public/**/*.hot-update.*
