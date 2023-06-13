<template>
    <div class="relative" :class="editingModeClasses">
        <div class="relative flow-root">
            <div v-if="mediaEnabled || templatesEnabled" class="float-left gc-media-buttons">
                <button
                    v-if="mediaEnabled"
                    @click="showMediaLibrary()"
                    type="button"
                    class="button insert-media add_media"
                    data-editor="content"
                >
                    <span class="gc-media-buttons-icon"></span> Add Media
                </button>
                <button
                    v-if="templatesEnabled"
                    @click="showTemplateLibrary()"
                    type="button"
                    class="button insert-template add_template"
                    data-editor="content"
                >
                    <span class="gc-media-buttons-icon"></span> Add Template
                </button>
            </div>
            <div class="float-right">
                <button @click="switchToVisualEditing()" type="button" class="gc-switch-editor switch-tmce">
                    Visual
                </button>
                <button @click="switchToCodeEditing()" type="button" class="gc-switch-editor switch-html">Code</button>
            </div>
        </div>
        <div class="clear-both border border-solid gc-editor-container">
            <visual-editor
                ref="visualEditor"
                :height="height"
                :options="options"
                :value="content"
                @input="onInput($event)"
            ></visual-editor>
            <template v-if="codeEditorIsActive">
                <code-editor
                    ref="codeEditor"
                    :height="codeEditorHeight"
                    :value="content"
                    @input="onInput($event)"
                ></code-editor>
            </template>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import CodeEditor from '@app/components/HtmlEditor/Editors/CodeEditor';
import VisualEditor from '@app/components/HtmlEditor/Editors/VisualEditor';

export default {
    name: 'HtmlEditor',
    components: {
        'code-editor': CodeEditor,
        'visual-editor': VisualEditor,
    },
    props: {
        height: {
            default: 500,
            required: false,
            type: Number,
        },
        options: {
            default: {},
            required: false,
            type: Object,
        },
        value: {
            required: true,
            type: String,
        },
    },
    data() {
        return {
            content: '',
            editingMode: 'visual',
            codeEditorHeight: null,
        };
    },
    mounted() {
        this.content = this.value;
    },
    computed: {
        codeEditorIsActive() {
            return this.editingMode === 'code';
        },

        visualEditorIsActive() {
            return !this.codeEditorIsActive;
        },

        editingModeClasses() {
            return {
                'html-active': this.codeEditorIsActive,
                'tmce-active': this.visualEditorIsActive,
            };
        },

        mediaEnabled() {
            return true;
        },

        templatesEnabled() {
            return (
                window.Givecloud.settings.tinymce_templates && window.Givecloud.settings.tinymce_templates.length > 0
            );
        },
    },
    methods: {
        editor() {
            return this.codeEditorIsActive ? this.$refs.codeEditor : this.$refs.visualEditor;
        },

        mce() {
            return this.$refs.visualEditor.editor;
        },

        getValue() {
            return this.content;
        },

        setValue(value) {
            this.content = value;
            this.$emit('input', value);
        },

        insertContent(content, format = 'block') {
            this.editor().insertContent(content, format);
        },

        insertMediaContent(media) {
            if (media.is_image) {
                return this.insertContent(`<img src="${media.public_url}" alt="">`);
            }

            if (media.content_type === 'application/pdf') {
                return this.insertContent(`
                    <a href="${media.public_url}" target="_blank">
                        <img src="${media.thumbnail_url}">
                    </a>
                `);
            }

            return this.insertContent(media.public_url);
        },

        addMentions(mentions) {
            this.$refs.visualEditor.addMentions(mentions);
        },

        setMentions(mentions) {
            this.$refs.visualEditor.setMentions(mentions);
        },

        showMediaLibrary() {
            window.j.images.chooseOne(true, (media) => {
                if (media.length === 1) {
                    return this.insertMediaContent(media[0]);
                }

                var shortcode = '[gallery ids="' + _.map(media, 'id') + '"]';
                return this.insertContent(shortcode);
            });
        },

        showTemplateLibrary() {
            window.j.contentTemplates.chooseOne((url) => {
                if (url) {
                    axios.get(url).then((res) => this.insertContent(res.data, 'raw'));
                }
            });
        },

        switchToCodeEditing() {
            if (this.codeEditorIsActive) {
                return;
            }

            this.mce().fire('BeforeSwitchHTML', this);

            this.codeEditorHeight = jQuery(this.mce().getContainer()).height();
            this.editingMode = 'code';

            this.mce().hide();
            this.mce().fire('SwitchHTML', this);
        },

        switchToVisualEditing() {
            if (this.visualEditorIsActive) {
                return;
            }

            this.mce().fire('BeforeSwitchTMCE', this);

            this.editingMode = 'visual';

            this.mce().show();
            this.mce().focus();
            this.mce().fire('SwitchTMCE', this);
        },

        onInput(value) {
            this.setValue(value);
        },
    },
};
</script>

