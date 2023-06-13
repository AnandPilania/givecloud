Vue.component('address-lookup-input', {
  name: 'AddressLookupInput',

  template: '<div>'
      + '<label :for="id" v-text="label" />'
      + '<input '
        + ':id="id" '
        + ':name="name" '
        + 'type="text" '
        + 'ref="autocomplete" '
        + ':class="classes" '
        + ':placeholder="placeholder" '
        + ':required="required" '
        + 'x-autocompletetype="address-line1" '
        + 'autocompletetype="address-line1" '
        + 'autocorrect="off" '
        + 'spellcheck="off" '
        + 'autocapitalize="off" '
        + 'v-model="autocompleteText" '
        + '@keydown.enter.prevent="" '
        + '@change="onChange" />'
    + '</div>',

  props: {
    classes: {
      type: String,
      required: false,
      default: "form-control"
    },
    id: {
      type: String,
      required: false,
      default: "inputLookupAddress",
    },
    label: {
      type: String,
      required: false,
      default: "Address Lookup",
    },
    name: {
      type: String,
      required: true,
    },
    placeholder: {
      type: String,
      required: false,
      default: "Start Typing",
    },
    required: {
      type: Boolean,
      required: false,
      default: true,
    },
    value: {
      type: String,
      required: false,
      default: "",
    },
  },

  data: function () {
    return {
      address: {
        line1: "",
        line2: "",
        country: "",
        country_code: "",
        state: "",
        state_code: "",
        city: "",
        zip: "",
      },

      /**
       * @type {Autocomplete}
       * @link https://developers.google.com/maps/documentation/javascript/reference#Autocomplete
       */
      autocomplete: null,

      autocompleteText: "",
    }
  },

  watch: {
    autocompleteText: function (newVal, oldVal) {
      this.$emit('inputChange', { newVal: newVal, oldVal: oldVal }, this.id)
    },
  },

  mounted: function () {
    this.googleMapsLoaded()
  },

  methods: {
    /**
     * Format result from Google API.
     *
     * @param {Object} place
     * @returns {Object} address
     */
    formatResult: function (place) {
      /**
       * Update the value of the input.
       *
       * @param {String} type
       * @param {String} name
       * @returns {String}
       */
      function findType(type, name) {
        var matchingType = place.address_components.filter(function (component) {
          return component.types.indexOf(type) > -1
        })

        if (matchingType.length === 0) {
          return ""
        }

        return matchingType[0][name || 'long_name'] || ""
      }

      return this.filterFormatResult({
        line1: [findType('street_number'), findType('route')].join(' '),
        line2: findType('subpremise'),
        country: findType('country'),
        country_code: findType('country', 'short_name'),
        state: findType('administrative_area_level_1'),
        state_code: findType('administrative_area_level_1', 'short_name'),
        city: findType('sublocality') || findType('locality') || findType('postal_town'),
        zip: findType('postal_code', 'short_name'),
      })
    },

    filterFormatResult: function (address) {
      if (address.country_code === 'GU') {
          address.country = 'United States';
          address.country_code = 'US';
          address.state = 'Guam';
          address.state_code = 'GU';
      }

      return address;
    },

    /**
     * Load Google autocomplete component once Google Maps is ready.
     */
    googleMapsLoaded: function () {
      window.googleMapsLoaded.then(function () {
        this.autocomplete = new google.maps.places.Autocomplete(
          document.getElementById(this.id),
          { types: ['address'], fields: ['address_components'] }
        )
        this.autocomplete.addListener('place_changed', this.onPlaceChanged)
        // Reset input value to prop value.
        this.autocompleteText = this.value
      }.bind(this))
    },

    onChange: function () {
      this.$emit('input', this.autocompleteText)
      this.$emit('change', this.autocompleteText)
    },

    onPlaceChanged: function () {
      var place = this.autocomplete.getPlace()

      if (place.address_components) {
        this.address = this.formatResult(place)
        this.$emit('place-change', this.address)
        this.autocompleteText = this.address.line1
      } else {
        this.autocompleteText = place.name
      }
      this.onChange()
    },
  },
})
