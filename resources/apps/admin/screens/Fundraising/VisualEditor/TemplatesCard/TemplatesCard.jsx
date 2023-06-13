import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowLeft } from '@fortawesome/pro-regular-svg-icons'
import {
  CarouselButton,
  Columns,
  Column,
  Text,
  Box,
  Container,
  RadioTile,
  RadioButton,
  RadioGroup,
  Badge,
} from '@/aerosol'
import { templates } from './templates'
import styles from './TemplatesCard.scss'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { chunkArray } from '@/shared/utilities/chunkArray'
import StandardSVG from './svgs/standard.svg?react'
import TilesSVG from './svgs/tiles.svg?react'
import ImpactSubscriptionsSVG from './svgs/impactSubscriptions.svg?react'
import MultiFundSVG from './svgs/multiFund.svg?react'
import ImpactFirstSVG from './svgs/impactFirst.svg?react'
import SplitFundSVG from './svgs/splitFund.svg?react'
import ImpactTilesSVG from './svgs/impactTiles.svg?react'
import classNames from 'classnames'

const mappedTemplates = {
  standard: <StandardSVG className={styles.image} />,
  amount_tiles: <TilesSVG className={styles.image} />,
  impact_subscriptions: <ImpactSubscriptionsSVG className={styles.image} />,
  multi_fund: <MultiFundSVG className={styles.image} />,
  impact_first: <ImpactFirstSVG className={styles.image} />,
  split_fund: <SplitFundSVG className={styles.image} />,
  impact_tiles: <ImpactTilesSVG className={styles.image} />,
}

const TemplatesCard = () => {
  const { templateValue, setTemplateState, setDefaultAmountState, defaultAmountValue } = useFundraisingFormState()

  const renderBadge = (isAvailable) => (!isAvailable ? <Badge theme='secondary'>coming soon</Badge> : null)

  const staticContent = (
    <Columns isResponsive={false} isStackingOnMobile={false}>
      <Column columnWidth='six' className={styles.static}>
        <CarouselButton
          className={styles.button}
          aria-label='go back to template and branding'
          isClean
          indexToNavigate={0}
        >
          <FontAwesomeIcon icon={faArrowLeft} />
        </CarouselButton>
        <Text isMarginless type='h3' isBold>
          Select a template
        </Text>
      </Column>
    </Columns>
  )

  const renderEmptyDivs = (numberOfDivs) =>
    numberOfDivs.map((_, index) => (
      <Column key={index}>
        <Columns isResponsive={false}>
          <Column />
        </Columns>
      </Column>
    ))

  const handleChange = (template) => {
    setTemplateState({
      ...templateValue,
      template,
    })
    setDefaultAmountState({
      ...defaultAmountValue,
      isCustomAmountValuesTouched: {},
      isCustomAmountInputTouched: false,
    })
  }

  const renderTemplate = (template) => {
    const { type, title, subtitle, isAvailable } = template
    return (
      <Column key={title}>
        <RadioGroup
          isDisabled={!isAvailable}
          label='templates'
          onChange={() => handleChange(template)}
          checkedValue={templateValue.template.type}
          name='templates'
          showInput={false}
          isLabelVisible={false}
        >
          <RadioButton isMarginless id={type} value={type}>
            <RadioTile className='border-0'>
              <Columns isResponsive={false}>
                <Column className={classNames(styles.column)}>{mappedTemplates[type]}</Column>
                <Column>
                  <Text isBold type='h5'>
                    {title}
                  </Text>
                  <Text isSecondaryColour>{subtitle}</Text>
                  {renderBadge(isAvailable)}
                </Column>
              </Columns>
            </RadioTile>
          </RadioButton>
        </RadioGroup>
      </Column>
    )
  }

  const renderRow = (row, index) => {
    const remainder = 2 - row.length
    const remainderArray = !!remainder ? Array.from(Array(remainder).keys()) : []

    return (
      <Columns isResponsive={false} key={index}>
        {row.map((template) => renderTemplate(template))}
        {remainderArray.length ? renderEmptyDivs(remainderArray) : null}
      </Columns>
    )
  }
  return (
    <Box className={styles.root}>
      <Container
        adjustHeight={320}
        isScrollable
        isScrollShadowVisible
        isTopBarVisible={false}
        staticContent={staticContent}
        containerWidth='full'
      >
        {chunkArray(templates).map((templateRow, index) => renderRow(templateRow, index))}
      </Container>
    </Box>
  )
}

export { TemplatesCard }