<style scoped>
@font-face {
    font-family: 'dashicons';
    src: url(~@icon/dashicons/dashicons.eot);
    src: url(~@icon/dashicons/dashicons.eot?#iefix) format('eot'), url(~@icon/dashicons/dashicons.woff2) format('woff2'),
        url(~@icon/dashicons/dashicons.ttf) format('truetype');
}

.gc-editor-container {
    border-color: #e5e5e5;
}

.gc-switch-editor {
    float: left;
    box-sizing: content-box;
    position: relative;
    top: 1px;
    background: #ebebeb;
    color: #666;
    cursor: pointer;
    font-size: 13px;
    line-height: 19px;
    height: 20px;
    margin: 5px 0 0 5px;
    padding: 3px 8px 4px;
    border: 1px solid #e5e5e5;
}

.gc-switch-editor:focus {
    box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, 0.8);
    outline: none;
    color: #23282d;
}

.gc-switch-editor:active,
.html-active .switch-html:focus,
.tmce-active .switch-tmce:focus {
    box-shadow: none;
}

.gc-switch-editor:active {
    background-color: #f5f5f5;
    box-shadow: none;
}

.tmce-active .quicktags-toolbar {
    display: none;
}

.tmce-active .switch-tmce,
.html-active .switch-html {
    background: #f5f5f5;
    color: #555;
    border-bottom-color: #f5f5f5;
}

.gc-media-buttons .button {
    display: inline-block;
    height: 28px;
    margin: 0 5px 4px 0;
    padding: 0 7px 1px 7px;
    background: #f7f7f7;
    font-size: 13px;
    line-height: 26px;
    color: #555;
    -webkit-appearance: none;
    text-decoration: none;
    border: 1px solid #ccc;
    border-radius: 3px;
    white-space: nowrap;
    box-sizing: border-box;
    box-shadow: 0 1px 0 #ccc;
    vertical-align: top;
    cursor: pointer;
}

.gc-media-buttons .button:active {
    position: relative;
    top: 1px;
    margin-top: -1px;
    margin-bottom: 1px;
}

.gc-media-buttons .insert-media {
    padding-left: 5px;
}

.gc-media-buttons a {
    text-decoration: none;
    color: #444;
    font-size: 12px;
}

.gc-media-buttons img {
    padding: 0 4px;
    vertical-align: middle;
}

.gc-media-buttons span.gc-media-buttons-icon {
    display: inline-block;
    width: 18px;
    height: 18px;
    vertical-align: text-top;
    margin: 0 2px;
}

.gc-media-buttons .add_media span.gc-media-buttons-icon {
    background: none;
}

.gc-media-buttons .add_media span.gc-media-buttons-icon:before {
    font: normal 18px/1 dashicons;
    speak: none;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.gc-media-buttons .add_media span.gc-media-buttons-icon:before {
    content: '\f104';
}

.gc-media-buttons .add_template span.gc-media-buttons-icon {
    background: none;
}

.gc-media-buttons .add_template span.gc-media-buttons-icon:before {
    font: normal 18px/1 dashicons;
    speak: none;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    content: '\f116';
}
</style>
