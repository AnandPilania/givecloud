import { render } from '@testing-library/react'
import { RecoilRoot } from 'recoil'
import { TopBarMenu } from '@/screens/Layout/TopBar/TopBarMenu'
import { TopBarMenuItem } from '@/screens/Layout/TopBar/TopBarMenuItem'
import { TopBarMenuUpdates } from '@/screens/Layout/TopBar/TopBarMenuUpdates'
import { TopBarMenuHelp } from '@/screens/Layout/TopBar/TopBarMenuHelp'
import { TopBarMenuUser } from '@/screens/Layout/TopBar/TopBarMenuUser'
import useTimeOfDay from '@/hooks/useTimeOfDay'
import { setConfig } from '@/utilities/config'
import { MockComponent } from '@/utilities/MockComponent'

jest.mock('@/screens/Layout/TopBar/TopBarMenuItem', () => ({
  TopBarMenuItem: jest.fn((props) => MockComponent(props)),
}))

jest.mock('@/screens/Layout/TopBar/TopBarMenuUpdates', () => ({
  TopBarMenuUpdates: jest.fn((props) => MockComponent(props)),
}))

jest.mock('@/screens/Layout/TopBar/TopBarMenuHelp', () => ({
  TopBarMenuHelp: jest.fn((props) => MockComponent(props)),
}))

jest.mock('@/screens/Layout/TopBar/TopBarMenuUser', () => ({
  TopBarMenuUser: jest.fn((props) => MockComponent(props)),
}))

jest.mock('@/hooks/useTimeOfDay', () => jest.fn())

afterEach(() => {
  jest.clearAllMocks()
})

test('renders expected number of TopBarMenuItems', () => {
  const topBarMenuItemInstances = TopBarMenuItem.mock.calls

  render(
    <RecoilRoot>
      <TopBarMenu />
    </RecoilRoot>
  )

  expect(topBarMenuItemInstances).toHaveLength(3)
})

test('renders TopBarMenuItem for updates', () => {
  const updates = [
    {
      id: 1,
      type: 'new',
      headline: 'headline',
      summary: 'summary',
      is_beta: false,
      is_new: true,
    },
  ]

  setConfig({ updates })

  render(
    <RecoilRoot>
      <TopBarMenu />
    </RecoilRoot>
  )

  const topBarMenuItemInstances = TopBarMenuItem.mock.calls
  const props = topBarMenuItemInstances[0][0]

  expect(props).toMatchObject({
    icon: 'bell',
    badge: updates.length,
  })

  expect(TopBarMenuUpdates).toHaveBeenCalled()
})

test('renders TopBarMenuItem for help', () => {
  render(
    <RecoilRoot>
      <TopBarMenu />
    </RecoilRoot>
  )

  const topBarMenuItemInstances = TopBarMenuItem.mock.calls
  const props = topBarMenuItemInstances[1][0]

  expect(props.icon).toEqual('book-open')
  expect(props.label).toEqual('Help')

  expect(TopBarMenuHelp).toHaveBeenCalled()
})

test('renders TopBarMenuItem for user', () => {
  const mockTimeOfDay = 'Morning'
  const userFirstName = 'Firstname'

  useTimeOfDay.mockReturnValueOnce(mockTimeOfDay)

  setConfig({ userFirstName })

  render(
    <RecoilRoot>
      <TopBarMenu />
    </RecoilRoot>
  )

  const topBarMenuItemInstances = TopBarMenuItem.mock.calls
  const props = topBarMenuItemInstances[2][0]

  expect(props.icon).toEqual('user-circle')
  expect(props.label).toEqual(`${mockTimeOfDay}, ${userFirstName}`)
  expect(TopBarMenuUser).toHaveBeenCalled()
})
