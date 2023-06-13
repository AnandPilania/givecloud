import { escapeRegExp } from 'lodash'
import FundraisingForm from '@/components/FundraisingForm'
import { getStyles, getViewportMeta, removeStyles, setStyles, setViewportMeta } from '@/utils/dom'
import scriptUrl from '@/utils/scriptUrl'
import renderReminderDialog from './components/ReminderDialog'

class ModalEmbed extends FundraisingForm {
  constructor(options) {
    super(options)

    this.onClose = options.onClose

    this.bodyStyles = null
    this.viewportMeta = null
    this.widgetType = 'modal_embed'
    this.isOpen = false

    this.$setupContainerBackdropSpinnerAndIframe = this._setupContainerBackdropSpinnerAndIframe()
  }

  async open({ x = '50%', y = '50%' } = {}) {
    this.circleAt = `${x} ${y}`

    if (this.isOpen) {
      return
    }

    this.isOpen = true

    // don't allow embed if the site embedding isn't using HTTPS instead
    // redirect to the hosted version for the fundraising form
    if ('https:' !== window.location.protocol) {
      window.location.href = this.fundraisingFormUrl
      return
    }

    // if there was an issue with the setup try redirecting to the hosted page
    this.$setupContainerBackdropSpinnerAndIframe.catch(() => {
      window.location.href = this.fundraisingFormUrl
    })

    this.dismissReminderDialog()
    this._preventScrollingAndZooming()
    this._renderAppInIframe()
    this._animateBackdropClipPathFromClickOrigin()
    this._createBackdropOverlay()

    this._fundraisingFormReady(() => {
      this.$spinner.style.display = 'none'

      this._collectEvent({
        event_name: 'open',
        event_category: 'fundraising_forms.modal_embed',
      })
    })
  }

  async showReminderDialog() {
    await this.$setupContainerBackdropSpinnerAndIframe

    const reminderEnabled = !!this.fundraisingFormConfig?.config?.embed_options?.reminder?.enabled
    const stateFromPreviousVisit = JSON.parse(localStorage.getItem(`fundraisingForm_${this.fundraisingFormId}`) || null)

    if (reminderEnabled && stateFromPreviousVisit) {
      renderReminderDialog(this, stateFromPreviousVisit?.friendlyAmount)
    }
  }

  dismissReminderDialog() {
    document.getElementById('givecloud-reminder-dialog')?.remove()
  }

  minimize() {
    if (this.$container.style.display === 'none') {
      return
    }

    this.isOpen = false

    this._animateBackdropClipPathToClickOrigin()
    this._restoreScrollingAndZooming()
    this.showReminderDialog()
    this._removeBackdropOverlay()

    this._collectEvent({
      event_name: 'close',
      event_category: 'fundraising_forms.modal_embed',
    })
  }

  close() {
    this.isOpen = false

    this._animateBackdropClipPathToClickOrigin()
    this._restoreScrollingAndZooming()

    this._collectEvent({
      event_name: 'close',
      event_category: 'fundraising_forms.modal_embed',
    })

    this.onClose?.(this)
  }

