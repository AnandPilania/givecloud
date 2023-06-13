import { RecoilRoot } from 'recoil'
import { BrowserRouter } from 'react-router-dom'
import { render, screen } from '@testing-library/react'
import { SidebarFirstLevelMenu } from './SidebarFirstLevelMenu'
import { setConfig } from '@/utilities/config'

test('renders a list for first level menu', () => {
  render(
    <RecoilRoot>
      <BrowserRouter>
        <SidebarFirstLevelMenu />
      </BrowserRouter>
    </RecoilRoot>
  )

  expect(screen.getByRole('list')).toBeInTheDocument()
})

test('renders expected list items based on uiFeaturePreviewMenuItems', () => {
  const uiFeaturePreviewMenuItems = [
    {
      key: 'menu_item_1',
      label: 'Menu Item 1',
      is_active: true,
      url: '/menu/one',
    },
    {
      key: 'menu_item_2',
      label: 'Menu Item 2',
      is_active: false,
      url: '/menu/two',
      new_link: {
        label: 'Link To',
        url: '/menu/two',
      },
      children: [
        {
          key: 'sub_menu_item',
          label: 'Sub Menu Item',
          is_active: false,
          url: '/menu/three',
        },
      ],
    },
  ]

  setConfig({ uiFeaturePreviewMenuItems })

  render(
    <RecoilRoot>
      <BrowserRouter>
        <SidebarFirstLevelMenu />
      </BrowserRouter>
    </RecoilRoot>
  )

  const listItems = screen.getAllByRole('listitem')

  expect(screen.getAllByRole('listitem')).toHaveLength(uiFeaturePreviewMenuItems.length)

  listItems.forEach((_, index) => {
    const link = screen.getByText(uiFeaturePreviewMenuItems[index].label)

    expect(link).toBeInTheDocument()
    expect(link).toHaveAttribute('href', uiFeaturePreviewMenuItems[index].url)
  })
})
