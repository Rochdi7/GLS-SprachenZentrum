<?php

return [
    'meta' => [
        'title' => 'Inscription aux cours d’allemand | GLS Sprachenzentrum',
        'description' => 'Inscrivez-vous en ligne à un cours d’allemand GLS : choisissez votre centre, votre niveau et vos horaires. Réponse rapide de notre équipe.',
    ],

    'header' => [
        'title' => 'Inscription GLS',
        'subtitle' => 'Complétez les étapes pour envoyer votre demande',
    ],

    'progress' => [
        'steps' => [
            'step1' => 'Informations',
            'step2' => 'Centre GLS',
            'step3' => 'Groupe',
            'step4' => 'Préférences',
        ],
    ],

    'fields' => [
        'nom' => [
            'label' => 'Nom',
            'placeholder' => 'Votre nom',
        ],
        'prenom' => [
            'label' => 'Prénom',
            'placeholder' => 'Votre prénom',
        ],
        'name' => [
            'label' => 'Nom complet',
            'placeholder' => 'Votre nom complet',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Téléphone',
            'placeholder' => '+212 650-123456',
        ],
        'adresse' => [
            'label' => 'Adresse',
            'placeholder' => 'Votre adresse complète',
        ],
        'type_cours' => [
            'label' => 'Type de cours',
            'placeholder' => 'Choisissez un type',
        ],
        'type_cours_options' => [
            'presentiel' => 'Cours présentiel',
            'en_ligne' => 'Cours en ligne',
        ],
        'centre' => [
            'label' => 'Centre GLS préféré',
            'placeholder' => 'Sélectionner un centre',
        ],
        'group_id' => [
            'label' => 'Groupe',
            'placeholder' => 'Sélectionner un groupe',
        ],
        'niveau' => [
            'label' => 'Niveau d\'Allemand',
            'placeholder' => 'Sélectionner un niveau',
        ],
        'horaire_prefere' => [
            'label' => 'Horaire de cours',
            'placeholder' => 'Auto rempli',
        ],
        'date_start' => [
            'label' => 'À partir de...',
            'placeholder' => 'Sélectionner une date',
        ],
        'accept_terms' => [
            'label' => 'J\'accepte les',
            'link' => 'conditions générales',
        ],
    ],

    'buttons' => [
        'prev' => 'Retour',
        'next' => 'Continuer',
        'submit' => 'Envoyer',
        'sending' => 'Envoi en cours...',
        'cancel' => 'Annuler',
        'back_home' => 'Retour à l\'accueil',
    ],

    'messages' => [
        'success_title' => 'Merci !',
        'success_text' => 'Votre demande a bien été envoyée. Notre équipe vous contactera sous peu.',
    ],

    'errors' => [
        'required_fields' => 'Veuillez remplir tous les champs obligatoires.',
        'duplicate' => 'Vous avez déjà fait une demande pour ce groupe.',
        'server_error' => 'Erreur serveur. Veuillez réessayer.',
        'connection_error' => 'Erreur de connexion. Veuillez réessayer.',
        'generic' => 'Une erreur est survenue.',
        'session_expired' => 'Session expirée. Veuillez recharger la page.',
        'check_fields' => 'Veuillez vérifier les champs du formulaire.',
    ],

    'js' => [
        'loading' => 'Chargement...',
        'error_loading' => 'Erreur de chargement',
        'select_level' => 'Sélectionner un niveau',
        'select_center' => 'Sélectionner un centre',
        'select_group' => 'Sélectionner un groupe',
        'select_date' => 'Sélectionner une date',
        'group_label' => 'Groupe',
        'group_night_label' => 'Groupe Nuit',
    ],
];
