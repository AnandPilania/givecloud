import TimeAgo from 'javascript-time-ago'
import enUS from 'javascript-time-ago/locale/en.json'
import frCA from 'javascript-time-ago/locale/fr-CA.json'
import esMX from 'javascript-time-ago/locale/es-MX.json'

TimeAgo.addLocale(enUS)
TimeAgo.addLocale(frCA)
TimeAgo.addLocale(esMX)

export const timeAgo = (locale, date) => {
  if (typeof date === 'string') {
    date = new Date(date)
  }

  return new TimeAgo(locale).format(date)
}
