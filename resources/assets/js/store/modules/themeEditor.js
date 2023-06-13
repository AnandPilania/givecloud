
import api from '@app/api/themeEditor';
import Vue from 'vue';

const state = {
    assets: window['theme_editor_assets'] || {},
    loading: false,
    openTabs: [],
    activeTab: null,
};

const getters = {
    assets: state => state.assets,
    loading: state => state.loading,
    openTabs: state => state.openTabs,
    activeTab: state => {
        return state.openTabs.find(tab => state.activeTab == tab.id);
    },
};

const actions = {
    openTab ({ commit, state }, file) {
        const tab = state.openTabs.find(tab => file.id == tab.id);
        if (tab) {
            commit('ACTIVATE_TAB', file);
        } else {
            commit('LOADING', true);
            api.getAsset(file).then(res => {
                this._vm.$Progress.finish();
                commit('LOADING', false);
                commit('OPEN_TAB', res.data);
            }).catch(() => {
                commit('LOADING', false);
            });
        }
    },
    activateTab ({ commit }, file) {
        commit('ACTIVATE_TAB', file);
    },
    closeTab ({ commit }, file) {
        commit('CLOSE_TAB', file);
    },
    saveTab ({ commit }, file) {
        commit('LOADING', true);
        api.saveAsset(file).then(() => {
            commit('LOADING', false);
            Vue.toasted.success('Asset saved.');
        }).catch(() => {
            commit('LOADING', false);
        })
    },
}

const mutations = {
    LOADING (state, loading) {
        state.loading = loading;
        if (loading) {
            this._vm.$Progress.start();
        } else {
            this._vm.$Progress.finish();
        }
    },
    OPEN_TAB (state, file) {
        state.openTabs.push(file);
        state.activeTab = file.id;
    },
    ACTIVATE_TAB (state, file) {
        state.activeTab = file.id;
    },
    CLOSE_TAB (state, file) {
        let index;
        state.openTabs = state.openTabs.filter((tab, i) => {
            if (tab.id != file.id) {
                return true;
            }
            index = i;
        });
        if (state.activeTab == file.id) {
            if (state.openTabs.length - 1 < index) {
                index = state.openTabs.length - 1;
            }
            if (state.openTabs[index]) {
                state.activeTab = state.openTabs[index].id;
            } else {
                state.activeTab = null;
            }
        }
    },
};


export default {
    state,
    getters,
    actions,
    mutations,
};
