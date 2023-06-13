<?php

namespace Ds\Domain\Commerce\Contracts;

interface ApplePayMerchantDomains
{
    /**
     * Register an Apple Pay Merchant Domain.
     *
     * @see https://developer.apple.com/documentation/apple_pay_on_the_web/maintaining_your_environment
     */
    public function registerApplePayMerchantDomain(string $domain): bool;

    public function getAppleDeveloperMerchantIdDomainAssociationFile(): string;
}
