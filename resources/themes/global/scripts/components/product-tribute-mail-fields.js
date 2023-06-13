Vue.component('product-tribute-mail-fields', {
  name: 'ProductTributeMailFields',

  template: '<div class="row gutter-sm">'
    + '<div class="col-12">'
        + '<div class="form-group labelify" :class="{ \'has-errors\': errors.has(\'address\') }">'
            + '<address-lookup-input '
                + ':id="\'ptmf-alu-\' + productId" '
                + 'name="tribute_notify_address" '
                + 'v-model="input.address" '
                + ':required="required" '
                + ':label="lang.address.label" '
                + ':placeholder="lang.address.placeholder" '
                + '@place-change="fillAddressFields" '
            + '/>'
            + '<div class="invalid-feedback" v-text="lang.address.invalid" />'
        + '</div>'
    + '</div>'
    + '<div class="col-5">'
        + '<div class="form-group labelify" :class="{ \'has-errors\': errors.has(\'city\') }">'
            + '<label for="tribute_notify_city" v-text="lang.city.label" />'
            + '<input '
                + 'type="text" '
                + 'id="tribute_notify_city" '
                + 'name="tribute_notify_city" '
                + 'class="form-control" '
                + 'v-model="input.city" '
                + 'x-autocompletetype="city" '
                + 'autocompletetype="city" '
                + 'autocorrect="off" '
                + 'spellcheck="off" '
                + 'autocapitalize="off">'
            + '<div class="invalid-feedback" v-text="lang.city.invalid" />'
        + '</div>'
    + '</div>'
    + '<div class="col-3">'
        + '<div class="form-group labelify" :class="{ \'has-errors\': errors.has(\'state\') }">'
            + '<label for="tribute_notify_state">'
                + '<template v-if="input.country_code === \'US\'">{{ lang.state.state }}</template>'
                + '<template v-else>{{ lang.state.province }}</template>'
            + '</label>'
            + '<select v-if="subdivisions" id="tribute_notify_state" name="tribute_notify_state" class="form-control" v-model="input.state">'
                + '<option></option>'
                + '<option v-for="(name, code) in subdivisions" :value="code" v-text="name" />'
            + '</select>'
            + '<input v-else id="tribute_notify_state" type="text" class="form-control" name="tribute_notify_state" v-model="input.state">'
        + '</div>'
    + '</div>'
    + '<div class="col-4">'
        + '<div class="form-group labelify" :class="{ \'has-errors\': errors.has(\'zip\') }">'
            + '<label for="tribute_notify_zip">'
              + '<template v-if="input.country_code === \'US\'">{{ lang.zip.zip }}</template>'
              + '<template v-else>{{ lang.zip.postal }}</template>'
            + '</label>'
            + '<input '
                + 'type="text" '
                + 'class="form-control" '
                + 'id="tribute_notify_zip" '
                + 'name="tribute_notify_zip" '
                + 'v-model="input.zip" '
                + 'x-autocompletetype="postal-code" '
                + 'autocompletetype="postal-code" '
                + 'autocorrect="off" '
                + 'spellcheck="off" '
                + 'autocapitalize="off">'
            + '<div class="invalid-feedback" v-text="lang.zip.invalid" />'
        + '</div>'
    + '</div>'
    + '<div class="col-8">'
        + '<div class="form-group labelify" :class="{ \'has-errors\': errors.has(\'input.country_code\') }">'
            + '<label for="tribute_notify_country" v-text="lang.country.label" />'
            + '<select class="form-control" id="tribute_notify_country" name="tribute_notify_country" v-model="input.country_code">'
                + '<option v-for="country in countries" :value="country.value" v-text="country.label" />'
            + '</select>'
            + '<div class="invalid-feedback" v-text="lang.country.invalid" />'
        + '</div>'
    + '</div>'
  + '</div>',

  props: {
    productId: {
      type: Number,
      require: true,
    },
    required: {
      type: Boolean,
      required: false,
      default: true,
    },
  },

  data: function () {
    return {
      lang: {
        address: {
          invalid: theme.trans('scripts.components.product_tribute_mail_fields.which_address_are_we_sending_the_notice_to'),
          label: theme.trans('scripts.components.product_tribute_mail_fields.street_address'),
          placeholder: theme.trans('general.forms.address_placeholder'),
        },
        city: {
          invalid: theme.trans('scripts.components.product_tribute_mail_fields.which_city'),
          label: theme.trans('general.forms.city'),
        },
        country: {
          invalid: theme.trans('scripts.components.product_tribute_mail_fields.which_country'),
          label: theme.trans('general.forms.country'),
        },
        state: {
          province: theme.trans('general.forms.province'),
          state: theme.trans('general.forms.state'),
        },
        zip: {
          invalid: theme.trans('scripts.components.product_tribute_mail_fields.what_is_the_zip'),
          postal: theme.trans('general.forms.postal'),
          zip: theme.trans('general.forms.zip'),
        },
      },
      input: {
        address: '',
        city: '',
        country_code: '',
        state: '',
        zip: '',
      }
    }
  },

  asyncComputed: {
    countries: {
      get: function () {
        return Givecloud.Services.Locale.countries().then(function (data) {
          var countries = [];
          if (Givecloud.config.force_country) {
            countries.push({
              value: Givecloud.config.force_country,
              label: data.countries[Givecloud.config.force_country] || Givecloud.config.force_country,
            });
          } else {
            Givecloud.config.pinned_countries.forEach(function (code) {
              countries.push({ value: code, label: data.countries[code] || code });

              if (data.countries[code]) {
                delete data.countries[code];
              }
            });

            if (countries.length) {
              countries.push({ value: '', label: '' });
            }

            Sugar.Object.forEach(data.countries, function (name, code) {
              countries.push({ value: code, label: name });
            });
          }

          return Promise.resolve(countries);
        });
      },
      default: {}
    },
    subdivisions: {
        get: function () {
          return this.getCountryStates(this.input.country_code);
        },
        default: {},
    },
  },

  methods: {
    fillAddressFields: function (address) {
      this.input.address = address.line1;
      this.input.city = address.city;
      this.input.zip = address.zip;
      this.input.country_code = address.country_code;

      // Deal with province/state dropdown differently as it is populated by an asyncComputed property.
      // I extracted it to a function to reuse it here, wrapped in a promise
      // to be able to select the correct province / state.
      this.getCountryStates(this.input.country_code).then(function (states) {
        // Match with code.
        var stateCode = states[address.state_code] ? address.state_code : null;

        // No match with code, so try to match with name instead.
        if (! stateCode) {
          stateCode = Object.keys(states).filter(function (code) {
            return states[code] === address.state;
          })[0] || null;
        }

        this.input.state = stateCode;
      }.bind(this));
    },

    getCountryStates: function (country) {
      return Givecloud.Services.Locale.subdivisions(country)
        .then(function (data) {
          if (Sugar.Object.size(data.subdivisions) === 0) {
            return Promise.resolve(null);
          }

          this.lang.state.province = theme.trans('general.subdivision_types.' + Sugar.String.underscore(data.subdivision_type));

          return Promise.resolve(data.subdivisions);
        }.bind(this));
    }
  }
})
