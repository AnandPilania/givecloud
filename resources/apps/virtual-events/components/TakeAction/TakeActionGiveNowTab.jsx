import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import IframeResizer from 'iframe-resizer-react'
import styles from '@/components/TakeAction/TakeActionGiveNowTab.scss'

const TakeActionGiveNowTab = ({
  domain,
  productCode,
  themeStyle,
  themePrimaryColor,
  hide = false,
  productSummary = '',
}) => {
  const url = `https://${domain}/embed/donation/${productCode}`
  const search = `?theme=${themeStyle}&primaryColor=${themePrimaryColor}&summary=${
    productSummary || ''
  }`
  const src = `${url}${search}`

  return (
    <div className={classnames(styles.root, hide && styles.hide)}>
      <IframeResizer
        width='100%'
        src={src}
        style={{
          width: '1px',
          minWidth: '100%',
          overflow: 'scroll',
        }}
      />
    </div>
  )
}

TakeActionGiveNowTab.propTypes = {
  domain: PropTypes.string.isRequired,
  productCode: PropTypes.string.isRequired,
  themeStyle: PropTypes.string.isRequired,
  themePrimaryColor: PropTypes.string.isRequired,
  hide: PropTypes.bool,
  productSummary: PropTypes.string,
}

export default memo(TakeActionGiveNowTab)
