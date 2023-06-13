import Endpoint from '@core/endpoint'
import EventEmitter from 'wolfy87-eventemitter'
import { arrayIncludes, getUrlParameter } from '@core/utils'

function getValues(obj) {
  return Object.keys(obj).reduce((values, property) => {
    return values.concat([obj[property]])
  }, [])
}

class CartEndpoint extends Endpoint {
  constructor(app, id) {
    super(app)

    this.$id = id
    this.$events = new EventEmitter()

    this.subscribe((cart) => (this.$cart = cart))
  }

  subscribe(fn) {
    this.$events.on('change', fn)
  }

  create(data) {
    data.utm_source = data.utm_source || getUrlParameter('utm_source')
    data.utm_medium = data.utm_medium || getUrlParameter('utm_medium')
    data.utm_campaign = data.utm_campaign || getUrlParameter('utm_campaign')
    data.utm_term = data.utm_term || getUrlParameter('utm_term')
    data.utm_content = data.utm_content || getUrlParameter('utm_content')

    return this.$http('POST', 'carts', data).then((cart) => {
      this.$id = cart.id
      this.$events.emitEvent('change', [cart])
      return Promise.resolve(cart)
    })
  }

  get() {
    return this.$http('GET', `carts/${this.$id}`).then((cart) => {
      this.$events.emitEvent('change', [cart])
      return Promise.resolve(cart)
    })
  }

