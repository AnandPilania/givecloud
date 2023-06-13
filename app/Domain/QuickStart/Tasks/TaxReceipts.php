<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Concerns\IsSkippable;

class TaxReceipts extends AbstractTask
{
    use IsSkippable;

    public function title(): string
    {
        return 'Automate your Tax Receipts';
    }

    public function description(): string
    {
        return 'No envelopes needed. Let Givecloud save you time and money with automated single or consolidated tax receipts. ';
    }

    public function action(): string
    {
        return route('backend.settings.tax_receipts');
    }

    public function actionText(): string
    {
        return 'Turn on Receipts';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/1541655-tax-receipts-with-givecloud';
    }

    public function isCompleted(): bool
    {
        return $this->isSkipped() || sys_get('bool:tax_receipt_pdfs');
    }
}
