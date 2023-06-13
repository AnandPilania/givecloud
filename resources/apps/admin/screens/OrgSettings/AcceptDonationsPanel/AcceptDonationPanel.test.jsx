import userEvent from '@testing-library/user-event'
import { screen, render, waitFor } from '@/mocks/setup'
import { AcceptDonationsPanel } from './AcceptDonationsPanel'
import { setConfig } from '@/utilities/config'

describe('<AcceptDonations />', () => {
  const mockComponent = () => render(<AcceptDonationsPanel />)

  describe('when the user is not a Givecloud Express customer', () => {
    beforeEach(() => setConfig({ isGivecloudExpress: false }))

    it('should render links to manage payment gateways and payment preferences', async () => {
      const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

      renderScreen()

      await waitForLoadingToBeFinished()

      expect(screen.getByRole('link', { name: 'Manage Payment Gateways' })).toBeInTheDocument()
      expect(screen.getByRole('link', { name: 'Manage Payment Preferences' })).toBeInTheDocument()
    })
  })

  describe('when the user is a Givecloud Express customer', () => {
    beforeEach(() => setConfig({ isGivecloudExpress: true }))

    describe('and Stripe is not connected', () => {
      it('should render an error alert to connect Stripe', async () => {
        const { renderScreen, waitForLoadingToBeFinished } = mockComponent()

        renderScreen()

        await waitForLoadingToBeFinished()

        expect(screen.getByRole('alert')).toBeInTheDocument()
        expect(screen.getByRole('button', { name: 'Connect Stripe' })).toBeInTheDocument()

        expect(screen.queryByText('Wallet Pay (Google Pay, Apple Pay)')).not.toBeInTheDocument()
        expect(screen.queryByText('PayPal')).not.toBeInTheDocument()
      })
    })

    describe('and Stripe is connected', () => {
      it('should render payment statuses', async () => {
        const { renderScreen, waitForLoadingToBeFinished, setAcceptDonations } = mockComponent()

        setAcceptDonations({
          stripe: { isEnabled: true, isAchAllowed: false, isWalletPayAllowed: false, isMulticurrencySupported: false },
        })

        renderScreen()

        await waitForLoadingToBeFinished()

        expect(screen.queryByRole('alert')).not.toBeInTheDocument()
        expect(screen.getByText('Wallet Pay (Google Pay, Apple Pay)')).toBeInTheDocument()
        expect(screen.getByRole('switch', { name: 'toggle for wallet pay' })).not.toBeChecked()
        expect(screen.getByText('PayPal')).toBeInTheDocument()
        expect(screen.getByText('Allow Multi-Currency')).toBeInTheDocument()
      })

      it('should enable Wallet Pay when toggle is clicked', async () => {
        const { renderScreen, waitForLoadingToBeFinished, setAcceptDonations, setPatchAcceptDonations } =
          mockComponent()

        setAcceptDonations({
          stripe: { isEnabled: true, isAchAllowed: false, isWalletPayAllowed: false },
        })

        setPatchAcceptDonations({
          stripe: { isEnabled: true, isAchAllowed: false, isWalletPayAllowed: true },
        })

        renderScreen()

        await waitForLoadingToBeFinished()

        const walletPayToggle = screen.getByRole('switch', { name: 'toggle for wallet pay' })
        expect(walletPayToggle).not.toBeChecked()

        userEvent.click(walletPayToggle)

        await waitFor(() => expect(walletPayToggle).toBeChecked())
      })

      it('should open the Stripe Disconnect Dialog when Disconnect Stripe button is clicked', async () => {
        const { renderScreen, waitForLoadingToBeFinished, setAcceptDonations } = mockComponent()

        setAcceptDonations({
          stripe: { isEnabled: true, isAchAllowed: false, isWalletPayAllowed: false },
        })

        renderScreen()

        await waitForLoadingToBeFinished()

        userEvent.click(screen.getByRole('button', { name: 'Disconnect Stripe' }))

        await waitFor(() => expect(screen.getByRole('heading', { name: 'Disconnect Stripe' })).toBeInTheDocument())
      })

      it('should deactive Stripe when Disconnect button is clicked in the dialog', async () => {
        const { renderScreen, waitForLoadingToBeFinished, setAcceptDonations, setPatchDisconnectAcceptDonations } =
          mockComponent()

        setAcceptDonations({
          stripe: { isEnabled: true, isAchAllowed: false, isWalletPayAllowed: false },
        })

        setPatchDisconnectAcceptDonations({
          stripe: { isEnabled: false, isAchAllowed: false, isWalletPayAllowed: false },
        })

        renderScreen()

        await waitForLoadingToBeFinished()

        userEvent.click(screen.getByRole('button', { name: 'Disconnect Stripe' }))

        await waitFor(() => expect(screen.getByRole('heading', { name: 'Disconnect Stripe' })).toBeInTheDocument())

        userEvent.click(screen.getByRole('button', { name: 'Disconnect' }))

        await waitFor(() =>
          expect(screen.queryByRole('heading', { name: 'Disconnect Stripe' })).not.toBeInTheDocument()
        )

        expect(screen.queryByRole('alert')).toBeInTheDocument()
      })

      it('should enable multi currency when toggle is clicked', async () => {
        const { renderScreen, waitForLoadingToBeFinished, setAcceptDonations, setPatchAcceptDonations } =
          mockComponent()

        setAcceptDonations({
          stripe: { isEnabled: true, isAchAllowed: false, isWalletPayAllowed: false, isMulticurrencySupported: false },
        })

        setPatchAcceptDonations({
          stripe: { isEnabled: true, isAchAllowed: true, isWalletPayAllowed: false, isMulticurrencySupported: true },
        })

        renderScreen()

        await waitForLoadingToBeFinished()

        const multiCurrencyToggle = screen.getByRole('switch', { name: 'toggle for multi-currency' })
        expect(multiCurrencyToggle).not.toBeChecked()

        userEvent.click(multiCurrencyToggle)

        await waitFor(() => expect(multiCurrencyToggle).toBeChecked())
      })
    })
  })
})
