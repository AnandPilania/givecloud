
import jQuery from 'jquery';
import tinymce from 'tinymce';

const toolbarHeight = 30;

function getToolbars(editor) {
    return jQuery(editor.getContainer())
        .find('.tox-toolbar:not(:first-child)')
        .map((i, toolbar) => jQuery(toolbar));
}

function updateContentAreaIframeHeight(editor, heightOffset) {
    if (heightOffset === 0 || tinymce.Env.iOS) {
        return;
    }

    let iframe = editor.getContentAreaContainer().firstChild;
    tinymce.DOM.setStyle(iframe, 'height', iframe.clientHeight + heightOffset);
}

function hideToolbars(editor, button, shouldUpdateContentAreaIframeHeight = true) {
    let heightOffset = 0;

    getToolbars(editor).each((i, toolbar) => {
        if (toolbar.is(':visible')) {
            toolbar.hide();
            heightOffset += toolbarHeight;
        }
    });

    if (shouldUpdateContentAreaIframeHeight) {
        updateContentAreaIframeHeight(editor, heightOffset);
    }

    tinymce.util.LocalStorage.setItem('hidetb', '1');
    button.setActive(false);
}

function showToolbars(editor, button, shouldUpdateContentAreaIframeHeight = true) {
    let heightOffset = 0;

    getToolbars(editor).each((i, toolbar) => {
        if (toolbar.is(':hidden')) {
            toolbar.show();
            heightOffset -= toolbarHeight;
        }
    });

    if (shouldUpdateContentAreaIframeHeight) {
        updateContentAreaIframeHeight(editor, heightOffset);
    }

    tinymce.util.LocalStorage.removeItem('hidetb');
    button.setActive(true);
}

function updateToolbars(editor, button, shouldHideToolbars, shouldUpdateContentAreaIframeHeight = true) {
    if (shouldHideToolbars) {
        hideToolbars(editor, button, shouldUpdateContentAreaIframeHeight);
    } else {
        showToolbars(editor, button, shouldUpdateContentAreaIframeHeight);
    }
}

export default (editor) => {
    let toggleButton;

    editor.ui.registry.addToggleButton('gc_adv', {
        icon: 'image-options',
        tooltip: 'Toolbar toggle',
        onSetup(buttonApi) {
            toggleButton = buttonApi;
            updateToolbars(editor, toggleButton, tinymce.util.LocalStorage.getItem('hidetb'), false);
        },
        onAction: () => updateToolbars(editor, toggleButton, toggleButton.isActive()),
    });
};
