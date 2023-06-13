<?php

namespace Ds\Enums;

class CardBrand
{
    public const AMEX = 'amex';
    public const AMERICANEXPRESS = 'americanexpress';
    public const CARTEBLANCHE = 'carteblanche';
    public const CHINAUNIONPAY = 'chinaunionpay';
    public const DINERSCLUB = 'dinersclub';
    public const DISCOVER = 'discover';
    public const ELO = 'elo';
    public const JCB = 'jcb';
    public const LASER = 'laser';
    public const MAESTRO = 'maestro';
    public const MASTERCARD = 'mastercard';
    public const SOLO = 'solo';
    public const SWITCH = 'switch';
    public const UNIONPAY = 'unionpay';
    public const VISA = 'visa';
    public const VISAELECTRON = 'visaelectron';

    public static function all(): array
    {
        return [
            self::AMEX,
            self::AMERICANEXPRESS,
            self::CARTEBLANCHE,
            self::CHINAUNIONPAY,
            self::DINERSCLUB,
            self::DISCOVER,
            self::ELO,
            self::JCB,
            self::LASER,
            self::MAESTRO,
            self::MASTERCARD,
            self::SOLO,
            self::SWITCH,
            self::UNIONPAY,
            self::VISA,
            self::VISAELECTRON,
        ];
    }

    public static function labels(): array
    {
        return [
            self::AMEX => 'American Express',
            self::AMERICANEXPRESS => 'American Express',
            self::CARTEBLANCHE => 'Carte Blanche',
            self::CHINAUNIONPAY => 'China UnionPay',
            self::DINERSCLUB => 'Diners Club',
            self::DISCOVER => 'Discover',
            self::ELO => 'Elo',
            self::JCB => 'JCB',
            self::LASER => 'Laser',
            self::MAESTRO => 'Maestro',
            self::MASTERCARD => 'MasterCard',
            self::SOLO => 'Solo',
            self::SWITCH => 'Switch',
            self::UNIONPAY => 'UnionPay',
            self::VISA => 'Visa',
            self::VISAELECTRON => 'Visa',
            null => 'Unknown',
        ];
    }
}
