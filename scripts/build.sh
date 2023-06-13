#!/bin/bash

apps() {
    npx webpack "$@" \
        --config webpack.config.js \
        --config resources/apps/admin/webpack.config.js \
        --config resources/apps/core/webpack.config.js \
        --config resources/apps/donation-forms/webpack.config.js \
        --config resources/apps/embeddable-forms/donate/webpack.config.js \
        --config resources/apps/peer-to-peer/webpack.config.js \
        --config resources/apps/virtual-events/webpack.config.js \
        --config resources/apps/widgets/webpack.config.js
}

# use subshell group to enable ctrl+c will kill everything
(trap 'kill 0' SIGINT; apps "$@"; wait)
