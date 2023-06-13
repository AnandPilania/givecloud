
import axios from 'axios';
import Vue from 'vue';

function errorHandler(error) {
    return err => {
        try {
            error = err.response.data.message || err || error;
        } catch(e) {
            // no nothing
        }
        Vue.prototype.$Progress.fail();
        Vue.toasted.error(error);
        return Promise.reject(error);
    }
}

function getAsset({ id, theme_id }) {
    return axios.get(`/jpanel/themes/${theme_id}/editor/assets/${id}.json`)
        .catch(errorHandler('Unable to retrieve asset. Please try again.'));
}

function saveAsset({ id, theme_id, value }) {
    return axios.post(`/jpanel/themes/${theme_id}/editor/assets/${id}`, { value })
        .catch(errorHandler('Unable to save asset. Please try again.'));
}


export default {
    getAsset,
    saveAsset,
};
