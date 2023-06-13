import getConfig from '@/utilities/config'

export const initGoogleTagManager = () => {
  const config = getConfig()

  if (!config.gtm_container_id) {
    return
  }

  const scriptEl = document.createElement('script')
  scriptEl.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(config.gtm_container_id)}`
  scriptEl.async = true

  document.head.appendChild(scriptEl)

  window.gtag = function () {
    window.dataLayer = window.dataLayer || []
    window.dataLayer.push(arguments)
  }

  window.gtag('js', new Date())
  window.gtag('config', config.gtm_container_id)
}
