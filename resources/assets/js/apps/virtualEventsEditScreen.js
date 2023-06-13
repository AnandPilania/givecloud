
import $ from 'jquery';
import axios from 'axios';
import jQuery from 'jquery';
import toastr from 'toastr';
import Vue from 'vue';

export default function (selector) {
    var data = jQuery(selector).data();

    return new Vue({
        el: selector,
        delimiters: ['${', '}'],
        props: ['field-name'],
        data: {
            baseUrl: data.baseUrl || '',
            hasStream: data.hasStream || false,
            streamUrl: data.streamUrl || '',
            streamKey: data.streamKey || '',
            slug: {
                inputValue: data.slug || '',
                savedValue: data.slug || '',
            },
            input: {
                id: data.id || '',
                name: data.name || '',
                logo: data.logo || '',
                background_image: data.backgroundImage || '',
                theme_style: data.themeStyle || '',
                theme_primary_color: data.themePrimaryColor || '',
                start_date: data.startDate || '',
                video_source: data.videoSource || '',
                video_id: data.videoId || '',
                chat_id: data.chatId || '',
                is_chat_enabled: data.isChatEnabled || false,
                is_amount_tally_enabled: data.isAmountTallyEnabled || false,
                is_honor_roll_enabled: data.isHonorRollEnabled || false,
                is_emoji_reaction_enabled: data.isEmojiReactionEnabled || false,
                is_celebration_enabled: data.isCelebrationEnabled || false,
                celebration_threshold: data.celebrationThreshold || 1000,
                prestream_message_line_1: data.prestreamMessageLineOne || '',
                prestream_message_line_2: data.prestreamMessageLineTwo || '',
                tab_one_label: data.tabOneLabel || '',
                tab_one_product_id: data.tabOneProductId || null,
                tab_two_label: data.tabTwoLabel || '',
                tab_two_product_id: data.tabTwoProductId || null,
                tab_three_label: data.tabThreeLabel || '',
                tab_three_product_id: data.tabThreeProductId || null,
            },
            formErrors: {}
        },
        methods: {
            onSubmit() {
                const $btn = $(this.$refs.submit_button);
                $btn.button('loading');

                axios.post('/jpanel/virtual-events/save', { ...this.input }).then((res) => {
                    if (res.status === 200) {
                        if (this.isNew) {
                            window.location = '/jpanel/virtual-events/' + res.data.id + '/edit';
                        } else {
                            window.location.reload();
                        }
                    }
                }).catch((err) => {
                    if (err.response.status === 422) {
                        this.formErrors = err.response.data.errors;
                        toastr['error']('Please fix the issues on the form');
                    }
                    $btn.button('reset');
                });
            },
            onDelete() {
                function deleteFn() {
                    const $btn = $(this.$refs.delete_button);
                    $btn.button('loading');
                    axios.post('/jpanel/virtual-events/' + this.input.id + '/destroy', {}).then(() => {
                        window.location = '/jpanel/virtual-events';
                    }).catch(() => {
                        toastr['error']('There was an error deleting the event');
                        this.button('reset');
                    });
                }
                $.confirm('Are you sure you want to delete this virtual event?', deleteFn.bind(this), 'danger', 'fa-trash');
            },
            onUpdateSlug() {
                const $btn = $(this.$refs.update_slug_button);
                $btn.button('loading');

                axios.post('/jpanel/virtual-events/' + this.input.id + '/update_slug', { slug: this.slug.inputValue }).then((res) => {
                    if (res.status === 200) {
                        $(this.$refs.slug_modal).modal('hide');
                        this.slug.savedValue = this.slug.inputValue;
                        toastr['success']('Public URL Updated');
                    }
                    $btn.button('reset');
                }).catch(() => {
                    toastr['error']('There was an error updating the Public URL');
                    $btn.button('reset');
                });
            },
            copyStreamUrl() {
                this.copyText(this.streamUrl, 'Stream URL successfully copied');
            },
            copyStreamKey() {
                this.copyText(this.streamKey, 'Stream Key successfully copied');
            },
            copyText(text, message) {
                var $temp = $('<input>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                toastr['success'](message);
            },
            specialFields() {
                jQuery(this.$refs.start_date)
                    .datepicker({ format: 'M d, yyyy', autoclose: true })
                    .on('changeDate', (e) => {
                        this.$nextTick(() => (this.input.start_date = e.currentTarget.value));
                    });
                jQuery(this.$refs.is_celebration_enabled).on('switchChange.bootstrapSwitch', (e, state) => {
                    this.$nextTick(() => (this.input.is_celebration_enabled = state));
                });
                jQuery(this.$refs.is_honor_roll_enabled).on('switchChange.bootstrapSwitch', (e, state) => {
                    this.$nextTick(() => (this.input.is_honor_roll_enabled = state));
                });
                jQuery(this.$refs.is_emoji_reaction_enabled).on('switchChange.bootstrapSwitch', (e, state) => {
                    this.$nextTick(() => (this.input.is_emoji_reaction_enabled = state));
                });
                jQuery(this.$refs.is_chat_enabled).on('switchChange.bootstrapSwitch', (e, state) => {
                    this.$nextTick(() => (this.input.is_chat_enabled = state));
                });
                jQuery(this.$refs.is_amount_tally_enabled).on('switchChange.bootstrapSwitch', (e, state) => {
                    this.$nextTick(() => (this.input.is_amount_tally_enabled = state));
                });
                jQuery(this.$refs.logo_image_browser_button).on('onImageChosen', (e, url) => {
                    if (this.input.logo !== url) {
                        this.$nextTick(() => (this.input.logo = url));
                    }
                });
                jQuery(this.$refs.background_image_browser_button).on('onImageChosen', (e, url) => {
                    if (this.input.background_image !== url) {
                        this.$nextTick(() => (this.input.background_image = url));
                    }
                });
                jQuery(this.$refs.tab_one_product_id).on('change', (e) => {
                    const value = e.currentTarget.value;
                    if (this.input.tab_one_product_id !== value) {
                        this.$nextTick(() => (this.input.tab_one_product_id = value));
                    }
                });
                jQuery(this.$refs.tab_two_product_id).on('change', (e) => {
                    const value = e.currentTarget.value;
                    if (this.input.tab_two_product_id !== value) {
                        this.$nextTick(() => (this.input.tab_two_product_id = value));
                    }
                });
                jQuery(this.$refs.tab_three_product_id).on('change', (e) => {
                    const value = e.currentTarget.value;
                    if (this.input.tab_three_product_id !== value) {
                        this.$nextTick(() => (this.input.tab_three_product_id = value));
                    }
                });

            },
        },
        watch: {
            ['slug.inputValue'](newValue) {
                let value = newValue.toLowerCase().replace(/[ ]+/ig, '-');
                value = value.replace(/[^a-zA-Z0-9-_]+/ig, '');
                if (newValue !== value) {
                    this.slug.inputValue = value;
                }
            },
            ['input.celebration_threshold'](newValue) {
                const value = newValue.replace(/[^0-9]+/ig, '');
                if (newValue !== value) {
                    this.input.celebration_threshold = value;
                }
            }
        },
        computed: {
            eventUrl() {
                return this.baseUrl + '/' + this.slug.savedValue;
            },
            isNew() {
                return this.input.id === '';
            },
            formattedPageTitle() {
                if (this.input.name) {
                    return (this.isNew ? 'Create' : 'Edit') + ' ' + this.input.name;
                }
                return (this.isNew ? 'Create' : 'Edit') + ' Virtual Event';
            },
            prestreamMessageLineOnePlaceHolder() {
                if (this.input.start_date) {
                    return `Eg, The event will start on ${this.input.start_date}`
                }
                return 'Eg, The event is starting soon';
            },
            prestreamMessageLineTwoPlaceHolder() {
                if (this.input.start_date) {
                    return 'Eg, at 7:30 PM EST'
                }
                return ''
            }
        },
        mounted: function () {
            this.specialFields();
        }
    });
}
