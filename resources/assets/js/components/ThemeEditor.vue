
<template>
    <div class="GC-ThemeEditor" :class="{ 'GC-ThemeEditor--zen': zenMode }">
        <div class="GC-ThemeEditor__Container">
            <vue-progress-bar class="GC-ThemeEditor__LoadingIndicator"></vue-progress-bar>
            <split-pane>
                <template slot="one">
                    <div class="GC-ThemeEditor__Workspace">
                        <div class="GC-ThemeEditor__Search">
                            <i class="fa fa-search"></i>
                            <input type="text" class="form-control" v-model="keywords" placeholder="Search files...">
                        </div>
                        <div class="GC-ThemeEditor__Files">
                            <directory-entry v-for="file in projectFiles" :key="file.name" :entry="file"></directory-entry>
                        </div>
                    </div>
                </template>
                <template slot="two">
                    <div class="GC-ThemeEditor__Content">
                        <div class="GC-ThemeEditor__ZenMode" @click="toggleZenMode" :title="zenMode ? 'Exit Zen Mode' : 'Enter Zen Mode'">
                            <i class="fa" :class="{ 'fa-compress': zenMode, 'fa-expand': !zenMode }" aria-hidden="true"></i>
                        </div>
                        <open-tabs></open-tabs>
                        <code-editor ref="codeEditor"></code-editor>
                        <div class="GC-ThemeEditor__Actions" v-if="activeTab">
                            <div class="pull-left">
                                <strong>{{ activeTab.name }}</strong>
                                <a v-if="!startReverting" @click="startReverting=true">Older versions</a>
                                <template v-else>
                                    <select>
                                        <option value="">Current</option>
                                    </select>
                                    or <a @click="startReverting=false">Cancel</a>
                                </template>
                                <template v-if="activeTab.content_type=='text/x-liquid'">
                                    | <a href="https://docs.givecloud.co/themes/liquid">Liquid variable reference</a>
                                </template>
                            </div>
                            <div class="pull-right">
                                <vue-ladda class="btn btn-success" data-spinner-size="20" :loading="savingTab" @click="saveTab()">Save</vue-ladda>
                            </div>
                        </div>
                    </div>
                </template>
            </split-pane>
        </div>
    </div>
</template>


<script>
import CodeEditor from './ThemeEditor/CodeEditor';
import DirectoryEntry from './ThemeEditor/DirectoryEntry';
import SplitPane from './Layout/SplitPane';
import Tabs from './ThemeEditor/Tabs';

export default {
    components: {
        'code-editor': CodeEditor,
        'directory-entry': DirectoryEntry,
        'split-pane': SplitPane,
        'open-tabs': Tabs,
    },
    computed: {
        savingTab() {
            return this.$store.getters.loading;
        },
        activeTab() {
            return this.$store.getters.activeTab;
        },
        projectFiles() {
            let files = {}
            _.forEach(this.$store.getters.assets, asset => {
                if (this.keywords && !Sugar.String.includes(asset.key.toLowerCase(), this.keywords.toLowerCase())) {
                    return;
                }
                let ref = files;
                let parts = _.trim(asset.key, '/').split('/');
                let count = parts.length - 1;
                for (var i=0; i < parts.length; i++) {
                    let part = parts[i];
                    if (i < count) {
                        if (_.isUndefined(ref[part])) {
                            ref[part] = {
                                name: (i === 0) ? Sugar.String.titleize(part) : part,
                                children: {},
                            };
                        }
                        if (count - i > 1) {
                            ref = ref[part].children;
                        } else {
                            ref = ref[part];
                        }
                    } else {
                        ref.children[part] = asset;
                    }
                }
            })
            return files;
        },
    },
    data() {
        return {
            zenMode: false,
            keywords: '',
            startReverting: false,
        }
    },
    methods: {
        toggleZenMode() {
            this.zenMode = !this.zenMode;
            this.$refs.codeEditor.resizeEditor();
            this.$nextTick(() => {
                jQuery('body').toggleClass('zen-mode');
            });
        },
        saveTab() {
            this.$store.dispatch('saveTab', {
                id: this.activeTab.id,
                theme_id: this.activeTab.theme_id,
                value: this.$refs.codeEditor.getCode(),
            });
        }
    },
};
</script>


<style lang="scss">
.GC-ThemeEditor {
    position: relative;
    display: flex;
    flex: 1;
    &--zen {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 10000;
        .GC-ThemeEditor__Container {
            margin-bottom: 0;
            > .GC-SplitPane {
                top: 0;
            }
        }
    }
    &__LoadingIndicator {
        position: relative !important;
    }
    &__Container {
        position: relative;
        display: flex;
        flex: 1;
        margin-bottom: 20px;
        > .GC-SplitPane {
            position: absolute;
            top: 20px;
            bottom: 0;
            left: 0;
            right: 0;
            > .GC-SplitPane__Container > .GC-SplitPane__Pane {
                position: relative;
                ::-webkit-scrollbar {
                    width: 8px;
                }
                ::-webkit-scrollbar-track {
                    background-color: #d8d9dc;
                }
                ::-webkit-scrollbar-thumb {
                    transition: all .3s ease;
                    border-color: transparent;
                    background-color: #aaaeb4;
                    z-index: 40;
                }
            }
        }
    }
    &__Workspace {
        position: absolute;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 100%;
        height: 100%;
        background-color: #ebedef;
        overflow-y: overlay;
        overflow-x: auto;
    }
    &__Search {
        position: relative;
        padding: 10px;
        .fa-search {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .form-control {
            padding-left: 30px;
            border-radius: 0;
        }
    }
    &__Files {
        flex: 1;
        padding-top: 5px;
        padding-bottom: 5px;
        user-select: none;
    }
    &__Content {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 100%;
        height: 100%;
        background-color: #1b1d1e;
    }
    &__ZenMode {
        position: absolute;
        top: 6px;
        right: 14px;
        color: #fff;
        font-size: 20px;
        opacity: .5;
        transition: opacity 0.3s ease;
        cursor: pointer;
        &:hover {
            opacity: 1;
        }
    }
    &__Actions {
        padding: 10px;
        background: #ebedef;
        font-size: 13px;
        line-height: 35px;
        vertical-align: middle;
        strong {
            display: inline-block;
            margin-right: 10px;
        }
        a {
            display: inline-block;
            text-decoration: underline;
            color: #1f262e;
            cursor: pointer;
        }
        select {
            display: inline-block;
            height: 28px;
            margin: 4px 0 0 0;
            padding: 4px 2px 2px 2px;
            max-width: 100%;
            font-size: 14px;
            line-height: 28px;
            color: #222;
            vertical-align: top;
            border: 1px solid #ccc;
            border-radius: 1px;
        }
        .btn {
            border-radius: 0;
        }
        button {
            font-size: 14px;
        }
    }
}
</style>
