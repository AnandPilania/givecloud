
import $ from 'jquery';
import Vue from 'vue';
import './bootstrap';
import './jquery';
import './jpanel';
import './pos';

window.Sugar.extend();

// bootstrap the theme editor instance only when present
import store from './store';
import MessengerConsole from './components/MessengerConsole';
import ThemeEditor from './components/ThemeEditor';
import WebPosPaymentModal from './components/WebPos/PaymentModal';

Vue.component('MessengerConsole', MessengerConsole);
Vue.component('ThemeEditor', ThemeEditor);
Vue.component('WebPosPaymentModal', WebPosPaymentModal);

if (document.getElementById('theme-editor-app')) {
  window.themeEditor = new Vue({
    el: '#theme-editor-app',
    store,
  });
}

window.toggleNavSubMenu = function (e) {
    var container = $(e).parent();
    var childrenUl = container.find('ul');
    var shownIcon = container.find('.menuToggleIconShown');
    var hiddenIcon = container.find('.menuToggleIconHidden');
    var show = childrenUl.hasClass('subMenuIsHidden') ? true : false;
    if (show) {
        childrenUl.slideDown().removeClass('non-bootstrap-hidden subMenuIsHidden');
        shownIcon.removeClass('non-bootstrap-hidden');
        hiddenIcon.addClass('non-bootstrap-hidden');
    } else {
        childrenUl.slideUp().addClass('subMenuIsHidden');
        shownIcon.addClass('non-bootstrap-hidden');
        hiddenIcon.removeClass('non-bootstrap-hidden');
    }
}

window.closeSidebar = function () {
    $('#mobileMenu').addClass('non-bootstrap-hidden');
}

window.openSidebar = function () {
    $('#mobileMenu').removeClass('non-bootstrap-hidden');
}

import sponseePhotoImporter from './apps/sponseePhotoImporter';
if (document.getElementById('sponsee-photo-importer-app')) {
  window.sponseePhotoImporterApp = sponseePhotoImporter('#sponsee-photo-importer-app');
}

import twoFactorAuthenticationProfile from './apps/twoFactorAuthenticationProfile';
if (document.getElementById('two-factor-authentication-profile-app')) {
  window.twoFactorAuthenticationProfileApp = twoFactorAuthenticationProfile('#two-factor-authentication-profile-app');
}

import virtualEventsEditScreen from './apps/virtualEventsEditScreen';
if (document.getElementById('virtual-events-edit-screen')) {
  window.virtualEventsEditScreen = virtualEventsEditScreen('#virtual-events-edit-screen');
}

import commentsApp from './apps/comments';
if (document.getElementById('comments-app')) {
    window.commentsApp = commentsApp('#comments-app');
}

// setup settings for payment providers
import settingsPaymentProvider from './apps/settingsPaymentProvider';
window.settingsPaymentProvider = settingsPaymentProvider;
