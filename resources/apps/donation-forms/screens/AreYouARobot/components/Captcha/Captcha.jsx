import { useRef } from 'react'
import { useEffectOnce } from 'react-use'
import { useRecoilState } from 'recoil'
import PropTypes from 'prop-types'
import ReactHcaptcha from '@hcaptcha/react-hcaptcha'
import ReactGoogleRecaptcha from 'react-google-recaptcha'
import captchaState from '@/atoms/captcha'
import styles from './Captcha.scss'

const Captcha = ({ onVerify }) => {
  const captchaRef = useRef(null)
  const [captcha, setCaptcha] = useRecoilState(captchaState)

  useEffectOnce(() => {
    const reset = () => {
      if (captcha.type === 'hcaptcha') {
        captchaRef?.current?.resetCaptcha()
      } else {
        captchaRef?.current?.reset()
      }
    }

    setCaptcha({ ...captcha, reset })
  })

  const handleOnVerify = (response) => {
    setCaptcha({ ...captcha, response })
    onVerify(response)
  }

  return (
    <div className={styles.root}>
      {captcha.type === 'hcaptcha' && (
        <ReactHcaptcha ref={captchaRef} theme='light' sitekey={captcha.site_key} onVerify={handleOnVerify} />
      )}

      {captcha.type === 'recaptcha' && (
        <ReactGoogleRecaptcha ref={captchaRef} theme='light' sitekey={captcha.site_key} onChange={handleOnVerify} />
      )}
    </div>
  )
}

Captcha.propTypes = {
  onVerify: PropTypes.func.isRequired,
}

export default Captcha
