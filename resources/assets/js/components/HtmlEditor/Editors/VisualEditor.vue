<template>
    <textarea ref="textarea" :value="value" class="hidden" autocomplete="off"></textarea>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import tinymce from 'tinymce';
import '@app/tinymce/plugins';

const defaultColorMap = {
    Black: '000000',
    'Burnt orange': '993300',
    'Dark olive': '333300',
    'Dark green': '003300',
    'Dark azure': '003366',
    'Navy blue': '000080',
    Indigo: '333399',
    'Very dark gray': '333333',
    Maroon: '800000',
    Orange: 'FF6600',
    Olive: '808000',
    Green: '008000',
    Teal: '008080',
    Blue: '0000FF',
    'Grayish blue': '666699',
    Gray: '808080',
    Red: 'FF0000',
    Amber: 'FF9900',
    'Yellow green': '99CC00',
    'Sea green': '339966',
    Turquoise: '33CCCC',
    'Royal blue': '3366FF',
    Purple: '800080',
    'Medium gray': '999999',
    Magenta: 'FF00FF',
    Gold: 'FFCC00',
    Yellow: 'FFFF00',
    Lime: '00FF00',
    Aqua: '00FFFF',
    'Sky blue': '00CCFF',
    'Red violet': '993366',
    White: 'FFFFFF',
    Pink: 'FF99CC',
    Peach: 'FFCC99',
    'Light yellow': 'FFFF99',
    'Pale green': 'CCFFCC',
    'Pale cyan': 'CCFFFF',
    'Light sky blue': '99CCFF',
    Plum: 'CC99FF',
};

const defaultFontFormats = [
    'Andale Mono=andale mono,times',
    'Arial=arial,helvetica,sans-serif',
    'Arial Black=arial black,avant garde',
    'Book Antiqua=book antiqua,palatino',
    'Comic Sans MS=comic sans ms,sans-serif',
    'Courier New=courier new,courier',
    'Georgia=georgia,palatino',
    'Helvetica=helvetica',
    'Impact=impact,chicago',
    'Symbol=symbol',
    'Tahoma=tahoma,arial,helvetica,sans-serif',
    'Terminal=terminal,monaco',
    'Times New Roman=times new roman,times',
    'Trebuchet MS=trebuchet ms,geneva',
    'Verdana=verdana,geneva',
    'Webdings=webdings',
    'Wingdings=wingdings,zapf dingbat',
];

