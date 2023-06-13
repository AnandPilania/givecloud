
import { Base64 } from 'js-base64';
import tinymce from 'tinymce';

const preservableTags = [
    'script',
    'style',
];

function replacePreservableTagsWithPlaceholders(event) {
    if (! event.content) {
        return;
    }

    const regex = new RegExp(`<(${preservableTags.join('|')})[^>]*>[\\s\\S]*?</\\1>`, 'g');

    // in order to prevent TinyMCE from mangling tags we replace all the tags we want preserved
    // placeholders that will be substituted back with the preserved tags when processing
    event.content = event.content.replace(regex, (match, preservableTag) => {
        return `
            <div>
                <img
                    class="mce-object"
                    width="20"
                    height="20"
                    src="${tinymce.Env.transparentSrc}"
                    data-gc-preserve="${Base64.encode(match)}"
                    data-mce-resize="false"
                    data-mce-placeholder="1"
                    alt="&lt;${preservableTag}&gt;"
                    title="&lt;${preservableTag}&gt;"
                />
            </div>
        `;
    });
}

function replacePlaceholdersWithPreservedTags(event) {
    if (! event.content) {
        return;
    }

    event.content = event.content.replace(/<div><img[^>]+><\/div>/g, (content) => {
        const match = content.match(/ data-gc-preserve="([^"]+)"/);
        return match ? Base64.decode(match[1]) : content;
    });
}

export default (editor) => {
    editor.on('BeforeSetContent', replacePreservableTagsWithPlaceholders);
    editor.on('PostProcess', replacePlaceholdersWithPreservedTags);
};
