
<template>
    <div class="GC-ThemeEditor-AceEditor"></div>
</template>


<script>
const Editor = ace.require('ace/editor').Editor;
const EditSession = ace.require('ace/edit_session').EditSession;
const UndoManager = ace.require('ace/undomanager').UndoManager;

var emmet, modelist;
const sessionStore = new Map();


// prevent emmet from being enabled or disabled
ace.config.loadModule('ace/config', function(config) {
    config.defineOptions(Editor.prototype, "editor", {
        enableEmmet: {
            set: function() {},
            value: true
        }
    });
});

ace.config.loadModule('ace/ext/emmet', function(aceExtEmmet) {
    emmet = aceExtEmmet;
});

ace.config.loadModule('ace/ext/language_tools', function(languageTools) {
    languageTools.addCompleter({
        getCompletions(editor, session, pos, prefix, callback) {
            //console.log('getCompletions', pos, prefix);
            callback(null, [] /* [{ meta: "givecloud", value }] */);
        }
    });
});

ace.config.loadModule('ace/ext/modelist', function(aceExtModelist) {
    modelist = aceExtModelist;
});

export default {
    mounted() {
        this.initEditor();
    },
    destroyed() {
        this.destroyEditor();
    },
    computed: {
        activeTab() {
            return this.$store.getters.activeTab;
        },
        openTabs() {
            let openTabs = this.$store.getters.openTabs;
            sessionStore.forEach((session, id) => {
                if (!this.openTabs.find(tab => tab.id === id)) {
                    sessionStore.delete(id);
                }
            });
            return openTabs;
        }
    },
    watch: {
        activeTab: function(newValue, oldValue) {
            if (newValue !== oldValue) {
                this.openTab(newValue);
            }
        }
    },
    methods: {
        initEditor() {
            this.editor = ace.edit(this.$el, {
                enableEmmet: true,
                enableLiveAutocompletion: true,
                fontFamily: '"Operator Mono", "Source Code Pro", Menlo, Consolas, Lucida Console, monospace',
                fontSize: 16,
                highlightActiveLine: true,
                mode: 'ace/mode/html',
                scrollPastEnd: true,
                showPrintMargin: false,
                theme: 'ace/theme/tomorrow_night',
                wrap: false
            });

            this.editor.$blockScrolling = Infinity;
            this.editor.commands.addCommand({
                    name: 'save',
                    bindKey: {
                        mac: 'Cmd-S', win: 'Ctrl-S'
                    },
                    exec: () => this.saveTab()
            });

            this.openTab(this.activeTab);
        },

        destroyEditor() {
            if (this.editor) {
                this.editor.destroy();
            }
            sessionStore.clear();
        },

        openTab(tab) {
            let session = sessionStore.get(tab.id);
            if (!session) {
                let mode = this.getMode(tab.key);
                session = new EditSession(tab.value || '', mode);
                session.setUndoManager(new UndoManager());
                sessionStore.set(tab.id, session);
                if (/css|less|liquid|sass|scss|stylus|html/.test(mode)) {
                    this.editor.keyBinding.addKeyboardHandler(ace.require('ace/ext/emmet').commands);
                } else {
                    this.editor.keyBinding.removeKeyboardHandler(ace.require('ace/ext/emmet').commands);
                }
            }
            window.activeSession = session;
            this.editor.setSession(session);
            this.editor.focus();
        },

        closeTab(tab) {
            let session = sessionStore.get(tab.id);
            if (session === this.editor.session) {
                this.editor.setSession(null);
            }
            sessionStore.delete(tab.id);
        },

        getMode(path) {
            if (path == null) return 'ace/mode/html';
            return modelist.getModeForPath(path).mode;
        },

        saveTab() {
            this.$store.dispatch('saveTab', {
                id: this.activeTab.id,
                theme_id: this.activeTab.theme_id,
                value: this.getCode(),
            });
        },

        getCode() {
            return this.editor.getValue();
        },

        resizeEditor() {
            this.editor.resize();
        }
    }
};
</script>


<style lang="scss">
.GC-ThemeEditor-AceEditor {
    position: absolute;
    top: 4px;
    bottom: 0;
    left: 0;
    right: 0;
    overflow: hidden;
}
</style>
