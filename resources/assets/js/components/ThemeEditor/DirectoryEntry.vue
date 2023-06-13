
<template>
    <div class="GC-DirectoryEntry">
        <div class="GC-DirectoryEntry__EntryContainer" @click="opened = !opened">
            <entry :entry="entry" :depth="depth" :opened="opened"></entry>
        </div>
        <transition v-on:enter="slideDown" v-on:leave="slideUp">
            <div v-if="opened" class="GC-DirectoryEntry__Opener">
                <template v-for="child in entry.children">
                    <template v-if="child.children">
                        <directory-entry :entry="child" :depth="depth+1"></directory-entry>
                    </template>
                    <template v-else>
                        <entry :entry="child" :depth="depth+1"></entry>
                    </template>
                </template>
            </div>
        </transition>
    </div>
</template>


<script>
import Entry from './Entry';

export default {
    name: 'directory-entry',
    components: {
        entry: Entry,
    },
    props: {
        entry: {
            type: Object,
            required: true,
        },
        depth: {
            type: Number,
            default: 1,
        },
    },
    data() {
        return {
            opened: false,
        };
    },
    methods: {
        slideUp(el, done) {
            jQuery(el).velocity('slideUp', { duration: 300 });
        },
        slideDown(el, done) {
            jQuery(el).velocity('slideDown', { duration: 300 });
        },
    }
};
</script>
