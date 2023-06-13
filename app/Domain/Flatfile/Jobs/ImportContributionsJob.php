<?php

namespace Ds\Domain\Flatfile\Jobs;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Enums\MemberOptinSource;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\Variant;
use Ds\Services\LedgerEntryService;
use Ds\Services\MemberService;
use Ds\Services\PaymentService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ImportContributionsJob extends ImportJob
{
    protected function importRow(array $row): void
    {
        $order = Order::firstOrNew([
            'client_uuid' => $row['contribution_external_id'] ?? strtoupper(uuid()),
        ]);

        $order->invoicenumber = $row['contribution_external_id'] ?? strtoupper(uuid());
        $order->createddatetime = toUtc($row['date']);
        $order->source = 'Import';
        $order->created_at = toUtc($row['date']);
        $order->updated_at = now();
        $order->started_at = toUtc($row['date']);
        $order->ordered_at = toUtc($row['date']);
        $order->currency_code = (string) currency();
        $order->functional_currency_code = (string) currency();
        $order->is_pos = false;
        $order->dp_sync_order = (bool) $row['dp_gift_id'];
        $order->is_processed = true;
        $order->iscomplete = true;
        $order->alt_contact_id = $row['dp_donor_id'] ?? null;
        $order->alt_transaction_id = $row['dp_gift_id'] ?? null;

        // totals
        $order->subtotal = $row['subtotal_amt'] ?? 0;
        $order->dcc_total_amount = $row['dcc'] ?? 0;
        $order->totalamount = $row['total'] ?? 0;

        // payment
        $order->confirmationdatetime = toUtc($row['date']);
        $order->payment_type = $row['payment_type'];
        $order->payment_provider_id = PaymentProvider::getOfflineProviderId();

        // credit card
        if (in_array($row['payment_type'], ['Credit Card', 'Visa', 'MasterCard', 'American Express', 'Discover', 'Diners Club'])) {
            $order->confirmationnumber = $row['payment_auth'];
            $order->billingcardtype = $row['payment_type'];

        // check
        } elseif (in_array($row['payment_type'], ['Check', 'Cheque'])) {
            $order->check_number = $row['payment_auth'];
            $order->check_date = toUtc($row['date']);
            $order->check_amt = $order->totalamount;

        // cash
        } elseif ($row['payment_type'] === 'Cash') {
            $order->cash_received = $order->totalamount;
            $order->cash_change = 0;

        // other
        } else {
            $order->payment_other_reference = $row['payment_auth'];
            $order->payment_other_note = $row['payment_type'];
        }

        $order->billing_title = null;
        $order->billing_first_name = $row['first_name'];
        $order->billing_last_name = $row['last_name'];
        $order->billing_organization_name = $row['organization_name'];
        $order->billingaddress1 = $row['address_line_1'];
        $order->billingaddress2 = $row['address_line_2'];
        $order->billingcity = $row['city'];
        $order->billingstate = $row['state'];
        $order->billingzip = $row['zip'];
        $order->billingcountry = $row['country'];
        $order->billingphone = $row['phone'];
        $order->billingemail = $row['email'];

        $order->saveQuietly();

        if ($variant = $this->getVariantFromName($row)) {
            $this->createOrderItemForVariant($order, $variant);
        }

        // post-processing functions
        $order->updateAggregates()
            ->save();

        $order->saveOriginalData();

        $member = $order->createMember();

        if ($member && $row['marketing_opt_in']) {
            app(MemberService::class)
                ->setMember($member)
                ->optin(MemberOptinSource::IMPORT);
        }

        $order->applyMemberships();

        $order->grantDownloads();

        App::make(PaymentService::class)->createPaymentFromOrder($order);

        App::make(LedgerEntryService::class)->make($order);

        $order->member->saveLifeTimeTotals();
    }

    protected function getVariantFromName(array $row): ?Variant
    {
        if ($form = Product::query()->donationForms()->hashid($row['form_experience_id'])->first()) {
            return $form->variants()->where('variantname', 'Today Only')->first();
        }

        $variant = Variant::query()
            ->leftJoin(Product::table(), 'productid', 'product.id')
            ->where(DB::raw('CONCAT(code, "-", variantname)'), $row['product_name'])
            ->first();

        if ($variant) {
            return $variant;
        }

        if ($product = Product::query()->withoutDonationForms()->where('code', $row['product_name'])->first()) {
            return $product->defaultVariant;
        }

        return Variant::query()->where('variantname', $row['product_name'])->first();
    }

    protected function createOrderItemForVariant(Order $order, Variant $variant): OrderItem
    {
        $item = OrderItem::query()->firstOrNew([
            'productorderid' => $order->id,
            'productinventoryid' => $variant->id,
        ]);

        $item->qty = 1;
        $item->price = $order->subtotal;
        $item->alt_transaction_id = $order->alt_transaction_id;

        $item->saveQuietly();

        return $item;
    }
}
