<?php

namespace Ds\Domain\DoubleTheDonation\Enums;

class Status
{
    public const WAITING_FOR_DONOR_ACTION = ':waiting-for-donor-action';
    public const WAITING_FOR_VERIFICATION = ':waiting-for-verification';
    public const PENDING_PAYMENT = ':pending-payment';
    public const MATCH_COMPLETE = ':match-complete';
    public const UNKNOWN_EMPLOYER = ':unknown-employer';
    public const INELIGIBLE = ':ineligible';

    public static function cases(): array
    {
        return [
            self::WAITING_FOR_DONOR_ACTION => 'Waiting for Donor',
            self::WAITING_FOR_VERIFICATION => 'Match Initiated',
            self::PENDING_PAYMENT => 'Pending Payment',
            self::MATCH_COMPLETE => 'Match Complete',
            self::UNKNOWN_EMPLOYER => 'Unknown Employer',
            self::INELIGIBLE => 'Ineligible',
        ];
    }

    public static function label(?string $status): ?string
    {
        foreach (static::cases() as $key => $value) {
            if ($key === $status) {
                return $value;
            }
        }

        return null;
    }
}
