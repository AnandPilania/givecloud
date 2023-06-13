import { iframeResize } from 'iframe-resizer'
import classnames from 'classnames'
import Givecloud from '@core/givecloud'
import Deferred from '@/utils/deferred'
import { setAttributes, setStyles, loadScript, loadStyle } from '@/utils/dom'
import scriptUrl from '@/utils/scriptUrl'
import styles from './FundraisingForm.scss?style-loader'

let googlePayAdded = false

const fundraisingFormIframeContents = `
  <!DOCTYPE html>
  <html>
  <head>
    <title>Fundraising Form</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta name="csrf-token" content="">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  </head>
  <body>
  <div id="app-root" data-dont-bootstrap></div>
  <div id="app-portal"></div>
  </body>
  </html>
`

const spinnerSvg = `
  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
    <circle cx="50" cy="50" r="45" stroke="#eaeaea" stroke-width="6" fill="none"></circle>
    <circle cx="50" cy="50" r="45" stroke="#636363" stroke-width="4" stroke-linecap="round" fill="none">
      <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1.5s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1"></animateTransform>
      <animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1.5s" values="0 282;112 170;0 282" keyTimes="0;0.5;1"></animate>
    </circle>
  </svg>
`

class FundraisingForm {
  constructor({ fundraisingFormId, widgetCacheKey, widgetType }) {
    this.fundraisingFormId = fundraisingFormId
    this.fundraisingFormUrl = `https://${scriptUrl.host}/fundraising/forms/${fundraisingFormId}`
    this.widgetCacheKey = widgetCacheKey
    this.widgetType = widgetType

    this.fundraisingFormReady = new Deferred()
    this.fundraisingFormConfig = null
    this.renderAppInIframeIntervalId = null
    this.$fundraisingFormIframe = null
  }

  async _fundraisingFormReady(callback) {
    await this.fundraisingFormReady.promise
    callback?.()
  }

  _renderAppInIframe() {
    clearInterval(this.renderAppInIframeIntervalId)

    return new Promise((resolve) => {
      this.renderAppInIframeIntervalId = setInterval(() => {
        if (this.$fundraisingFormIframe?.contentWindow?.renderApp) {
          this.$fundraisingFormIframe.contentWindow.renderApp()
          clearInterval(this.renderAppInIframeIntervalId)
          resolve(true)
        }
      }, 25)
    })
  }

  _createSpinner(element, { ...spinnerStyles } = {}) {
    this.$spinner = document.createElement('div')

    setStyles(this.$spinner, {
      position: 'fixed',
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      'z-index': 10,
      display: 'flex',
      'align-items': 'center',
      'justify-content': 'center',
      'text-align': 'center',
      ...spinnerStyles,
    })

    this.$spinner.innerHTML = `
      <div class="${classnames(styles.spinner)}">
        ${spinnerSvg}
      </div>
    `

    element.appendChild(this.$spinner)
  }

  async _createFundraisingFormIframe(element, options = {}) {
    this.$fundraisingFormIframe = document.createElement('iframe')

    setAttributes(this.$fundraisingFormIframe, {
      title: 'Fundraising Form',
      allowtransparency: 'true',
      frameborder: 0,
      allowpaymentrequest: 'true',
      allow: 'payment',
    })

    // check for Chrome for iOS
    if (window.navigator.userAgent.match('CriOS')) {
      this.$fundraisingFormIframe.setAttribute('src', location.origin)
    }

    setStyles(this.$fundraisingFormIframe, {
      position: 'relative',
      display: 'block',
      width: '100%',
      height: '100%',
      'max-width': '100%',
      'max-height': '100%',
      margin: 'auto',
      border: 'none',
      'box-sizing': 'border-box',
      background: 'transparent',
      'border-radius': '0',
      'z-index': 20,
    })

    element.appendChild(this.$fundraisingFormIframe)

    const fundraisingFormId = encodeURIComponent(this.fundraisingFormId)
    const widgetType = encodeURIComponent(this.widgetType)

    const res = await fetch(`https://${scriptUrl.host}/v1/widgets/${fundraisingFormId}?widget_type=${widgetType}`)

    if (res.ok === false) {
      throw 'Unable to load fundraising form configuration.'
    }

    this.fundraisingFormConfig = await res.json()

    const doc = this.$fundraisingFormIframe.contentDocument
    const win = this.$fundraisingFormIframe.contentWindow

    doc.open('text/html', 'replace')
    doc.write(fundraisingFormIframeContents)
    doc.close()

    this.givecloud = new Givecloud()

    this.givecloud.$window = win
    this.givecloud.$parentWindow = window

    this.givecloud.setConfig(this.fundraisingFormConfig.givecloud.config)

    this.fundraisingFormConfig.givecloud.gateways.forEach((gateway) => {
      this.givecloud.Gateway(gateway.name).setConfig(gateway.settings)

      gateway.scripts.forEach(async (script) => {
        // for embedded fundraising forms paypal is instead loaded
        // in a dedicated iframe in order to circumvent the paypal redirects
        if (gateway.name === 'paypalexpress') {
          return
        }

        await loadScript(doc, script)

        // for braintree we also need to load the google pay script
        // into the parent page to allow performing google pay
        if (gateway.name === 'braintree' && !googlePayAdded) {
          googlePayAdded = true
          await loadScript(document, 'https://pay.google.com/gp/p/js/pay.js')
        }

        // for stripe we also need to load the scripts
        // into the parent page to allow performing wallet pay
        if (gateway.name === 'stripe' && !window.Stripe) {
          await loadScript(document, script)
        }
      })
    })

    win.Givecloud = this.givecloud
    win.donationFormConfig = this.fundraisingFormConfig.config

    this.fundraisingFormConfig.styles.forEach((href) => loadStyle(doc, href))
    this.fundraisingFormConfig.scripts.forEach(async (script) => await loadScript(doc, script))

    iframeResize(
      {
        log: false,
        autoResize: false,
        checkOrigin: false, // iframe has no src attribute
        sizeWidth: false,
        sizeHeight: false,
        scrolling: 'omit',
        onClose: () => options.onClose?.(),
        onMessage: ({ message }) => {
          switch (message?.name) {
            case 'fundraisingFormReady':
              this.fundraisingFormReady.resolve(true)
              options.onReady?.()
              break

            case 'fundraisingFormMinimize':
              options.onMinimize?.()
              break
          }
        },
      },
      this.$fundraisingFormIframe
    )
  }

  _collectEvent(data) {
    // if Givecloud instance hasn't been configed yet but an actual
    // form the local with defaults necessary to made the analytics calls
    if (!this.givecloud.config?.host) {
      this.givecloud.setConfig({
        host: scriptUrl.host,
        locale: { iso: 'en-US', language: 'en', region: 'US' },
      })
    }

    this.givecloud.Analytics.event({
      eventable: `product_${this.fundraisingFormId}`,
      ...data,
    })
  }
}

export default FundraisingForm
