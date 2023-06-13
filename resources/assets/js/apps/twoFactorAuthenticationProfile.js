
import axios from 'axios';
import jQuery from 'jquery';
import Vue from 'vue';

export default function(selector) {
    return new Vue({
        el: selector,
        delimiters: ['${', '}'],
        data: {
            enabled: jQuery(selector).data('enabled') || false,
            showingQrCode: false,
            twoFactorQrCodeSvg: null,
            showingRecoveryCodes: false,
            twoFactorRecoveryCodes: [],
        },
        methods: {
            async enable() {
                await axios.post('/jpanel/auth/user/two-factor-authentication');
                await this.showQrCode();
                await this.showRecoveryCodes();
                this.enabled = true;
            },
            disable() {
                return axios.delete('/jpanel/auth/user/two-factor-authentication').then(() => {
                    this.enabled = false;
                    this.showingQrCode = false;
                    this.showingRecoveryCodes = false;
                });
            },
            showQrCode() {
                return axios.get('/jpanel/auth/user/two-factor-qr-code').then(res => {
                    this.showingQrCode = true;
                    this.twoFactorQrCodeSvg = res.data.svg;
                });
            },
            showRecoveryCodes() {
                return axios.get('/jpanel/auth/user/two-factor-recovery-codes').then(res => {
                    this.showingRecoveryCodes = true;
                    this.twoFactorRecoveryCodes = res.data;
                });
            },
            regenerateRecoveryCodes() {
                return axios.post('/jpanel/auth/user/two-factor-recovery-codes').then(() => {
                    this.showRecoveryCodes();
                });
            },
        }
    });
}
