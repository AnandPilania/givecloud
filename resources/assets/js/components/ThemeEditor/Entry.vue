
<template>
    <div class="GC-Entry" :class="{ 'GC-Entry--active': active }" @click="open">
        <div class="GC-Entry__EntryContainer" :class="{ 'GC-Entry__EntryContainer--directory': entry.children }" :style="style">
            <div class="GC-Entry__Icon">
                <i :class="[icon]"></i>
            </div>
            <div class="GC-Entry__Name">{{ entry.name }}</div>
        </div>
    </div>
</template>


<script>
export default {
    props: {
        entry: {
            type: Object,
            required: true,
        },
        depth: {
            type: Number,
            default: 1,
        },
        opened: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        active: function() {
            if (this.$store.getters.activeTab) {
                return this.$store.getters.activeTab.id === this.entry.id;
            }
        },
        style() {
            return {
                paddingLeft: `calc(${this.depth}rem - 2px)`
            };
        },
        icon() {
            if (this.entry.children) {
                return this.opened ? 'fa fa-folder-open' : 'fa fa-folder';
            }
            return 'icon text-icon';
            //return 'fa fa-file-o';
            //return FileIcons.getClass(this.entry.name);
        },
    },
    methods: {
        open() {
            if (this.entry.key) {
                this.$store.dispatch('openTab', this.entry);
            }
        }
    },
};
</script>


<style lang="scss">
.GC-Entry {
    &--active {
        background: #c7cbd1;
    }
    &__EntryContainer {
        position: relative;
        display: flex;
        padding: 0.4rem;
        padding-left: calc(1rem - 2px);
        padding-right: 3rem;
        font-size: 14px;
        font-weight: 400;
        text-decoration: none;
        border-left: 2px solid transparent;
        cursor: pointer;
        user-select: none;
        color: #313131;
        &:hover {
            background-color: #dbdde0;
            border-color: rgb(38,110,161);
        }
        &--directory {
            font-weight: bold;
        }
    }
    &__Icon {
        display: inline-block;
        width: 15px;
        margin-right: 6px;
        vertical-align: middle;
        color: #999a9c;
    }
}
</style>
