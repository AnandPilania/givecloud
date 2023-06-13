
import axios from 'axios';
import jQuery from 'jquery';
import toastr from 'toastr';
import Vue from 'vue';

export default function(selector, data, updateProviderInputFilter) {
    updateProviderInputFilter = updateProviderInputFilter || ((input) => input);

    return new Vue({
        el: selector,
        delimiters: ['${', '}'],
        data,
        mounted() {
            jQuery(selector).find('[data-toggle="tooltip"]').tooltip();
        },
        computed: {
            //
        },
        methods: {
            updateEnabled({ value }) {
                const data = {
                    provider: this.provider,
                    enabled: value
                };
                axios.post('/jpanel/settings/payment', data)
                    .then(() => {
                        if (value) {
                            toastr.success('Payment provider has been enabled.');
                        } else {
                            toastr.success('Payment provider has been disabled.');
                        }
                    });
            },
            updateProvider() {
                this.saving = true;
                axios.post('/jpanel/settings/payment', updateProviderInputFilter(this.input))
                    .then(() => {
                        toastr.success('Payment provider has been updated.');
                    })
                    .finally(() => {
                        this.saving = false;
                    });
            }
        }
    });
}
