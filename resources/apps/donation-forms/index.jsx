import 'wicg-inert'
import ReactDOM from 'react-dom'
import { initLogRocket } from '@/utilities/tracking/logRocket'
import { initGoogleTagManager } from '@/utilities/tracking/googleTagManager'
import { initMetaPixel } from '@/utilities/tracking/metaPixel'

// styles.css needs to be loaded before the App component in order for Tailwind
// preflight styles to not take precedence of styles define in the CSS modules
// of the React app. This is primarily an issue for us with [type='button'] styles.
import './styles.css'
import App from '@/components/App/App'

window.iFrameResizer = { onReady: () => window.parentIFrame.sendMessage({ name: 'fundraisingFormReady' }) }
import 'iframe-resizer/js/iframeResizer.contentWindow'

const element = document.getElementById('app-root')

window.renderApp = () => {
  if (element.hasAttribute('data-rendered')) {
    return
  }

  initLogRocket()
  initGoogleTagManager()
  initMetaPixel()

  element.setAttribute('data-rendered', 'true')
  ReactDOM.render(<App />, element)
}

if (!element.hasAttribute('data-dont-bootstrap')) {
  window.renderApp()
}
