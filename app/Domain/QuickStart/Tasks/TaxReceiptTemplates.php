<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Models\TaxReceiptTemplate;
use Illuminate\Support\Str;

class TaxReceiptTemplates extends AbstractTask
{
    public function title(): string
    {
        return 'Personalize the Tax Receipt Template';
    }

    public function description(): string
    {
        return 'Customize your tax receipt by adding your organization\'s information and branding.';
    }

    public function action(): string
    {
        return route('backend.settings.tax_receipts');
    }

    public function actionText(): string
    {
        return 'Customize Receipts';
    }

    public function dependsOn(): array
    {
        return [
            TaxReceipts::class,
        ];
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/1541655-tax-receipts-with-givecloud';
    }

    public function isCompleted(): bool
    {
        if (! app(TaxReceipts::class)->isCompleted()) {
            return false;
        }

        return TaxReceiptTemplate::query()
            ->where('template_type', 'template')
            ->get()
            ->filter(function (TaxReceiptTemplate $taxReceiptTemplate) {
                return ! Str::contains($taxReceiptTemplate->body, '555 Test Address') &&
                    ! Str::contains($taxReceiptTemplate->body, 'XXXXXXXXXXX');
            })->isNotEmpty();
    }

    public function isSkipped(): bool
    {
        return app(TaxReceipts::class)->isSkipped();
    }
}
