
import jQuery from 'jquery';
import Vue from 'vue';
import HtmlEditor from '@app/components/HtmlEditor/HtmlEditor';

export default function(selector, options = {}) {
    let $el = jQuery(selector), $input = null, height = 500;

    if ($el.data('primary')) {
        height = Math.max(height, jQuery(window).height() * 0.75);
    }

    if ($el.is('input,textarea')) {
        $input = $el.hide();
        $el = jQuery('<div/>').insertAfter($el);
    } else {
        $el.empty();
    }

    return new Vue({
        el: $el.get(0),
        template: '<html-editor ref="editor" v-model="content" :height="height" :options="options"></html-editor>',
        components: {
            'html-editor': HtmlEditor,
        },
        data: {
            content: $input ? $input.val() : '',
            options,
            height
        },
        watch: {
            content(newValue) {
                $input && $input.val(newValue);
            }
        },
        destroyed() {
            if ($input) {
                $input.show();
            }
        },
        methods: {
            addMentions(mentions) {
                return this.$refs.editor.addMentions(mentions);
            }
        }
    });
}
