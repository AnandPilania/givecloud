import FundraisingForm from '@/components/FundraisingForm'
import { setAttributes, setStyles } from '@/utils/dom'
import scriptUrl from '@/utils/scriptUrl'
import styles from './InlineEmbed.scss?style-loader'

class InlineEmbed extends FundraisingForm {
  constructor({ containerElement, ...options }) {
    super(options)

    this.widgetType = 'inline_embed'
    this._renderContainerIframe(containerElement)
  }

  async _renderContainerIframe(element) {
    this._createContainer(element)

    // don't allow embed if the site embedding isn't using HTTPS
    if ('https:' !== window.location.protocol) {
      this._createInsecureWebsiteIframe()
      return
    }

    this._createSpinner(this.$container, {
      position: 'absolute',
      bottom: 'auto',
      width: '413px',
      height: '590px',
      'margin-top': '24px',
      'border-radius': '0',
      rounded: false,
      shadow: false,
    })

    this._createIframe()
    await this._renderAppInIframe()

    this._fundraisingFormReady(() => (this.$spinner.style.display = 'none'))

    this._collectEvent({
      event_name: 'impression',
      event_category: 'fundraising_forms.inline_embed',
    })
  }

  _createContainer(element) {
    this.$container = document.createElement('div')
    this.$container.className = styles.container

    Object.keys(element.dataset).forEach((key) => {
      this.$container.dataset[key] = element.dataset[key]
    })

    element.replaceWith(this.$container)
  }

  _createInsecureWebsiteIframe() {
    const iframe = document.createElement('iframe')

    setAttributes(iframe, {
      allowtransparency: 'true',
      frameborder: 0,
    })

    setStyles(iframe, {
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
    })

    this.$container.appendChild(iframe)

    const contentForInsecuredWebsite = `
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
        <style>
        html, body { height: 90%; overflow: hidden; }
        body { display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; font-family: Inter, text-align: center; }
        h1 { margin-bottom: 5px; font-size: 32px; font-weight: bold; }
        .btn { font-size: 24px; }
        </style>
      </head>
      <body>
      <h1>Fundraising Form</h1>
      <a class="btn" href="https://${scriptUrl.host}/fundraising/forms/${this.fundraisingFormId}" target="_blank">Click Here to Donate</a>
      </body>
      </html>
    `

    iframe.contentDocument.open('text/html', 'replace')
    iframe.contentDocument.write(contentForInsecuredWebsite)
    iframe.contentDocument.close()
  }

  async _createIframe() {
    await this._createFundraisingFormIframe(this.$container)
  }

  static getEmbedSelectors() {
    return ['[data-fundraising-form][data-inline]']
  }

  static detectEmbed(element) {
    if (element.matches('[data-fundraising-form][data-inline]')) {
      return {
        containerElement: element,
        fundraisingFormId: element.dataset.fundraisingForm,
        widgetType: 'inline_embed',
      }
    }

    return null
  }
}

export default InlineEmbed
