import type { ComponentMeta } from '@storybook/react'
import type { ColumnsProps } from '@/aerosol/Columns'
import type { ColumnProps } from '@/aerosol/Column'
import { Columns } from '@/aerosol/Columns'
import { Column } from '@/aerosol/Column'
import { COLUMN_SIZES } from '@/shared/constants/theme'

export default {
  title: 'Aerosol/Columns',
  component: Columns,
  args: {
    isStackingOnMobile: true,
    isMarginless: false,
    isPaddingless: false,
    isWrapping: false,
    columnWidth: 'one',
  },
  argTypes: {
    isMarginless: {
      control: 'boolean',
      description: 'Removes margin from the Columns component',
      default: false,
    },
    isPaddingless: {
      control: 'boolean',
      description: 'Removes padding from the Column component',
      default: false,
    },
    isStackingOnMobile: {
      control: 'boolean',
      description: 'Prevents columns to stack on mobile',
      default: true,
    },
    isWrapping: {
      control: 'boolean',
      description: 'adds wrapping to the columns',
      default: false,
    },
    columnWidth: {
      description: 'The width of one Column is 1/6th of the width of the Columns component.',
      options: COLUMN_SIZES,
      control: { type: 'select' },
      default: 'three',
    },
    className: {
      control: false,
    },
  },
} as ComponentMeta<typeof Columns>

export const Default = ({
  isMarginless,
  isPaddingless,
  columnWidth,
  isWrapping,
  isStackingOnMobile,
}: ColumnProps & ColumnsProps) => (
  <>
    <Columns isWrapping isStackingOnMobile={false}>
      <Column columnWidth='small'>
        <div className='h-28 bg-red-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width small</span>
        </div>
      </Column>
      <Column columnWidth='small'>
        <div className='h-28 bg-blue-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width small</span>
        </div>
      </Column>
      <Column columnWidth='small'>
        <div className='h-28 bg-green-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width small</span>
        </div>
      </Column>
      <Column columnWidth='small'>
        <div className='h-28 bg-yellow-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width small</span>
        </div>
      </Column>
      <Column columnWidth='small'>
        <div className='h-28 bg-pink-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width small</span>
        </div>
      </Column>
      <Column columnWidth='small'>
        <div className='h-28 bg-purple-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width small</span>
        </div>
      </Column>
    </Columns>
    <Columns isMarginless={isMarginless} isWrapping={isWrapping} isStackingOnMobile={isStackingOnMobile}>
      <Column isPaddingless={isPaddingless} columnWidth={columnWidth}>
        <div className='h-28 bg-green-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width {columnWidth}</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-yellow-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
    </Columns>
    <Columns isMarginless={isMarginless} isWrapping={isWrapping} isStackingOnMobile={isStackingOnMobile}>
      <Column isPaddingless={isPaddingless} columnWidth='two'>
        <div className='h-28 bg-blue-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 2</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='four'>
        <div className='h-28 bg-red-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 4</span>
        </div>
      </Column>
    </Columns>
    <Columns isMarginless={isMarginless} isWrapping={isWrapping} isStackingOnMobile={isStackingOnMobile}>
      <Column isPaddingless={isPaddingless} columnWidth='five'>
        <div className='h-28 bg-green-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 5</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-yellow-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
    </Columns>
    <Columns isMarginless={isMarginless} isWrapping={isWrapping} isStackingOnMobile={isStackingOnMobile}>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-green-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-yellow-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-blue-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-purple-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-indigo-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
      <Column isPaddingless={isPaddingless} columnWidth='one'>
        <div className='h-28 bg-pink-500 text-white flex items-center justify-center font-bold px-2 text-center'>
          <span>Column Width 1</span>
        </div>
      </Column>
    </Columns>
  </>
)
