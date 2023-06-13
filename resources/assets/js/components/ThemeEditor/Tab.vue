
<template>
    <div class="GC-ThemeEditor-Tab" :class="{ 'GC-ThemeEditor-Tab--active': active }" @click="activate">
        <div class="GC-ThemeEditor-Tab__Name">{{ file.name }}</div>
        <div class="GC-ThemeEditor-Tab__Close">
            <i class="fa fa-times-circle" @click.stop="close"></i>
        </div>
    </div>
</template>


<script>
export default {
    props: {
        file: {
            type: Object,
            required: true,
        },
    },
    computed: {
        active: function() {
            if (this.$store.getters.activeTab) {
                return this.$store.getters.activeTab.id === this.file.id;
            }
        },
    },
    methods: {
        activate() {
            this.$store.dispatch('activateTab', this.file);
        },
        close() {
            this.$store.dispatch('closeTab', this.file);
        },
    }
};
</script>


<style lang="scss">
.GC-ThemeEditor-Tab {
    background: #373839;
    position: relative;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 0 12px;
    line-height: 1;
    font-size: 13px;
    cursor: pointer;
    border-bottom: 1px solid #373839;
    &--active {
        background-color: #191c1c;
        border-color: #7799b4;
        color: #fff;
    }
    &__Close {
        display: inline-block;
        margin-left: 8px;
    }
}
</style>
