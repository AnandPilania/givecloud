import type { FC } from 'react'
import type { LinkProps } from 'react-router-dom'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPencil } from '@fortawesome/pro-regular-svg-icons'
import { ClickableBox, ClickableBoxIcon, Column, Columns, Text, Button } from '@/aerosol'
import styles from './FormPreviewPanel.styles.scss'

type ReactLinkProps = Pick<LinkProps, 'to'>

interface Props extends ReactLinkProps {
  previewImageUrl?: string
}

const FormPreviewPanel: FC<Props> = ({ previewImageUrl, to }) => {
  return (
    <ClickableBox
      aria-label='open the visual editor to customize your form'
      isMarginless
      isFullHeight
      to={to}
      isCustomizable
      className='group'
    >
      <Columns isResponsive={false} isStackingOnMobile={false}>
        <Column className='justify-center'>
          <Text type='h5' isMarginless className='text-left'>
            Fundraising Experience
          </Text>
        </Column>
        <Column isPaddingless columnWidth='small' className='justify-center'>
          <ClickableBoxIcon icon={faPencil} placement='static' />
        </Column>
      </Columns>
      <div className={classNames(styles.imageContainer, 'group-hover:bg-slate-200 group-hover:opacity-30')}>
        <img src={previewImageUrl} alt='' />
      </div>
      <Button className={classNames(styles.button, 'group-hover:block')}>
        <FontAwesomeIcon icon={faPencil} className='mr-2' />
        Customize
      </Button>
    </ClickableBox>
  )
}

export { FormPreviewPanel }
