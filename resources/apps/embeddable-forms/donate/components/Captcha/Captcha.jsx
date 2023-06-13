import { memo, useContext, useRef, useEffect } from 'react'
import ReCAPTCHA from 'react-google-recaptcha'
import HCaptcha from '@hcaptcha/react-hcaptcha'
import { StoreContext } from '@/root/store'
import styles from '@/components/Captcha/Captcha.scss'

const Captcha = () => {
  const { captcha, theme } = useContext(StoreContext)
  const captchaRef = useRef(null)

  useEffect(() => {
    captcha.ref.set(captchaRef)
  }, [captcha, captchaRef])

  if (!captcha.required) {
    return null
  }

  const onChange = (value) => {
    captcha.response.set(value)
  }

  return (
    <div className={styles.root}>
      {captcha.type === 'hcaptcha' && (
        <HCaptcha ref={captchaRef} theme={theme} sitekey={captcha.key} onVerify={onChange} />
      )}

      {captcha.type === 'recaptcha' && (
        <ReCAPTCHA ref={captchaRef} theme={theme} sitekey={captcha.key} onChange={onChange} />
      )}
    </div>
  )
}

export default memo(Captcha)
