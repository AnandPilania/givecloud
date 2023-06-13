<?php

return [
    'type' => [
        'Cash' => 'Argent comptant',
        'Check' => 'Cheque',
        'ACH' => 'Compte',
        'Free' => 'Gratuit',
        'Other' => 'Autre',
        'Secure Account' => 'Voûte sécurisée',
    ],

    'description' => [
        'alternate' => 'paiement alternatif',
        'authorization' => 'autorisation',
        'ending_in' => 'se terminant par',
        'ref' => 'réf',
        'ach' => [
            'business' => 'compte d\'entreprise',
            'personal' => 'compte personnel',
        ],
        'check' => [
            'dated' => 'en date du :date',
            'date' => 'j M, Y',
        ],
        'paypal' => 'compte PayPal',
    ],

    'period' => [
        'starting' => 'débutant',
        'one_time' => 'une fois',

        'weekly' => '/sem',
        'semi_monthly' => '/2sem',
        'quarterly' => '/tri',
        'monthly' => '/mois',
        'semi_yearly' => '/6mois',
        'yearly' => '/an',
    ],

    'recurring' => [
        'description' => '{1} Vous serez facturé :description.|[2,*] Vous serez facturé :description et :other.',
        'weekly' => ':amount/:day',
        'bi_monthly' => ':amount/deux-semaines (:day)',
        'quarterly' => ':amount/trim (:day)',
        'monthly' => ':amount/mois (:day)',
        'semi_yearly' => ':amount/6mois (:day)',
        'yearly' => ':amount/an (:day)',
    ],

    'payment_was_not_successful' => "Le paiement n'a pas réussi ou nécessite d'autres mesures.",
    'payment_method_setup_not_successful' => "Impossible d'ajouter le mode de paiement. La configuration n'a pas réussi ou nécessite d'autres actions.",

    'payment_failure_friendly_messages' => [
        'fallback' => "Votre paiement n'a pas pu être traité.",
        'incorrect_number' => "Il semble que le numéro de carte de crédit fourni n'est pas valide.",
        'insufficient_funds' => "Il semble qu'il n'y ait pas assez de fonds disponibles pour traiter votre paiement. Vous êtes en bonne compagnie - cela arrive souvent.",
        'expired_card' => 'Il semble que votre carte de crédit ait expiré.',
        'pickup_card' => 'Votre banque signale que cette carte a peut-être été déclarée perdue ou volée.',
        'processing_error' => "Quel chanceux êtes-vous. Il semble qu'il y ait un problème intermittent avec le réseau de paiement.",
        'invalid_account' => "Votre banque signale qu'il y a un problème avec cette carte ou le compte auquel elle est associée.",
        'incorrect_cvc' => 'Il semble que le code de sécurité que vous avez fourni est incorrect.',
        'duplicate_transaction' => 'Ce paiement est trop identique à un paiement récent et a été refusé pour éviter une transaction en double.',
        'invalid_expiry_year' => "L'année d'expiration de la carte est incorrecte.",
        'call_issuer' => 'Il y a un problème avec cette carte ou le compte auquel elle est associée.',
        'incorrect_address' => "L'adresse que vous avez fournie ne correspond pas aux relevés bancaires.",
        'do_not_try_again' => 'Il y a un problème avec cette carte ou le compte auquel elle est associée.',
        'reenter_transaction' => "Votre banque n'a pas pu traiter ce paiement.",
        'account_number_invalid' => "Le compte bancaire que vous avez fourni n'est pas valide.",
        'routing_number_invalid' => "Le numéro d'acheminement bancaire que vous avez fourni n'est pas valide.",
        'card_not_supported' => "Votre banque n'autorise pas votre carte à effectuer ce type de paiement en ligne.",
    ],

    'payment_failure_corrective_actions' => [
        'fallback' => 'Vérifiez les informations que vous avez fournies et réessayez. Vous devrez peut-être essayer une autre carte ou un autre appareil.',
        'incorrect_number' => 'Vérifiez le numéro de carte et réessayez.',
        'insufficient_funds' => 'Rechargez votre carte et réessayez, ou essayez une autre carte.',
        'expired_card' => "Vérifiez la date d'expiration que vous avez saisie ou essayez une autre carte.",
        'pickup_card' => 'Essayez une autre carte ou contactez votre banque.',
        'processing_error' => 'Réessayez dans quelques secondes.',
        'invalid_account' => 'Essayez une autre carte ou contactez votre banque.',
        'incorrect_cvc' => 'Vérifiez le code de sécurité à 3 ou 4 chiffres au dos de votre carte et réessayez.',
        'duplicate_transaction' => "Essayez d'actualiser la page et réessayez.",
        'invalid_expiry_year' => "Vérifiez la date d'expiration que vous avez saisie et réessayez.",
        'call_issuer' => "Votre banque vous demande de la contacter ou d'essayer une autre carte.",
        'incorrect_address' => "Vérifiez l'adresse que vous avez fournie et réessayez.",
        'do_not_try_again' => "Votre banque vous demande de la contacter ou d'essayer une autre carte.",
        'reenter_transaction' => "Essayez d'actualiser la page et réessayez, ou essayez une autre carte.",
        'account_number_invalid' => 'Vérifiez le numéro de compte bancaire et réessayez.',
        'routing_number_invalid' => "Vérifiez le numéro d'acheminement bancaire et réessayez.",
        'card_not_supported' => 'Contactez votre banque ou essayez une autre carte.',
    ],
];
