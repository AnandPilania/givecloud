import { screen, render } from '@/mocks/setup'
import { mockFundraisingSettings } from '@/mocks/data'
import { DonationMethods } from './DonationMethods'

describe('<DonationMethods />', () => {
  const mockComponent = () => render(<DonationMethods />)

  it('should prefill label and link inputs when there are ways to donate', async () => {
    const { renderScreen, waitForLoadingToBeFinished } = mockComponent()
    const mockFundraisingSettingsData = mockFundraisingSettings()

    renderScreen()

    await waitForLoadingToBeFinished()

    const waysToDonateLabels = screen.getAllByRole('textbox', { name: /label/i })
    const waysToDonateLinks = screen.getAllByRole('textbox', { name: /link/i })

    expect(waysToDonateLabels[0]).toHaveValue(mockFundraisingSettingsData.orgOtherWaysToDonate[0].label)
    expect(waysToDonateLinks[0]).toHaveValue(mockFundraisingSettingsData.orgOtherWaysToDonate[0].href)

    expect(waysToDonateLabels.length).toBe(2)
    expect(waysToDonateLinks.length).toBe(2)
  })

  it('should render empty label and link inputs when there are no ways to donate', async () => {
    const { renderScreen, waitForLoadingToBeFinished, setFundraisingSettings } = mockComponent()

    setFundraisingSettings({ orgOtherWaysToDonate: [{ id: 1, label: '', href: '' }] })

    renderScreen()

    await waitForLoadingToBeFinished()

    const waysToDonateLabel = screen.getAllByRole('textbox', { name: /label/i })
    const waysToDonateLink = screen.getAllByRole('textbox', { name: /link/i })

    expect(waysToDonateLabel.length).toBe(1)
    expect(waysToDonateLink.length).toBe(1)
  })
})
