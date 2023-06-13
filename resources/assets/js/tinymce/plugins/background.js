
import tinymce from 'tinymce';

const backgroundDialogBody = {
    type: "panel",
    items: [
        {
            name: "color",
            type: "colorinput",
            label: "Color",
            //maxWidth: 150,
        },
        {
            name: "image",
            type: "urlinput",
            filetype: "image",
            label: "Image",
        },
        {
            name: "size",
            type: "listbox",
            label: "Scaling",
            //maxWidth: 275,
            items: [
                { value: "", text: "" },
                { value: "cover", text: "Cover (no stretching)" },
                {
                    value: "contain",
                    text: "Contain (no cropping or stretching)",
                },
            ],
        },
        {
            name: "position",
            type: "listbox",
            label: "Positioning",
            //maxWidth: 180,
            items: [
                { value: "", text: "" },
                { value: "center center", text: "Center" },
                { value: "top center", text: "Top" },
                { value: "bottom center", text: "Bottom" },
                { value: "top left", text: "Top Left" },
                { value: "top right", text: "Top Right" },
                { value: "center left", text: "Center Left" },
                { value: "center right", text: "Center Right" },
                { value: "bottom left", text: "Bottom Left" },
                { value: "bottom right", text: "Bottom Right" },
            ],
        },
        {
            name: "height",
            type: "listbox",
            label: "Height",
            //maxWidth: 180,
            items: [
                { value: "", text: "Auto" },
                { value: "10vh", text: "10%" },
                { value: "20vh", text: "20%" },
                { value: "30vh", text: "30%" },
                { value: "40vh", text: "40%" },
                { value: "50vh", text: "50%" },
                { value: "60vh", text: "60%" },
                { value: "70vh", text: "70%" },
                { value: "80vh", text: "80%" },
                { value: "90vh", text: "90%" },
            ],
        },
        {
            name: "blend",
            type: "listbox",
            label: "Color Blend",
            //maxWidth: 180,
            items: [
                { value: "", text: "" },
                { value: "darken", text: "Darken" },
                { value: "multiply", text: "Multiply" },
                { value: "lighten", text: "Lighten" },
                { value: "screen", text: "Screen" },
                { value: "saturation", text: "Saturation" },
                { value: "overlay", text: "Overlay" },
                { value: "color-dodge", text: "Color Dodge" },
                { value: "hard-light", text: "Hard Light" },
                { value: "soft-light", text: "Soft Light" },
                { value: "difference", text: "Difference" },
                { value: "exclusion", text: "Exclusion" },
                { value: "hue", text: "Hue" },
                { value: "color", text: "Color" },
                { value: "luminosity", text: "Luminosity" },
            ],
        },
        {
            name: "attachment",
            type: "listbox",
            label: "Parralax Effect",
            //maxWidth: 180,
            items: [
                { value: "scroll", text: "No" },
                { value: "fixed", text: "Yes" },
            ],
        },
        {
            name: "animation",
            type: "listbox",
            label: "Animation Effects",
            //maxWidth: 180,
            items: [
                { value: "", text: "None" },
                { value: "bg-animation-zoom", text: "Slow Zoom" },
                { value: "bg-animation-pan-h", text: "Slow Horizontal Pan" },
                { value: "bg-animation-pan-v", text: "Slow Vertical Pan" },
            ],
        },
    ],
};

function showBackgroundDialog(node, editor) {
    const data = {
        attachment: node[0].style.backgroundAttachment,
        color: tinymce.dom.DOMUtils.DOM.toHex(node[0].style.backgroundColor),
        image: node[0].style.backgroundImage.replace(/^url\(['"]?(.*?)['"]?\)/,'$1'),
        size: node[0].style.backgroundSize,
        position: node[0].style.backgroundPosition,
        blend: node[0].style.backgroundBlendMode,
        height: node[0].style.minHeight,
        animation: '',
    };

    if (tinymce.DOM.hasClass(node[0], 'bg-animation-zoom')) {
        data.animation = 'bg-animation-zoom';
    } else if (tinymce.DOM.hasClass(node[0], 'bg-animation-pan-h')) {
        data.animation = 'bg-animation-pan-h';
    } else if (tinymce.DOM.hasClass(node[0], 'bg-animation-pan-v')) {
        data.animation = 'bg-animation-pan-v';
    }

    editor.windowManager.open({
        title: 'Edit background',
        width: 500,
        height: 340,
        initialData: {
            attachment: data.attachment,
            color: data.color,
            image: {
                value: data.image,
            },
            size: data.image ? data.size : 'cover',
            position: data.image ? data.position : 'center',
            blend: data.blend,
            height: data.height,
            animation: data.animation,
        },
        body: backgroundDialogBody,
        buttons: [
            {
                type: 'cancel',
                name: 'closeButton',
                text: 'Cancel'
            },{
                type: 'submit',
                name: 'submitButton',
                text: 'Ok',
                primary: true
            }
        ],
        onSubmit: (dialogApi) => submitBackgroundDialog(node, dialogApi),
    });
}

function submitBackgroundDialog(node, dialogApi) {
    const data = dialogApi.getData();
    const updateStyles = tinymce.DOM.settings.update_styles;
    const updateClasses = tinymce.DOM.settings.update_classes;

    var styles = {
        'display': data.height ? 'flex' : '',
        'align-items': data.height ? 'center' : '',
        'justify-content': data.height ? 'center' : '',
        'background-color': data.color,
        'background-image': data.image?.value ? `url(${data.image.value})` : '',
        'background-size': data.image ? data.size : '',
        'background-position': data.image ? data.position : '',
        'background-attachment': data.image ? data.attachment : '',
        'background-blend-mode': data.blend,
        'min-height': data.height,
    };

    tinymce.DOM.settings.update_styles = true;

    tinymce.DOM.setStyles(node, styles);
    tinymce.DOM.removeClass(node, 'bg-animation-zoom');
    tinymce.DOM.removeClass(node, 'bg-animation-pan-h');
    tinymce.DOM.removeClass(node, 'bg-animation-pan-v');

    if (data.animation) {
        tinymce.DOM.addClass(node, data.animation);
    }

    tinymce.DOM.settings.update_styles  = updateStyles;
    tinymce.DOM.settings.update_classes = updateClasses;

    dialogApi.close();
}

export default (editor) => {
    let node = tinymce.dom.DomQuery();

    editor.ui.registry.addContextMenu('gc_background', {
        update(element) {
            if (element.matches('.mceBackground')) {
                node = editor.dom.$(element);
            } else {
                node = editor.dom.$(element).parents('.mceBackground');
            }

            return node.length ? 'gc_background' : '';
        }
    });

    editor.ui.registry.addMenuItem('gc_background', {
        type: 'item',
        text: 'Background',
        icon: 'edit-image',
        onAction: () => node && showBackgroundDialog(node, editor),
    });
};
