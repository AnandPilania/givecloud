<?php

return [
    'accepted' => 'El :attribute debe ser aceptado.',
    'active_url' => 'El :attribute no es una URL válida.',
    'after' => 'El :attribute debe ser una fecha después de: fecha.',
    'alpha' => 'El :attribute solo puede contener letras.',
    'alpha_dash' => 'El :attribute solo puede contener letras, números y guiones.',
    'alpha_num' => 'El :attribute solo puede contener letras y números.',
    'array' => 'El :attribute debe ser una matriz.',
    'before' => 'El :attribute debe ser una fecha anterior a :date.',
    'between' => [
        'numeric' => 'El :attribute debe estar entre :min y :max.',
        'file' => 'El :attribute debe estar entre :min y :max kilobytes.',
        'string' => 'El :attribute debe estar entre :min y :max caracteres.',
        'array' => 'El :attribute debe tener entre :min y :max artículos.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de atributo no coincide.',
    'date' => 'El :attribute no es una fecha válida.',
    'date_format' => 'El :attribute no coincide con el formato :format.',
    'different' => 'El :attribute y :other deben ser diferentes.',
    'digits' => 'El :attribute debe ser :digits dígitos.',
    'digits_between' => 'El :attribute debe estar entre :min y :max dígitos.',
    'email' => 'El :attribute debe ser una dirección de correo electrónico válida.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'filled' => 'El campo de atributo es obligatorio.',
    'image' => 'El :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado no es válido.',
    'integer' => 'El :attribute debe ser un entero.',
    'ip' => 'El :attribute debe ser una dirección IP válida.',
    'json' => 'El :attribute debe ser una cadena JSON válida.',
    'max' => [
        'numeric' => 'El :attribute no puede ser mayor que :max.',
        'file' => 'El :attribute no puede ser mayor que :max kilobytes.',
        'string' => 'El :attribute no puede ser mayor que caracteres :max.',
        'array' => 'El :attribute no puede tener más de elementos :max.',
    ],
    'mimes' => 'El :atributo debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => 'El :atributo debe ser al menos :min.',
        'file' => 'El :attribute debe ser al menos :min kilobytes.',
        'string' => 'El :attribute debe tener al menos caracteres :min.',
        'array' => 'El :attribute debe tener al menos elementos :min.',
    ],
    'multiple_of' => 'El :attribute debe ser un múltiplo de :value',
    'not_in' => 'El :attribute seleccionado no es válido.',
    'not_regex' => 'El formato del atributo no es válido.',
    'numeric' => 'El :attribute debe ser un número.',
    'password' => 'La contraseña es incorrecta.',
    'present' => 'El campo de atributo debe estar presente.',
    'profanity' => 'El campo :attribute contiene un lenguaje que creemos que es inapropiado; reformule la redacción.',
    'regex' => 'El formato del atributo no es válido.',
    'required' => 'El campo de atributo es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando los :values no están presentes.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de los :values está presente.',
    'same' => 'El :attribute y :other deben coincidir.',
    'size' => [
        'numeric' => 'El :attribute debe ser :size.',
        'file' => 'El :attribute debe ser :size kilobytes.',
        'string' => 'El :attribute debe ser :size caracteres.',
        'array' => 'El :attribute debe contener :size artículos.',
    ],
    'string' => 'El :attribute debe ser una cadena.',
    'timezone' => 'El :attribute debe ser una zona válida.',
    'unique' => 'El :attribute ya se ha tomado.',
    'url' => 'El formato del atributo no es válido.',

    'custom' => [
        'page_photo_custom.required' => 'Debes subir una imagen.',
    ],

    'attributes' => [
        'payment_method_id' => 'método de pago',
        'billing_firstname' => 'nombre de facturación',
        'billing_lastname' => 'apellido de facturación',
        'billing_email' => 'Correo Electrónico de Facturas',
        'billing_address1' => 'Dirección de Envio',
        'billing_address2' => 'dirección de facturación 2',
        'billing_city' => 'ciudad de facturación',
        'billing_state' => 'estado de cuenta',
        'billing_zip' => 'zip de facturación',
        'billing_country' => 'país de facturación',
    ],
];
