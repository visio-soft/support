<?php

return [
    /*
     * The model to use for partner support tickets
     */
    'models' => [
        'partner_support' => \VisioSoft\Support\Models\PartnerSupport::class,
        'partner_support_reply' => \VisioSoft\Support\Models\PartnerSupportReply::class,
    ],

    /*
     * The table names
     */
    'tables' => [
        'partner_support' => 'partner_support',
        'partner_support_replies' => 'partner_support_replies',
    ],

    /*
     * Support ticket statuses
     */
    'statuses' => [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'waiting_customer' => 'Waiting for Customer',
        'waiting_admin' => 'Waiting for Admin',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ],

    /*
     * Support ticket priorities
     */
    'priorities' => [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    /*
     * Enable file attachments
     */
    'attachments' => [
        'enabled' => true,
        'disk' => 'public',
        'path' => 'support-attachments',
        'max_size' => 10240, // KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
    ],

    /*
     * Middleware for partner panel
     */
    'partner_middleware' => ['web', 'auth'],

    /*
     * Middleware for admin panel
     */
    'admin_middleware' => ['web', 'auth'],
];
