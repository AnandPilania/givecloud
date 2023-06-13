/* global process */

import 'es5-shim';
import 'es6-promise/auto';
import 'sugar/polyfills/es6';
import 'url-search-params-polyfill';

import _ from 'lodash';
import ace from 'ace-builds/src-noconflict/ace';
import axios from 'axios';
import { Chart, BarController, BarElement, CategoryScale, LinearScale } from 'chart.js';
import emmet from 'emmet-core/emmet';
import flatpickr from 'flatpickr';
import jQuery from 'jquery';
import * as Ladda from 'ladda';
import metisMenu from 'metismenu';
import moment from 'moment';
import Odometer from 'odometer';
import Raphael from 'raphael';
import Selectize from 'selectize';
import Sugar from 'sugar';
import tinymce from 'tinymce';
import toastr from 'toastr';
import Velocity from 'velocity-animate';
import Vue from 'vue';
import VeeValidate from 'vee-validate';
import VueLadda from 'vue-ladda';
import VueMultiselect from 'vue-multiselect';
import VueProgressBar from 'vue-progressbar';
import VueSelectize from 'vue2-selectize';
import VueTheMask from 'vue-the-mask';
import VueToasted from 'vue-toasted/dist/vue-toasted';
import VueToggleButton from 'vue-js-toggle-button';
import Vuex from 'vuex';

window.$ = jQuery;
window._ = _;
window.ace = ace;
window.axios = axios;
window.Chart = Chart;
window.emmet = emmet;
window.flatpickr = flatpickr;
window.Ladda = Ladda;
window.metisMenu = metisMenu;
window.moment = moment;
window.Odometer = Odometer;
window.Raphael = Raphael;
window.Selectize = Selectize;
window.Sugar = Sugar;
window.tinymce = tinymce;
window.toastr = toastr;
window.Velocity = Velocity;
window.Vue = Vue;
window.Vuex = Vuex;

window['j'+'Query'] = jQuery; // foolishness required to bypass webpack.ProvidePlugin

import 'ace-builds/src-noconflict/ext-emmet';
import 'ace-builds/src-noconflict/ext-language_tools';
import 'ace-builds/src-noconflict/mode-css';
import 'ace-builds/src-noconflict/mode-html';
import 'ace-builds/src-noconflict/mode-javascript';
import 'ace-builds/src-noconflict/mode-scss';
import 'ace-builds/src-noconflict/mode-twig';
import 'ace-builds/src-noconflict/theme-tomorrow_night';
import 'alpinejs';
import 'bootstrap';
import 'bootstrap-datepicker';
import 'bootstrap-select';
import 'bootstrap-slider';
import 'bootstrap-switch';
import 'bootstrap-touchspin';
import 'bootstrapValidator';
import 'datatables.net';
import 'datatables.net-bs';
import 'datatables.net-select';
import 'file-icons-js';
import 'form-serializer';
import 'jquery-ui/ui/widgets/sortable';
import 'jasny-bootstrap/dist/js/jasny-bootstrap';
import 'jquery-datatables-checkboxes';
import 'jquery-form';
import 'jquery-latitude-longitude-picker-gmaps/js/jquery-gmaps-latlon-picker';
import 'jquery-match-height';
import 'jquery-minicolors';
import 'jquery-sparkline';
import 'morris.js/morris.js';
import 'tinymce/icons/default';
import 'tinymce/jquery.tinymce';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/directionality';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/hr';
import 'tinymce/plugins/image';
import 'tinymce/plugins/imagetools';
import 'tinymce/plugins/importcss';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/nonbreaking';
import 'tinymce/plugins/noneditable';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/print';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/table';
import 'tinymce/plugins/template';
import 'tinymce/plugins/visualchars';
import 'velocity-animate/velocity.ui';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// register the CSRF Token as a common header with Axios and jQuery
let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    jQuery.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': token.content }
    });

    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

ace.config.set('basePath', 'https://cdn.givecloud.co/npm/ace-builds@1.4.12/src-min-noconflict');

toastr.options.closeButton = true;
toastr.options.progressBar = true;
toastr.options.timeOut = 6000;
toastr.options.extendedTimeOut = 2500;

Vue.config.productionTip = !(process.env.NODE_ENV === 'production');

Vue.use(VueTheMask);

Vue.use(VueToasted, {
  theme: 'primary',
  position: 'top-right',
  duration : 5000
});

Vue.use(VeeValidate, {
  classes: true,
  strict: false,
  validity: true
});

if (window.Givecloud && window.Givecloud.CardholderData) {
  VeeValidate.Validator.extend('credit_card', window.Givecloud.CardholderData.validNumber);
  VeeValidate.Validator.extend('expiration_date', window.Givecloud.CardholderData.validExpirationDate);

  VeeValidate.Validator.extend('cvv', function(value, args) {
    return window.Givecloud.CardholderData.validCvv(value, args[0]);
  });
}

Vue.use(VueProgressBar, {
    color: '#a3eaa9',
    failedColor: '#8b2c1d',
    thickness: '3px',
    transition: {
        speed: '0.2s',
        opacity: '0.6s',
        termination: 1000
    },
});

Vue.use(VueToggleButton);
Vue.use(Vuex);

Vue.component('vue-ladda', VueLadda);
Vue.component('vue-multiselect', VueMultiselect);
Vue.component('vue-selectize', VueSelectize);

Chart.register(BarController, BarElement, CategoryScale, LinearScale);

// include vendor stylesheets here as CSS files can't be include
// via @import in newer versions of node-sass
import 'animate.css/animate.css';
import 'font-awesome/css/font-awesome.css';
import '../sass/vendor/_bootstrap.scss';
import 'toastr/toastr.scss';
import 'jquery-minicolors/jquery.minicolors.css';
import 'metismenu/dist/metisMenu.css';
import 'morris.js/morris.css';
import 'odometer/themes/odometer-theme-default.css';
import 'selectize/dist/css/selectize.bootstrap3.css';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
import 'bootstrap-select/dist/css/bootstrap-select.css';
import 'bootstrap-slider/dist/css/bootstrap-slider.css';
import 'bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.css';
import 'bootstrapValidator/dist/css/bootstrapValidator.css';
import 'jasny-bootstrap/dist/css/jasny-bootstrap.css';
import 'jquery-datatables-checkboxes/css/dataTables.checkboxes.css';
import 'file-icons-js/css/style.css';
import 'flatpickr/dist/themes/airbnb.css';
import 'ladda/dist/ladda-themeless.min.css';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import 'sb-admin-2-sass/dist/css/sb-admin-2.css';
