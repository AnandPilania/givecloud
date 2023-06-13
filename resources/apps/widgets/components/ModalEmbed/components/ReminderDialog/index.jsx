import ReactDOM from 'react-dom'
import tinycolor from 'tinycolor2'
import ReminderDialog from './ReminderDialog'
import styles from './ReminderDialog.scss?string-loader'

const renderReminderDialog = (modal, contributionAmount) => {
  const config = modal.fundraisingFormConfig.config

  document.getElementById('givecloud-reminder-dialog')?.remove()

  const el = document.createElement('div')
  el.id = 'givecloud-reminder-dialog'
  document.body.append(el)

  const shadowRoot = el.attachShadow({ mode: 'open' })

  shadowRoot.innerHTML = `
    <style>${styles}</style>
    <div id="app"></div>
  `

  const primaryColour = tinycolor(config.primary_colour || '#f93d4c')
  const backgroundColour = tinycolor(config.primary_colour || '#fbb92b')

  const cssVariables = {
    '--background-colour': backgroundColour.setAlpha(0.9).toRgbString(),
    '--text-colour': backgroundColour.getBrightness() < 172 ? '#fff' : '#000',
    '--primary-colour': primaryColour.toHexString(),
    '--primary-colour-white-or-black': primaryColour.getBrightness() < 172 ? '#fff' : '#000',
  }

  const styleElement = shadowRoot.querySelector('style')

  Object.keys(cssVariables).forEach((property) => {
    styleElement.sheet.cssRules[0].style.setProperty(property, cssVariables[property])
  })

  const dismissDialog = () => el.remove()

  ReactDOM.render(
    <ReminderDialog modal={modal} dismissDialog={dismissDialog} contributionAmount={contributionAmount} />,
    shadowRoot.getElementById('app')
  )
}

export default renderReminderDialog
