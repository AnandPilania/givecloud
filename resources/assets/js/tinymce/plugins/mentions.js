
import tinymce from 'tinymce';

function fetchMatchingMentions(editor, pattern) {
    const results = Object.keys(editor.settings.mentions())
        .filter((mention) => {
            return String(mention).includes(pattern);
        }).map((mention) => {
            return {
                text: mention,
                value: mention,
            };
        });

    return new tinymce.util.Promise((resolve) => resolve(results));
}

function onAction(editor, autocompleteApi, rng, value) {
    editor.selection.setRng(rng);
    editor.insertContent(value);
    autocompleteApi.hide();
}

export default (editor) => {
    editor.ui.registry.addAutocompleter('mentions', {
        ch: '[',
        minChars: 0,
        columns: 1,
        matches: () => true,
        fetch: (pattern) => fetchMatchingMentions(editor, pattern),
        onAction: (autocompleteApi, rng, value) => onAction(editor, autocompleteApi, rng, value),
    });
};
