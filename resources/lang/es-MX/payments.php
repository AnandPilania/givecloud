<?php

return [
    'type' => [
        'Cash' => 'Efectivo',
        'Check' => 'Cheque',
        'ACH' => 'Cuenta',
        'Free' => 'Gratis',
        'Other' => 'Otro',
        'Secure Account' => 'Bóveda de seguridad',
    ],

    'description' => [
        'alternate' => 'pago alternativo',
        'authorization' => 'autorización',
        'ending_in' => 'terminando en',
        'ref' => 'ref',
        'ach' => [
            'business' => 'cuenta corporativa',
            'personal' => 'cuenta personal',
        ],
        'check' => [
            'dated' => 'con fecha :date',
            'date' => 'j M, Y',
        ],
        'paypal' => 'cuenta PayPal',
    ],

    'period' => [
        'one_time' => 'una vez',
        'starting' => 'a partir de',
        'weekly' => '/sem',
        'semi_monthly' => '/2sem',
        'quarterly' => '/cuarto',
        'monthly' => '/mes',
        'semi_yearly' => '/6mes',
        'yearly' => '/año',
    ],

    'recurring' => [
        'description' => '{1} Se le cobrará :description.|[2,*] Se le cobrará :description y :other.',
        'weekly' => ':amount/:day',
        'bi_monthly' => ':amount/bi-:day',
        'quarterly' => ':amount/tri (:day)',
        'monthly' => ':amount/mes (:day)',
        'semi_yearly' => ':amount/6mes (:day)',
        'yearly' => ':amount/año (:day)',
    ],

    'payment_was_not_successful' => 'El pago no fue exitoso o requiere más acción.',
    'payment_method_setup_not_successful' => 'No se puede agregar el método de pago. La instalación no se realizó correctamente o requiere más acciones.',

    'payment_failure_friendly_messages' => [
        'fallback' => 'Su pago no pudo ser procesado.',
        'incorrect_number' => 'Parece que el número de tarjeta de crédito proporcionado no es válido.',
        'insufficient_funds' => 'Parece que no hay suficientes fondos disponibles para procesar su pago. Estás en buena compañía, sucede a menudo.',
        'expired_card' => 'Parece que su tarjeta de crédito ha caducado.',
        'pickup_card' => 'Su banco informa que esta tarjeta puede haber sido reportada como extraviada o robada.',
        'processing_error' => 'Eres afortunado. Parece que hay un problema intermitente con la red de pago.',
        'invalid_account' => 'Tu banco informa que hay un problema con esta tarjeta o la cuenta a la que está conectada.',
        'incorrect_cvc' => 'Parece que el código de seguridad que proporcionó es incorrecto.',
        'duplicate_transaction' => 'Este pago es demasiado idéntico a un pago reciente y se rechazó para evitar una transacción duplicada.',
        'invalid_expiry_year' => 'El año de vencimiento de la tarjeta es incorrecto.',
        'call_issuer' => 'Hay un problema con esta tarjeta o con la cuenta a la que está conectada.',
        'incorrect_address' => 'La dirección que proporcionó no coincide con los registros del banco.',
        'do_not_try_again' => 'Hay un problema con esta tarjeta o con la cuenta a la que está conectada.',
        'reenter_transaction' => 'Su banco no pudo procesar este pago.',
        'account_number_invalid' => 'La cuenta bancaria que proporcionó no es válida.',
        'routing_number_invalid' => 'El número de ruta bancaria que proporcionó no es válido.',
        'card_not_supported' => 'Su banco no permite que su tarjeta realice este tipo de pago en línea.',
    ],

    'payment_failure_corrective_actions' => [
        'fallback' => 'Vuelva a verificar la información que proporcionó y vuelva a intentarlo. Es posible que deba probar con una tarjeta o dispositivo diferente.',
        'incorrect_number' => 'Vuelva a comprobar el número de tarjeta e inténtelo de nuevo.',
        'insufficient_funds' => 'Recarga tu tarjeta e inténtalo de nuevo, o prueba con otra tarjeta.',
        'expired_card' => 'Verifique la fecha de vencimiento que ingresó o pruebe con una tarjeta diferente.',
        'pickup_card' => 'Pruebe con una tarjeta diferente o póngase en contacto con su banco.',
        'processing_error' => 'Vuelva a intentarlo en un par de segundos.',
        'invalid_account' => 'Pruebe con una tarjeta diferente o póngase en contacto con su banco.',
        'incorrect_cvc' => 'Verifique el código de seguridad de 3 o 4 dígitos que se encuentra en el reverso de su tarjeta y vuelva a intentarlo.',
        'duplicate_transaction' => 'Intente actualizar la página y vuelva a intentarlo.',
        'invalid_expiry_year' => 'Compruebe la fecha de caducidad que ha introducido y vuelva a intentarlo.',
        'call_issuer' => 'Su banco quiere que se comunique con ellos o pruebe con otra tarjeta.',
        'incorrect_address' => 'Verifique la dirección que proporcionó y vuelva a intentarlo.',
        'do_not_try_again' => 'Su banco quiere que se comunique con ellos o pruebe con otra tarjeta.',
        'reenter_transaction' => 'Intente actualizar la página y vuelva a intentarlo, o pruebe con una tarjeta diferente.',
        'account_number_invalid' => 'Compruebe el número de cuenta bancaria e inténtelo de nuevo.',
        'routing_number_invalid' => 'Verifique el número de ruta del banco y vuelva a intentarlo.',
        'card_not_supported' => 'Póngase en contacto con su banco o pruebe con una tarjeta diferente.',
    ],
];
