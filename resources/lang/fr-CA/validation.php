<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => "Le champ :attribute n'est pas une URL valide.",
    'after' => 'Le champ :attribute doit être une date postérieure à :date.',
    'alpha' => 'Le champ :attribute ne peut contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne peut contenir que des lettres, des chiffres et des tirets.',
    'alpha_num' => 'Le champ :attribute ne peut contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'before' => 'Le champ :attribute doit être une date antérieure à :date.',
    'between' => [
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'file' => 'Le champ :attribute doit être compris entre :min et :max kilo-octets.',
        'string' => 'Le champ :attribute doit avoir entre :min et :max caractères.',
        'array' => 'Le champ :attribute doit avoir entre :min et :max éléments.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation :attribute ne correspond pas.',
    'date' => "Le champ :attribute n'est pas une date valide.",
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'different' => 'Le champ :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit avoir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit avoir entre :min et :max chiffres.',
    'email' => 'Le champ :attribute doit être une adresse courriel valide.',
    'exists' => "Le champ :attribute sélectionné n'est pas valide.",
    'filled' => 'Le champ :attribute est obligatoire.',
    'image' => 'Le champ :attribute doit être une image.',
    'in' => "Le champ :attribute sélectionné n'est pas valide.",
    'integer' => 'Le champ :attribute doit être un entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'json' => 'Le champ :attribute doit être une chaîne de charactères JSON valide.',
    'max' => [
        'numeric' => 'Le champ :attribute ne peut pas être supérieur à :max.',
        'file' => 'Le champ :attribute ne doit pas dépasser :max kilo-octets.',
        'string' => 'Le champ :attribute ne peut pas avoir plus de :max caractères.',
        'array' => 'Le champ :attribute ne peut pas avoir plus de :max éléments.',
    ],
    'mimes' => 'Le champ :attribute doit être un fichier de type: :values.',
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
        'file' => "Le champ :attribute doit être d'au moins :min kilo-octets.",
        'string' => 'Le champ :attribute doit comporter au moins :min caractères.',
        'array' => 'Le champ :attribute doit avoir au moins :min éléments.',
    ],
    'multiple_of' => 'Le champ :attribute doit être un multiple de :value',
    'not_in' => "Le champ :attribute sélectionné n'est pas valide.",
    'not_regex' => "Le format :attribute n'est pas valide.",
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'password' => 'Le mot de passe est incorrect.',
    'present' => 'Le champ :attribute doit être présent.',
    'profanity' => 'Le champ :attribute contient un langage que nous estimons inapproprié - veuillez reformuler.',
    'regex' => "Le format :attribute n'est pas valide.",
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire lorsque :other vaut :value.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with' => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_with_all' => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_without' => "Le champ :attribute est obligatoire lorsque :values n'est pas présent.",
    'required_without_all' => "Le champ :attribute est obligatoire lorsqu'aucune des valeurs :values n'est présente.",
    'same' => 'Les champs :attribute et :other doivent correspondre.',
    'size' => [
        'numeric' => 'Le champ :attribute doit être :size.',
        'file' => 'Le champ :attribute doit être :size en kilo-octets.',
        'string' => 'Les champs :attribute doivent avoir :size caractères.',
        'array' => 'Le champ :attribute doit contenir des :size éléments.',
    ],
    'string' => 'Le champ :attribute doit être une chaîne.',
    'timezone' => 'Le champ :attribute doit être une zone valide.',
    'unique' => 'Le champ :attribute a déjà été pris.',
    'url' => "Le format :attribute n'est pas valide.",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'page_photo_custom.required' => 'Vous devez téléverser une image.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'payment_method_id' => 'mode de paiement',
        'billing_firstname' => 'prénom pour facturation',
        'billing_lastname' => 'nom de famille pour facturation',
        'billing_email' => 'adresse courriel de facturation',
        'billing_address1' => 'adresse de facturation',
        'billing_address2' => 'adresse de facturation 2',
        'billing_city' => 'ville de facturation',
        'billing_state' => 'province / région de facturation',
        'billing_zip' => 'code postal de facturation',
        'billing_country' => 'pays de facturation',
    ],
];
