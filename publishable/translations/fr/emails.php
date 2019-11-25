<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Confirmation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the email broker for a email confirmation attempt
    | has failed, such as for an invalid token or invalid user.
    |
    */

    'confirmed' => 'Votre adresse courriel est maintenant confirmé!',
    'unconfirmed' => 'Vous devez valider votre email avant de pouvoir accéder à ce site. Si vous n\'avez pas reçu le courriel de confirmation, veuillez s\'il vous plaît consulter votre dossier de spams. Si vous avez besoin d\'un nouveau un email de confirmation, <a href="' . route('email.resend') . '" class="alert-link">cliquez ici</a>.', // phpcs:ignore Generic.Files.LineLength.TooLong
    'sent' => 'Nous avons envoyé votre lien de comfirmation de courriel!',
    'token' => "Ce jeton de confirmation de courriel n'est pas valide ou est expiré.",
    'user' => "Aucun utilisateur n'a été trouvé avec cette adresse courriel.",
    'throttled' => 'Veuillez attendre avant de ré-essayer.',

];
