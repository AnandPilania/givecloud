export const setAttributes = (el, attributes) => {
  Object.keys(attributes).forEach((key) => el.setAttribute(key, attributes[key]))
}

export const getStyles = (el, keys) => {
  const styles = {}
  const computedStyle = window.getComputedStyle(el, null)
  keys.forEach((key) => (styles[key] = computedStyle.getPropertyValue(key)))
  return styles
}

export const setStyles = (el, styles) => {
  Object.keys(styles).forEach((key) => el.style.setProperty(key, styles[key]))
}

export const removeStyles = (el, styles) => {
  styles.forEach((key) => el.style.removeProperty(key))
}

export const getViewportMeta = (doc) => {
  return doc.querySelector('meta[name="viewport"]')?.getAttribute('content') || null
}

export const setViewportMeta = (doc, content) => {
  let el = doc.querySelector('meta[name="viewport"]')

  if (el) {
    el.setAttribute('content', content || '')
  } else {
    el = doc.createElement('meta')
    el.name = 'viewport'
    el.content = content

    doc.head.appendChild(el)
  }
}

export const loadScript = (doc, script) => {
  let resolved = false

  return new Promise((resolve, reject) => {
    const el = doc.createElement('script')
    el.type = 'text/javascript'

    if (typeof script === 'string') {
      script = { src: script }
    }

    const { src, ...attributes } = script

    el.async = false
    el.src = src
    setAttributes(el, attributes)

    el.onerror = (err) => reject(err, el)

    el.onload = el.onreadystatechange = function () {
      if (!resolved && (!this.readyState || this.readyState == 'complete')) {
        resolved = true
        resolve()
      }
    }

    doc.body.appendChild(el)
  })
}

export const loadStyle = (doc, href) => {
  const el = doc.createElement('link')
  el.setAttribute('rel', 'stylesheet')
  el.href = href
  doc.head.appendChild(el)
}

export const onDomReady = (callback) => {
  if (document.readyState !== 'loading') {
    callback()
  } else if (document.addEventListener) {
    document.addEventListener('DOMContentLoaded', callback)
  }
}

export const setInnerHtml = (el, html) => {
  el.innerHTML = html
  Array.from(el.querySelectorAll('script')).forEach((oldScript) => {
    const newScript = document.createElement('script')

    Array.from(oldScript.attributes).forEach((attr) => newScript.setAttribute(attr.name, attr.value))
    newScript.appendChild(document.createTextNode(oldScript.innerHTML))
    oldScript.parentNode.replaceChild(newScript, oldScript)
  })
}
