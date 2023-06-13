import LoveSVG from './love.svg'
import GiftSVG from './gift.svg'
import ParachuteSVG from './parachute.svg'
import CatSVG from './cat.svg'
import FoodSVG from './food.svg'
import BiodegradableSVG from './biodegradable.svg'
import ConfettiSVG from './confetti.svg'
import DogSVG from './dog.svg'
import KnowledgeSVG from './knowledge.svg'
import RingsSVG from './rings.svg'
import WaterSVG from './water.svg'
import NatureSVG from './nature.svg'

export const fallbackAvatar = {
  name: 'nature',
  img: <img src={NatureSVG} alt='avatar of three trees' className='w-full h-full' />,
}

export const avatarMap = {
  love: <img src={LoveSVG} alt='avatar of a heart' />,
  gift: <img src={GiftSVG} alt='avatar of a boxed gift' />,
  parachute: <img src={ParachuteSVG} alt='avatar of a first aid box parachuting down' />,
  cat: <img src={CatSVG} alt='avatar of a cat' />,
  dog: <img src={DogSVG} alt='avatar of a dog' />,
  food: <img src={FoodSVG} alt='avatar of a heart on a plate' />,
  biodegradable: <img src={BiodegradableSVG} alt='avatar of green leaves in a circle' />,
  confetti: <img src={ConfettiSVG} alt='avatar of confetti' />,
  knowledge: <img src={KnowledgeSVG} alt='avatar of an apple on a stack of books' />,
  rings: <img src={RingsSVG} alt='avatar of two engagement rings' />,
  water: <img src={WaterSVG} alt='avatar of a water drop' />,
}

export const avatarMapWithFallback = {
  nature: fallbackAvatar.img,
  ...avatarMap,
}
