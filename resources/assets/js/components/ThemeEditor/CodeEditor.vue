
<template>
    <div class="GC-ThemeEditor-Editor" v-if="activeTab">
        <template v-if="isBinary">
            <template v-if="isImage">
                Image
            </template>
            <template v-else>
                Binary
            </template>
        </template>
        <template v-else>
                <ace-editor ref="aceEditor"></ace-editor>
        </template>
    </div>
</template>


<script>
import AceEditor from './AceEditor';

export default {
    components: {
        'ace-editor': AceEditor,
    },
    computed: {
        activeTab() {
            return this.$store.getters.activeTab;
        },
        isBinary() {
            return this.isImage;
        },
        isImage() {
            return this.activeTab.name.match(/\.(gif|png|jpeg|jpg|tiff|bmp|ico|svg)$/i);
        }
    },
    methods: {
        resizeEditor() {
            if (this.$refs.aceEditor) {
                this.$refs.aceEditor.resizeEditor();
            }
        },
        getCode() {
            if (this.$refs.aceEditor) {
                return this.$refs.aceEditor.getCode();
            }
        }
    }
};
</script>


<style lang="scss">
.GC-ThemeEditor-Editor {
    position: relative;
    flex: 1;
    overflow: hidden;
    color: #fff;
}
</style>
