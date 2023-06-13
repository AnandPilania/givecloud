import type { ComponentType } from 'react'
import classNames from 'classnames'
import { Fragment } from 'react'
import { Combobox, Transition } from '@headlessui/react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faSearch, faSpinner, faTrash, IconDefinition } from '@fortawesome/pro-regular-svg-icons'
import { Text } from '@/aerosol/Text'
import { Button } from '@/aerosol/Button'
import styles from './CommandInput.styles.scss'

export type SelectedType<T> = T | string
type ExtractProps<T> = T extends ComponentType<infer P> ? P : T
type HeadlessInputProps = ExtractProps<typeof Combobox.Input>

interface CommandInputProps<T> {
  setQuery?: (value: string) => void
  errors?: string[]
  isOptional?: boolean
  isDisabled?: boolean
  isLabelHidden?: boolean
  selected?: SelectedType<T>
  isQueryEmpty?: boolean
  isLoading?: boolean
  icon?: IconDefinition
  customOption?: Record<string, string>
  query?: string
  name: string
  setSelected: (value: SelectedType<T>) => void
  onChange: (value: string) => void
  displayValue: (value: SelectedType<T>) => string
}

type Props<T> = CommandInputProps<T> & Omit<HeadlessInputProps, 'displayValue'>

const CommandInput = <T,>({
  isLoading,
  onChange,
  customOption,
  query,
  icon = faSearch,
  errors,
  isQueryEmpty,
  isDisabled,
  isLabelHidden,
  isOptional,
  label,
  name,
  children,
  selected,
  setSelected,
  ...rest
}: Props<T>) => {
  const hasErrors = !!errors?.filter((error) => !!error).length

  const renderErrorMessages = () => {
    if (hasErrors)
      return errors.slice(0, 1).map((error, index) => (
        <p key={`error-item-${error}-${index}`} data-testid={`error-${index}`} className={styles.errorMessage}>
          {error}
        </p>
      ))
  }

  const renderCustomOption = () => {
    if (customOption) {
      return (
        <Combobox.Option
          value={customOption}
          className={({ active }) => classNames(styles.option, active && styles.active)}
        >
          <Text isMarginless>
            Nothing found, Add <span className='font-bold'>"{query}"</span> ?
          </Text>
        </Combobox.Option>
      )
    }
    return (
      <Combobox.Option value='nothing found' disabled className={styles.option}>
        {({ selected }) => (
          <Text isMarginless isBold={selected}>
            Nothing found
          </Text>
        )}
      </Combobox.Option>
    )
  }

  const renderOption = () => {
    if (isQueryEmpty) {
      return renderCustomOption()
    }

    return <>{children}</>
  }

  const renderLabel = () => {
    if (label && !isLabelHidden)
      return (
        <Combobox.Label
          htmlFor={name}
          className={classNames(styles.label, isDisabled && styles.disabled, hasErrors && styles.error)}
        >
          {label}
          {isOptional && <span className={classNames(styles.optional)}>optional</span>}
        </Combobox.Label>
      )
    return (
      <label htmlFor={name} className='sr-only'>
        {label || name}
      </label>
    )
  }

  const renderOptions = () => {
    if (isLoading) {
      return (
        <Combobox.Options className={classNames('h-60', styles.options)}>
          {[...new Array(7)].map((_, i) => (
            <Combobox.Option value='loading' key={i} disabled className={styles.option}>
              <div className={styles.optionLoading}>
                <FontAwesomeIcon icon={faSpinner} spin />
              </div>
            </Combobox.Option>
          ))}
        </Combobox.Options>
      )
    }
    return <Combobox.Options className={styles.options}>{renderOption()}</Combobox.Options>
  }

  const renderAction = () =>
    !!selected ? (
      <Button
        onClick={() => setSelected('')}
        aria-label='delete selected option'
        isClean
        theme='error'
        size='small'
        className={styles.deleteButton}
      >
        <FontAwesomeIcon icon={faTrash} />
      </Button>
    ) : (
      <FontAwesomeIcon
        aria-hidden='true'
        className={classNames(styles.icon, hasErrors && styles.errorIcon)}
        icon={icon}
      />
    )

  return (
    <Combobox as='div' disabled={isDisabled} name={name} value={selected} onChange={setSelected} nullable>
      {renderLabel()}
      <div className={classNames(styles.root, isDisabled ? 'cursor-not-allowed' : 'cursor-text')}>
        <Combobox.Input
          {...rest}
          name={name}
          className={classNames(styles.input, isDisabled && styles.disabled, hasErrors && styles.error)}
          onChange={({ target }) => onChange(target.value)}
        />
        <Combobox.Button className={styles.button} />

        {renderAction()}
        {renderErrorMessages()}
        <Transition
          as={Fragment}
          leave='transition ease-in duration-100'
          leaveFrom='opacity-100'
          leaveTo='opacity-0'
          afterLeave={() => onChange('')}
        >
          {renderOptions()}
        </Transition>
      </div>
    </Combobox>
  )
}

export { CommandInput }

export { Props as CommandInputProps }

CommandInput.defaultProps = {
  errors: [],
  isDisabled: false,
  isOptional: false,
  isLabelHidden: false,
  isQueryEmpty: false,
  icon: faSearch,
}
