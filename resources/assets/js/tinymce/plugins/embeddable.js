
import jQuery from 'jquery';

function matchUrlOnALineByItself(content) {
    const regex = /(^|<p>)(https?:\/\/[^\s"]+?)(<\/p>\s*|$)/gi;
    const match = regex.exec(content);

    if (match) {
        return {
            index: match.index + match[1].length,
            content: match[2],
        };
    }
}

function addMarkersForEmbeddableUrls(content) {
    let pieces = [], match;

    while (content && (match = matchUrlOnALineByItself(content))) {
        if (match.index) {
            pieces.push(content.substring(0, match.index));
        }

        pieces.push('<p data-oembed-marker="' + encodeURIComponent(match.content) + '">' + match.content + '</p>');
        content = content.slice(match.index + match.content.length);
    }

    if (content) {
        pieces.push(content);
    }

    return pieces.join('')
        .replace(/<p>\s*<p data-oembed-marker=/g, '<p data-oembed-marker=' )
        .replace(/<\/p>\s*<\/p>/g, '</p>');
}

function replaceMarkersWithHtmlForEmbed(editor) {
    const body = jQuery(editor.getBody());

    body.find('[data-oembed-marker]').each((index, node) => {
        const link = jQuery(node).data('oembed-marker');

        jQuery.get(`https://app.givecloud.co/services/embed.json?url=${link}`)
            .then(data => {
                if (data.html) {
                    editor.selection.setCursorLocation(node);
                    editor.$(node).remove();
                    editor.insertContent(`${data.html} <p></p>`);
                    setTimeout(() => editor.nodeChanged(), 250);
                }
            }).always(() => {
                editor.dom.setAttrib(node, 'data-oembed-marker', null);
            });
    });
}

export default (editor) => {
    editor.on('BeforeSwitchTMCE', (htmlEditor) => {
        htmlEditor.setValue(addMarkersForEmbeddableUrls(
            htmlEditor.getValue()
        ));
    });

    editor.on('PastePreProcess', (event) => {
        if (event.content) {
            let content = event.content.replace(/<[^>]+>/g, '').trim();

            // after stripping tags and trimming if all that's left is a URL
            // then replace the event content with just the URL
            if (/^https?:\/\/\S+$/i.test(content)) {
                event.content = content;
            }

            event.content = addMarkersForEmbeddableUrls(event.content);
        }
    });

    editor.on('SetContent', () => replaceMarkersWithHtmlForEmbed(editor));
};