export default {
    name: 'VisualEditor',
    props: {
        height: {
            default: 500,
            required: false,
            type: Number,
        },
        options: {
            default: () => ({}),
            required: false,
            type: Object,
        },
        value: {
            required: true,
            type: String,
        },
    },
    data: () => ({
        editor: null,
        mentions: {},
    }),
    mounted() {
        tinymce.init(this.getOptions());
    },
    destroyed() {
        if (this.editor) {
            this.editor.destroy();
            this.editor = null;
        }
    },
    computed: {
        textareaStyles() {
            return {
                height: `${this.height}px`,
            };
        },
    },
    watch: {
        value(newValue, oldValue) {
            if (this.editor.isHidden() && newValue !== oldValue) {
                this.setValue(newValue);
            }
        },
    },
    methods: {
        getValue() {
            return this.editor.getContent();
        },

        setValue(value) {
            this.editor.setContent(value);
        },

        insertContent(content, format = 'block') {
            this.editor.insertContent(content, { format });
            this.editor.nodeChanged();
        },

        getMentions() {
            return this.mentions;
        },

        addMentions(mentions) {
            this.mentions = Object.assign(this.mentions, mentions);
        },

        setMentions(mentions) {
            this.mentions = mentions;
        },

        onChange() {
            this.$emit('input', this.getValue());
        },

        onFilePicked(callback, value, meta) {
            if (meta.filetype === 'image') {
                window.j.images.chooseOne(false, function (media) {
                    callback(media.public_url);
                });
            } else {
                console.error(`No support for ${meta.filetype} pickers`);
            }
        },

        onImageUpload(blobInfo, success, failure) {
            const data = new FormData();
            data.append('file', blobInfo.blob(), blobInfo.filename());

            axios
                .post(this.editor.settings.images_upload_url, data, {
                    withCredentials: this.editor.settings.images_upload_credentials,
                })
                .then(function (res) {
                    success(res.data.location);
                })
                .catch(function (err) {
                    failure('HTTP Error: ' + err.message);
                });
        },

        getOptions() {
            return Object.assign({}, this.getDefaultOptions(), this.options);
        },

        getDefaultOptions() {
            return {
                autoresize_on_init: false,
                base_url: 'https://cdn.givecloud.co/npm/tinymce@5.10.2',
                branding: false,
                color_map: this.getColorMap(),
                contextmenu: 'link gc_background image inserttable | cell row column deletetable',
                convert_urls: false,
                deprecation_warnings: false,
                document_base_url: window.location.origin,
                extended_valid_elements:
                    'video[id|class|controls|preload|width|height|autoplay|muted|loop|playsinline]',
                file_picker_callback: this.onFilePicked.bind(this),
                file_picker_types: 'image',
                formats: {
                    bold: { inline: 'strong' },
                },
                font_formats: this.getFontFormats(),
                height: this.height,
                image_advtab: true,
                images_reuse_filename: true,
                images_upload_credentials: true,
                images_upload_handler: this.onImageUpload.bind(this),
                images_upload_url: '/jpanel/api/v1/tinymce/imageupload',
                imagetools_proxy: '/jpanel/api/v1/tinymce/imagetools',
                imagetools_toolbar: 'rotateleft rotateright flipv fliph editimage imageoptions',
                menubar: false,
                mentions: this.getMentions.bind(this),
                max_height: 800,
                min_height: 500,
                paste_data_images: true,
                plugins:
                    'charmap fullscreen givecloud hr image imagetools ' +
                    'importcss link lists media noneditable paste table template',
                relative_urls: false,
                setup: this.setupEditor.bind(this),
                skin: 'oxide',
                statusbar: false,
                suffix: '.min',
                target: this.$refs.textarea,
                theme: 'silver',
                toolbar: [
                    'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright alignjustify link table gc_adv fullscreen',
                    'fontselect fontsizeselect strikethrough hr forecolor pastetext removeformat charmap outdent indent undo redo',
                ],
                visualblocks_default_state: false,
            };
        },

        getColorMap() {
            const themeColorMap = {};

            if (window.Givecloud.settings.default_color_1) {
                themeColorMap['Primary Theme Color'] = window.Givecloud.settings.default_color_1.replace(/\W/g, '');
            }

            if (window.Givecloud.settings.default_color_2) {
                themeColorMap['Secondary Theme Color'] = window.Givecloud.settings.default_color_2.replace(/\W/g, '');
            }

            if (window.Givecloud.settings.default_color_3) {
                themeColorMap['Alternate Theme Color'] = window.Givecloud.settings.default_color_3.replace(/\W/g, '');
            }

            const colorMap = [];

            _.forEach({ ...themeColorMap, ...defaultColorMap }, (color, name) => {
                colorMap.push(color);
                colorMap.push(name);
            });

            return colorMap;
        },

        getFontFormats() {
            const customFonts = window.Givecloud.settings.tinymce_fonts;
            const fontFormats = [
                ...defaultFontFormats,
                `${customFonts[0]}=${customFonts[0]},Helvetica Neue,Helvetica,Arial,sans-serif`,
                `${customFonts[1]}=${customFonts[1]},Georgia,serif`,
            ];

            return fontFormats.sort().join('; ');
        },

        setupEditor(editor) {
            this.editor = editor;
            this.setValue(this.value);

            this.editor.on('change', this.onChange.bind(this));
        },
    },
};
</script>
