import { useEffect } from 'react'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTrashCan } from '@fortawesome/pro-regular-svg-icons'
import { Button, Column, Columns, Input, Text, Tooltip } from '@/aerosol'
import { useFundraisingSettingsState } from '@/screens/OrgSettings/FundraisingPanel/useFundraisingSettingsState'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './DonationMethods.scss'

const getId = () => crypto.randomUUID()

const DonationMethods = () => {
  const { extraSmall } = useTailwindBreakpoints()
  const { fundraisingValue, setFundraisingValue } = useFundraisingSettingsState()

  useEffect(() => {
    const numWaysToDonate = fundraisingValue.orgOtherWaysToDonate.length

    const { label, href } = fundraisingValue.orgOtherWaysToDonate[numWaysToDonate - 1] ?? {}

    const hasLastFieldsFilled = !!label && !!href

    if (hasLastFieldsFilled && numWaysToDonate <= 5) addDonationMethod()
  }, [fundraisingValue.orgOtherWaysToDonate])

  const addDonationMethod = () => {
    setFundraisingValue({
      ...fundraisingValue,
      orgOtherWaysToDonate: [...fundraisingValue.orgOtherWaysToDonate, { id: getId(), label: '', href: '' }],
    })
  }

  const handleChange = ({ name, value, id }) => {
    const updatedWaysToDonate = fundraisingValue.orgOtherWaysToDonate.map((item) =>
      item.id === id ? { ...item, [name]: value } : item
    )

    setFundraisingValue({
      ...fundraisingValue,
      orgOtherWaysToDonate: updatedWaysToDonate,
    })
  }

  const removeDonationMethod = ({ id }) => {
    const resetFirstItem = () =>
      fundraisingValue.orgOtherWaysToDonate.map((item) => {
        return { ...item, label: '', href: '' }
      })

    const removeItem = () => fundraisingValue.orgOtherWaysToDonate.filter((item) => item.id !== id)

    const isOnlyItem = fundraisingValue.orgOtherWaysToDonate.length <= 1

    const updatedWaysToDonate = isOnlyItem ? resetFirstItem() : removeItem()

    setFundraisingValue({
      ...fundraisingValue,
      orgOtherWaysToDonate: updatedWaysToDonate,
    })
  }

  const renderDonationMethods = () => {
    const isRemovable = (index) =>
      index === fundraisingValue.orgOtherWaysToDonate.length - 1 && fundraisingValue.orgOtherWaysToDonate.length > 1

    const tooltipContent = (
      <Text isMarginless isBold>
        {`We automatically delete empty fields when you save.`}
      </Text>
    )

    return fundraisingValue.orgOtherWaysToDonate.map(({ href, id, label }, index) => {
      const hasHiddenLabels = index > 0 && extraSmall.greaterThan
      return (
        <Columns key={id} isResponsive={false} isStackingOnMobile={false} className={styles.columns}>
          <div className={styles.inputContainer}>
            <Column>
              <Input
                className='mb-3'
                isMarginless
                isLabelHidden={hasHiddenLabels}
                aria-label={`label ${index + 1} for other ways to donate`}
                charCountMax={30}
                id={getId()}
                label='Label'
                name='label'
                value={label || ''}
                onChange={({ target: { name, value } }) => handleChange({ name, value, id })}
              />
            </Column>
            <Column>
              <Input
                isMarginless={extraSmall.greaterThan}
                isLabelHidden={hasHiddenLabels}
                aria-label={`link ${index + 1} for other ways to donate`}
                addOn='https://'
                id={getId()}
                label='Link'
                name='href'
                value={href || ''}
                onChange={({ target: { name, value } }) => {
                  handleChange({ name, value, id })
                }}
              />
            </Column>
          </div>
          <Column columnWidth='small' className={classnames(hasHiddenLabels ? 'mt-4' : 'self-center')} isPaddingless>
            <Tooltip isHidden={!isRemovable(index)} tooltipContent={tooltipContent}>
              <Button
                aria-label='remove donation method'
                theme='error'
                onClick={() => removeDonationMethod({ id })}
                isClean
                isDisabled={isRemovable(index)}
              >
                <FontAwesomeIcon icon={faTrashCan} size='lg' />
              </Button>
            </Tooltip>
          </Column>
        </Columns>
      )
    })
  }

  return <>{renderDonationMethods()}</>
}

export { DonationMethods }