  _preventScrollingAndZooming() {
    const styles = {
      position: 'fixed',
      right: 0,
      left: 0,
      overflow: 'hidden',
    }

    this.bodyStyles = getStyles(document.body, Object.keys(styles))
    setStyles(document.body, styles)

    this.viewportMeta = getViewportMeta(document)
    setViewportMeta(document, 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no')
  }

  _restoreScrollingAndZooming() {
    setStyles(document.body, this.bodyStyles)
    setViewportMeta(document, this.viewportMeta)

    this.bodyStyles = null
    this.viewportMeta = null
  }

  async _setupContainerBackdropSpinnerAndIframe() {
    if ('https:' !== window.location.protocol) {
      return
    }

    this._createContainer()
    this._createBackdrop()
    this._createSpinner(this.$backdrop)

    await this._createIframe()

    this._collectEvent({
      event_name: 'impression',
      event_category: 'fundraising_forms.modal_embed',
    })

    return true
  }

  _createContainer() {
    this.$container = document.createElement('div')

    setStyles(this.$container, {
      display: 'none',
      position: 'fixed',
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      'z-index': 2147483647,
      overflow: 'hidden',
      '-webkit-overflow-scrolling': 'touch',
    })

    document.body.appendChild(this.$container)
  }

  _createBackdrop() {
    this.$backdrop = document.createElement('div')

    setStyles(this.$backdrop, {
      display: 'flex',
      'justify-content': 'center',
      width: '100%',
      height: '100%',
      background: 'rgba(0,0,0,0.5)',
      'clip-path': `circle(0% at ${this.circleAt})`,
      transition: '1s ease clip-path',
      'box-sizing': 'border-box',
    })

    this.$container.appendChild(this.$backdrop)
  }

  // For better performance, removing backdrop-filter from this.$backdrop and adding an additional layer on top
  _createBackdropOverlay() {
    this.$backdropOverlay = document.createElement('div')

    setStyles(this.$backdropOverlay, {
      position: 'absolute',
      inset: 0,
      '-webkit-backdrop-filter': 'blur(5px)',
      'backdrop-filter': 'blur(5px)',
      'z-index': -1,
    })

    this.$container.appendChild(this.$backdropOverlay)
  }

  _removeBackdropOverlay() {
    this.$backdropOverlay.remove()
  }

  _animateBackdropClipPathToClickOrigin() {
    setStyles(this.$backdrop, {
      'clip-path': `circle(200% at 50% 50%)`,
      transition: '0.3s ease clip-path',
    })

    // wrapping in setTimeout call to avoid issues with transitions
    // not being animated properly depending on the order of setStyles calls
    setTimeout(() => {
      setStyles(this.$backdrop, { 'clip-path': `circle(0% at 50% 50%)` })

      setTimeout(() => (this.$container.style.display = 'none'), 1000)
    })
  }

  _animateBackdropClipPathFromClickOrigin() {
    setStyles(this.$backdrop, {
      'clip-path': `circle(0% at ${this.circleAt})`,
      transition: '1s ease clip-path',
    })

    this.$container.style.display = 'block'

    // wrapping in setTimeout call to avoid issues with transitions
    // not being animated properly depending on the order of setStyles calls
    setTimeout(() => {
      setStyles(this.$backdrop, { 'clip-path': `circle(200% at ${this.circleAt})` })

      // clip-path just destroys performance so remove once the animation is done
      setTimeout(() => removeStyles(this.$backdrop, ['clip-path']), 1000)
    })
  }

  async _createIframe() {
    await this._createFundraisingFormIframe(this.$backdrop, {
      onMinimize: (contributionAmount) => this.minimize(contributionAmount),
      onClose: () => this.close(),
    })
  }

  static getEmbedSelectors() {
    return [
      'a[href^="#/fundraising/forms/"]',
      `a[href^="https://${escapeRegExp(scriptUrl.host)}/fundraising/forms/"]`,
      '[data-fundraising-form]:not([data-inline])',
    ]
  }

  static detectEmbed(element) {
    const linkWithAnchor = element.closest('a[href^="#/fundraising/forms/"]')

    if (linkWithAnchor) {
      const regex = new RegExp(`^#/fundraising/forms/([\\w\\d-]+)$`)
      const matches = linkWithAnchor.hash.match(regex)

      if (matches) {
        return { fundraisingFormId: matches[1], widgetType: 'modal_embed' }
      }
    }

    const linkWithUrl = element.closest(`a[href^="https://${escapeRegExp(scriptUrl.host)}/fundraising/forms/"]`)

    if (linkWithUrl) {
      const regex = new RegExp(`^https://${escapeRegExp(scriptUrl.host)}/fundraising/forms/([\\w\\d-]+)$`)
      const matches = linkWithUrl.href.match(regex)

      if (matches) {
        return { fundraisingFormId: matches[1], widgetType: 'modal_embed' }
      }
    }

    const elementWithDataAttribute = element.closest('[data-fundraising-form]:not([data-inline])')

    if (elementWithDataAttribute) {
      return { fundraisingFormId: elementWithDataAttribute.dataset.fundraisingForm, widgetType: 'modal_embed' }
    }

    return null
  }
}

export default ModalEmbed
