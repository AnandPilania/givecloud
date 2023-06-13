
<template>
    <div class="GC-ThemeEditor-Tabs" v-if="openTabs.length">
        <div class="GC-ThemeEditor-Tabs__Container">
            <open-tab v-for="file in openTabs" :key="file.id" :file="file"></open-tab>
        </div>
    </div>
</template>


<script>
import Tab from './Tab';

export default {
    components: {
        'open-tab': Tab,
    },
    computed: {
        openTabs: function() {
            return this.$store.getters.openTabs;
        },
    },
};
</script>


<style lang="scss">
.GC-ThemeEditor-Tabs {
    display: flex;
    height: 40px;
    flex: 0 0 40px;
    background: #646666;
    color: #929792;
    user-select: none;
    &__Container {
        display: flex;
        height: 40px;
        flex: 1 0 40px;
        overflow-x: auto;
        overflow-y: hidden;
    }
}
</style>
