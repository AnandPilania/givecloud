import 'current-script-polyfill'
import 'url-polyfill'
import WebFont from 'webfontloader'
import InlineEmbed from './components/InlineEmbed/InlineEmbed'
import ModalEmbed from './components/ModalEmbed/ModalEmbed'
import { onDomReady } from './utils/dom'

const widgets = new Map()
const widgetApiHasNotAlreadyBeenLoaded = typeof window['GivecloudWidgetApi'] === 'undefined'

const firstOrCreateWidget = (config) => {
  const fundraisingFormId = config.options.fundraisingFormId || config.options.widgetId
  const widgetCacheKey = `${fundraisingFormId}:${config.widgetType}`

  if (widgets.has(widgetCacheKey)) {
    return widgets.get(widgetCacheKey)
  }

  widgets.set(
    widgetCacheKey,
    new config.widgetClass({
      ...config.options,
      fundraisingFormId,
      widgetCacheKey,
      widgetType: config.widgetType,
    })
  )

  return widgets.get(widgetCacheKey)
}

const getModalEmbed = (options) => {
  return firstOrCreateWidget({
    options: {
      ...options,
      onClose: (widget) => widgets.delete(widget.widgetCacheKey),
    },
    widgetClass: ModalEmbed,
    widgetType: 'modal_embed',
  })
}

const createModalEmbed = (options) => {
  const widget = getModalEmbed(options)
  widget.showReminderDialog()

  return {
    open(position = { x: '50%', y: '50%' }) {
      openModalEmbed(options, position)
    },
  }
}

const openModalEmbed = (options, position) => {
  getModalEmbed(options).open(position)
}

const renderInlineEmbed = (options) => {
  firstOrCreateWidget({
    options,
    widgetClass: InlineEmbed,
    widgetType: 'inline_embed',
  })
}

if (widgetApiHasNotAlreadyBeenLoaded) {
  document.addEventListener(
    'click',
    function (event) {
      const options = ModalEmbed.detectEmbed(event.target)

      if (options) {
        event.preventDefault()

        openModalEmbed(options, {
          x: `${event.clientX}px`,
          y: `${event.clientY}px`,
        })
      }
    },
    false
  )

  onDomReady(() => {
    document.querySelectorAll(InlineEmbed.getEmbedSelectors().join(',')).forEach((element) => {
      const options = InlineEmbed.detectEmbed(element)

      if (options) {
        renderInlineEmbed(options)
      }
    })

    document.querySelectorAll(ModalEmbed.getEmbedSelectors().join(',')).forEach((element) => {
      const options = ModalEmbed.detectEmbed(element)

      if (options) {
        createModalEmbed(options)
      }
    })

    // attempt to preload fundraising forms with any locally
    // save state for which a reminder dialog could be shown
    Object.keys(localStorage)
      .filter((key) => key.match(/^fundraisingForm_/))
      .forEach((key) => {
        createModalEmbed({ fundraisingFormId: key.replace(/^fundraisingForm_(.+)$/, '$1') })
      })
  })
}

WebFont.load({
  google: {
    families: ['Inter:wght@200;300;400;500;600;700;800;900'],
  },
})

export default {
  version: '0.0.1',
  // widgets,
  createModalEmbed: createModalEmbed,
  openModalEmbed: openModalEmbed,
  openWidget: openModalEmbed,
  renderInlineEmbed: renderInlineEmbed,
}
