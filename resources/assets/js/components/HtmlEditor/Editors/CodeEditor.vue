<template>
    <div ref="container" :style="containerStyles"></div>
</template>

<script>
import ace from 'ace-builds/src-noconflict/ace';

const editorOptions = {
    displayIndentGuides: true,
    enableEmmet: true,
    enableLiveAutocompletion: true,
    fontFamily: '"Operator Mono", "Source Code Pro", Monaco, Menlo, Consolas, Lucida Console, monospace',
    fontSize: 14,
    highlightActiveLine: true,
    mode: 'ace/mode/html',
    scrollPastEnd: true,
    showPrintMargin: false,
    theme: 'ace/theme/chrome',
    useWorker: false,
    wrap: false,
};

export default {
    name: 'CodeEditor',
    props: {
        height: {
            default: 500,
            required: false,
            type: Number,
        },
        value: {
            required: true,
            type: String,
        },
    },
    data() {
        return {
            editor: null,
        };
    },
    mounted() {
        this.editor = ace.edit(this.$refs.container, editorOptions);
        this.editor.$blockScrolling = Infinity;
        this.setValue(this.value);

        this.editor.on('change', this.onChange.bind(this));
    },
    destroyed() {
        if (this.editor) {
            this.editor.destroy();
            this.editor = null;
        }
    },
    computed: {
        containerStyles() {
            return {
                height: `${this.height}px`,
            };
        },
    },
    methods: {
        getValue() {
            return this.editor.getValue();
        },

        setValue(value) {
            this.editor.setValue(value, 1);
        },

        insertContent(content) {
            this.editor.insert(content);
        },

        onChange() {
            this.$emit('input', this.getValue());
        },
    },
};
</script>
