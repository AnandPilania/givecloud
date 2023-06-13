<?php

return [
    // --------
    // API
    // --------
    // cart
    'unsupported_type' => 'Type non pris en charge',
    'cannot_have_more_than_one_donation_item' => 'La quantité de dons ne peut pas être supérieure à un',
    'code_could_not_be_applied' => "Le code n'a pas pu être appliqué.",
    'item_from_another_cart' => "L'article appartient à un autre panier.",
    'unable_to_upgrade_item' => "Impossible de mettre à niveau l'item.",
    'email_already_in_use' => 'Cette adresse courriel est déjà utilisée. Connectez-vous ou utilisez une autre adresse courriel.',
    'email_already_registered' => 'Cette adresse courriel est déjà enregistrée. Essayez avec une autre.',
    // checkouts
    'cart_missing_payment' => 'Le panier nécessite un paiement.',
    'invalid_payment_method' => "Le mode de paiement sélectionné n'est pas valide.",
    'no_payment_gateway' => 'Aucune passerelle de paiement configurée.',
    'no_payment_method' => 'Aucun mode de paiement sélectionné.',
    'no_payment_provider' => 'Aucun fournisseur de paiement sélectionné.',
    'not_logged_in' => 'Vous devez être connecté pour payer avec un mode de paiement.',
    'payment_gateway_not_configured' => "La passerelle de paiement n'est pas encore configurée",
    'payment_gateway_offline' => 'Le traitement des paiements est temporairement hors ligne. Veuillez réessayer plus tard.',
    // --------
    // ACCOUNTS
    // --------
    'missing_organization_name' => "Veuillez spécifier un nom d'organisation.",
    'validation' => [
        'account_type_not_found' => "Le type de supporter spécifié n'existe pas.",
        'enter_valid_email' => 'Veuillez saisir une adresse courriel valide.',
        'email_already_registered' => "L'adresse courriel :value a déjà été enregistrée. Essayez de vous connecter en réinitialisant votre mot de passe.",
        'missing_postal_code' => 'Veuillez spécifier votre code postal.',
        'no_account_type_selected' => 'Veuillez spécifier votre type de supporter.',
        'nps_value_only_from_1_to_10' => 'Les valeurs NPS doivent se situer entre 1 et 10.',
        'password_confirmation_no_match' => 'Les mots de passe ne correspondent pas.',
        'password_length_8_characters_min' => 'Votre mot de passe doit comporter au moins 8 caractères.',
        'password_at_least_1_uppercase_lowercase_and_number' => 'Votre mot de passe doit contenir au moins une lettre majuscule / minuscule et un chiffre.',
        'postal_code_5_characters_min' => 'Votre code postal doit comporter au moins 5 caractères.',
    ],
    // auth
    'account_not_found' => 'Compte non trouvé',
    'could_not_verify_postal_code' => 'Impossible de vérifier votre code postal.',
    'incorrect_login' => 'Adresse courriel ou mot de passe incorrect.',
    'device_already_linked' => 'Votre appareil est déjà lié à un compte existant.',
    'donor_id_not_found' => "Aucune correspondance trouvée pour l'ID ':donor_id'.",
    'incorrect_login_with_jpanel_link' => 'Adresse courriel ou mot de passe incorrect. Si vous essayez de vous connecter au panneau de configuration, <a href=":jpanel_url">cliquez ici</a>',
    'invalid_signin_attempt' => "Tentative d'inscription non valide.",
    'emailed_you_a_password_reset_link' => 'Nous vous avons envoyé un lien de réinitialisation de mot de passe par courriel.',
    'emailed_you_a_temporary_password' => 'Nous vous avons envoyé un mot de passe temporaire par courriel.',
    // payment_methods
    'add_another_payment_method_before_removing_current' => "Veuillez ajouter un autre mode de paiement avant d'essayer de supprimer celui existant.",
    'payment_method_added_to_profile' => 'Un mode de paiement (:account_type se terminant par :account_last_four) a été ajouté à votre profil.',
    'payment_method_removed_to_profile' => 'Un mode de paiement (:account_type se terminant par :account_last_four) a été supprimé de votre profil.',
    // subscriptions
    'amount_greater_than_0_dollars' => 'Le montant dû doit être supérieur à 0,00 $.',
    'cannot_cancelled_expired_subscription' => 'Les abonnements expirés ne peuvent pas être annulés.',
    'cannot_cancelled_locked_subscription' => 'Les abonnements verrouillés ne peuvent pas être annulés.',
    'cannot_update_cancelled_subscription' => 'Les abonnements annulés ne peuvent pas être mis à jour.',
    'cannot_update_expired_subscription' => 'Les abonnements expirés ne peuvent pas être mis à jour.',
    'cannot_update_locked_subscription' => 'Les abonnements verrouillés ne peuvent pas être mis à jour.',
    'error_while_saving_payment_details' => "Une erreur s'est produite lors de l'enregistrement des détails du paiement récurrent.",
    'subscription_already_cancelled' => "L'abonnement est déjà annulé.",
    'subscription_not_found' => 'Abonnement introuvable.',
];