  update(data) {
    return this.$http('PATCH', `carts/${this.$id}`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  delete() {
    //return this.$http('DELETE', `carts/${this.$id}`);
    return Promise.reject()
  }

  empty() {
    return this.$http('DELETE', `carts/${this.$id}/items`).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  addDiscount(code) {
    return this.$http('POST', `carts/${this.$id}/items`, { type: 'discount_item', data: { code } }).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  addProduct(data) {
    let validation = this.$validator(
      data,
      {
        variant_id: 'required|integer',
        amt: 'numeric|min:0',
        qty: 'integer|min:1',
        recurring_frequency: 'in:weekly,biweekly,monthly,quarterly,biannually,annually',
        recurring_day: 'required_if:recurring_frequency,monthly,quarterly,biannually,annually|integer|min:1|max:31',
        recurring_day_of_week: 'required_if:recurring_frequency,weekly,biweekly|integer|min:1|max:7',
        recurring_with_initial_charge: 'boolean',
        recurring_with_dpo: 'boolean',
        is_tribute: 'boolean',
        dpo_tribute_id: 'integer',
        tribute_type_id: 'required_if:is_tribute,1|integer|min:1',
        tribute_name: 'required_if:is_tribute,1',
        tribute_notify: 'in:email,letter',
        tribute_notify_name: 'required_with:tribute_notify',
        tribute_notify_email: 'required_if:tribute_notify,email|email',
        tribute_notify_address: 'required_if:tribute_notify,letter',
        tribute_notify_city: 'required_if:tribute_notify,letter',
        tribute_notify_state: 'required_if:tribute_notify,letter',
        tribute_notify_zip: 'required_if:tribute_notify,letter',
        tribute_notify_country: 'required_if:tribute_notify,letter',
        fields: 'array',
      },
      {
        'required.variant_id': 'No product selected.',
        'min.qty': 'Quantity is 0.',
      }
    )

    if (validation.fails()) {
      return Promise.reject(validation.errors.all())
    }

    return this.$http('POST', `carts/${this.$id}/items`, { type: 'product_item', data }).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  addSponsorship(data) {
    let validation = this.$validator(data, {
      sponsorship_id: 'required|integer',
      payment_option_id: 'required|integer',
      payment_option_amount: 'numeric',
      initial_charge: 'boolean',
    })

    if (validation.fails()) {
      return Promise.reject(validation.errors.all())
    }

    return this.$http('POST', `carts/${this.$id}/items`, { type: 'sponsorship_item', data }).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  updateItem(id, data) {
    return this.$http('PATCH', `carts/${this.$id}/items/${id}`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  upgradeItem(id, data) {
    return this.$http('PATCH', `carts/${this.$id}/items/${id}/upgrade`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  removeItem(id) {
    return this.$http('DELETE', `carts/${this.$id}/items/${id}`).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  checkout(data) {
    let validation = this.$validator(data, {
      ship_to_billing: 'boolean',
      account_type_id: 'required|integer',
      //billing_title          : '',
      billing_first_name: 'required',
      billing_last_name: 'required',
      //billing_company        : '',
      billing_email: 'required',
      billing_address1: 'required',
      //billing_address2       : '',
      //billing_company        : '',
      billing_city: 'required',
      //billing_province_code  : '',
      billing_zip: 'required',
      billing_country_code: 'required',
      // billing_phone          : 'required',
    })

    if (validation.fails()) {
      const errors = getValues(validation.errors.all())
      return Promise.reject(errors[0][0])
    }

    if (this.$cart.shippable_item_count > 0 && !data.ship_to_billing) {
      validation = this.$validator(data, {
        //shipping_title         : '',
        shipping_first_name: 'required',
        shipping_last_name: 'required',
        //shipping_email         : '',
        shipping_address1: 'required',
        //shipping_address2      : '',
        //shipping_company       : '',
        shipping_city: 'required',
        shipping_province_code: 'required',
        shipping_zip: 'required',
        shipping_country_code: 'required',
        //shipping_phone         : '',
      })

      if (validation.fails()) {
        const errors = getValues(validation.errors.all())
        return Promise.reject(errors[0][0])
      }
    }

    return this.$http('PATCH', `carts/${this.$cart.id}/checkout`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  oneClickCheckout(data, paymentType, requireBillingAddress = true) {
    let validation = this.$validator(data, {
      account_type_id: 'required|integer',
      //billing_title          : '',
      billing_first_name: 'required',
      billing_last_name: 'required',
      //billing_company        : '',
      billing_email: 'required',
      ...(requireBillingAddress && {
        billing_address1: 'required',
        //billing_address2       : '',
        //billing_company        : '',
        billing_city: 'required',
        //billing_province_code  : '',
        billing_zip: 'required',
        billing_country_code: 'required',
        // billing_phone          : 'required',
      }),
    })

    const shouldValidate = !arrayIncludes(['payment_method', 'paypal', 'wallet_pay'], paymentType)

    if (validation.fails() && shouldValidate) {
      const errors = getValues(validation.errors.all())
      return Promise.reject({
        cart: this.$cart,
        error: errors[0][0],
      })
    }

    if (this.$id) {
      data.cart_id = this.$id
    }

    data.utm_source = data.utm_source || getUrlParameter('utm_source')
    data.utm_medium = data.utm_medium || getUrlParameter('utm_medium')
    data.utm_campaign = data.utm_campaign || getUrlParameter('utm_campaign')
    data.utm_term = data.utm_term || getUrlParameter('utm_term')
    data.utm_content = data.utm_content || getUrlParameter('utm_content')

    return this.$http('POST', 'checkouts', data).then((cart) => {
      this.$id = cart.id
      this.$events.emitEvent('change', [cart])
      return Promise.resolve(cart)
    })
  }

  updateCheckout(data) {
    return this.$http('PATCH', `carts/${this.$id}/checkout`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  updateDcc(cover_costs_enabled, type = null) {
    const data = {
      cover_costs_enabled,
      cover_costs_type: type,
    }

    if (this.$app.config.processing_fees.using_ai && cover_costs_enabled && type === null) {
      data.cover_costs_type = 'more_costs'
    }

    return this.$http('PATCH', `carts/${this.$id}/dcc`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  updateEmployerMatch(data) {
    return this.$http('PATCH', `carts/${this.$id}/match`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  updateReferralSource(data) {
    return this.$http('PATCH', `carts/${this.$id}/referral`, data).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  updateOptin(email_opt_in, source = null) {
    return this.$http('PATCH', `carts/${this.$id}/optin`, { email_opt_in: !!email_opt_in, source }).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }

  complete() {
    if (this.$cart.requires_payment) {
      throw Error('Cart requires payment.')
    }

    return this.$http('PATCH', `carts/${this.$cart.id}/complete`).then((data) => {
      this.$events.emitEvent('change', [data.cart])
      return Promise.resolve(data)
    })
  }
}

export default CartEndpoint
