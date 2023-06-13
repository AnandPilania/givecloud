import { memo } from 'react'
import { motion } from 'framer-motion'
import PropTypes from 'prop-types'
import styles from './AnimatedText.scss'

const getListsOfWords = (text) => {
  const splitWords = text.split(' ')
  const words = []

  for (const [, item] of splitWords.entries()) {
    words.push(item.split(''))
  }

  words.forEach((word) => word.push('\u00A0'))
  return words
}

const AnimatedText = ({ as: Element, text, className }) => {
  const characterAnimation = {
    hidden: {
      y: '200%',
    },
    visible: {
      y: 0,
      transition: { ease: [0.455, 0.03, 0.515, 0.955], duration: 0.75 },
    },
  }

  const renderEachCharacter = (index) =>
    getListsOfWords(text)
      [index].flat()
      .map((el, idx) => (
        <span key={idx} className={styles.character}>
          <motion.span style={{ display: 'inline-block' }} variants={characterAnimation}>
            {el}
          </motion.span>
        </span>
      ))

  return (
    <Element aria-label={text} className={className}>
      {getListsOfWords(text).map((_, index) => (
        <span key={index} className={styles.word}>
          {renderEachCharacter(index)}
        </span>
      ))}
    </Element>
  )
}

AnimatedText.propTypes = {
  as: PropTypes.string.isRequired,
  text: PropTypes.string.isRequired,
  className: PropTypes.string,
}

export default memo(AnimatedText)
