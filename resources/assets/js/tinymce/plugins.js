
import tinymce from 'tinymce';
import setupBackgroundPlugin from './plugins/background';
import setupEmbeddablePlugin from './plugins/embeddable';
import setupMentionsPlugin from './plugins/mentions';
import setupNotificationsPlugin from './plugins/notifications';
import setupPreserveTagsPlugin from './plugins/preserveTags';
import setupToolbarTogglePlugin from './plugins/toolbarToggle';

tinymce.PluginManager.add('givecloud', function(editor) {
    editor.contentStyles.push('body {padding: 10px; visibility: hidden;}');

    editor.on('init', () => {
        editor.dom.$('body').css({ visibility: 'visible' });
    });

    setupBackgroundPlugin(editor);
    setupEmbeddablePlugin(editor);
    setupMentionsPlugin(editor);
    setupNotificationsPlugin(editor);
    setupPreserveTagsPlugin(editor);
    setupToolbarTogglePlugin(editor);
});
