<?php

return [
    'meta' => [
        'title' => 'Enrol in a German course | GLS Sprachenzentrum',
        'description' => 'Enrol online in a GLS German course: choose your centre, level and schedule. Quick reply from our team.',
    ],

    'header' => [
        'title' => 'GLS Registration',
        'subtitle' => 'Complete the steps to submit your request',
    ],

    'progress' => [
        'steps' => [
            'step1' => 'Information',
            'step2' => 'GLS Center',
            'step3' => 'Group',
            'step4' => 'Preferences',
        ],
    ],

    'fields' => [
        'nom' => [
            'label' => 'Last Name',
            'placeholder' => 'Your last name',
        ],
        'prenom' => [
            'label' => 'First Name',
            'placeholder' => 'Your first name',
        ],
        'name' => [
            'label' => 'Full Name',
            'placeholder' => 'Your full name',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'Phone',
            'placeholder' => '+212 650-123456',
        ],
        'adresse' => [
            'label' => 'Address',
            'placeholder' => 'Your full address',
        ],
        'type_cours' => [
            'label' => 'Course Type',
            'placeholder' => 'Choose a type',
        ],
        'type_cours_options' => [
            'presentiel' => 'In-Person Course',
            'en_ligne' => 'Online Course',
        ],
        'centre' => [
            'label' => 'Preferred GLS Center',
            'placeholder' => 'Select a center',
        ],
        'group_id' => [
            'label' => 'Group',
            'placeholder' => 'Select a group',
        ],
        'niveau' => [
            'label' => 'German Level',
            'placeholder' => 'Select a level',
        ],
        'horaire_prefere' => [
            'label' => 'Course Schedule',
            'placeholder' => 'Auto filled',
        ],
        'date_start' => [
            'label' => 'Start Date',
            'placeholder' => 'Select a date',
        ],
        'accept_terms' => [
            'label' => 'I accept the',
            'link' => 'terms and conditions',
        ],
    ],

    'buttons' => [
        'prev' => 'Back',
        'next' => 'Continue',
        'submit' => 'Submit',
        'sending' => 'Sending...',
        'cancel' => 'Cancel',
        'back_home' => 'Back to Home',
    ],

    'messages' => [
        'success_title' => 'Thank you!',
        'success_text' => 'Your request has been sent successfully. Our team will contact you soon.',
    ],

    'errors' => [
        'required_fields' => 'Please fill in all required fields.',
        'duplicate' => 'You have already submitted a request for this group.',
        'server_error' => 'Server error. Please try again.',
        'connection_error' => 'Connection error. Please try again.',
        'generic' => 'An error occurred.',
        'session_expired' => 'Session expired. Please reload the page.',
        'check_fields' => 'Please check the form fields.',
    ],

    'js' => [
        'loading' => 'Loading...',
        'error_loading' => 'Loading error',
        'select_level' => 'Select a level',
        'select_center' => 'Select a center',
        'select_group' => 'Select a group',
        'select_date' => 'Select a date',
        'group_label' => 'Group',
        'group_night_label' => 'Night Group',
    ],
];
