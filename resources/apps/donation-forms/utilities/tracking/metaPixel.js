import getConfig from '@/utilities/config'

export const initMetaPixel = () => {
  const config = getConfig()

  if (!config.meta_pixel_id) {
    return
  }

  window.fbq = window._fbq = function () {
    window.fbq.callMethod ? window.fbq.callMethod.apply(window.fbq, arguments) : window.fbq.queue.push(arguments)
  }

  window.fbq.push = window.fbq
  window.fbq.loaded = true
  window.fbq.version = '2.0'
  window.fbq.queue = []

  const scriptEl = document.createElement('script')
  scriptEl.src = 'https://connect.facebook.net/en_US/fbevents.js'
  scriptEl.async = true

  document.head.appendChild(scriptEl)

  window.fbq('init', config.meta_pixel_id)
  window.fbq('track', 'PageView')
}
