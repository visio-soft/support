# Partner Support - FilamentPHP 3 Plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/visio-soft/support.svg?style=flat-square)](https://packagist.org/packages/visio-soft/support)
[![Total Downloads](https://img.shields.io/packagist/dt/visio-soft/support.svg?style=flat-square)](https://packagist.org/packages/visio-soft/support)

A comprehensive FilamentPHP 3 plugin for managing partner support tickets with separate panels for partners and administrators.

## Features

- **Partner Panel**: Partners can create and manage their support tickets
- **Admin Panel**: Administrators can view, assign, and respond to support tickets
- **Status Management**: Track tickets through various states (Open, In Progress, Waiting for Customer, etc.)
- **Priority Levels**: Set ticket priorities (Low, Normal, High, Urgent)
- **File Attachments**: Upload and download attachments with tickets and replies
- **Internal Notes**: Admin-only internal notes for team collaboration
- **Assignment System**: Assign tickets to specific admin users
- **Rich Text Editing**: Full WYSIWYG editor for ticket descriptions and replies
- **Filtering & Tabs**: Filter tickets by status, priority, assignment, etc.
- **Laravel 12 Compatible**: Built for the latest Laravel version

## Installation

You can install the package via composer:

```bash
composer require visio-soft/support
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="support-migrations"
php artisan migrate
```

Optionally, you can publish the config file:

```bash
php artisan vendor:publish --tag="support-config"
```

## Configuration

The config file `config/support.php` allows you to customize:

- Table names
- Status and priority options
- File attachment settings (disk, path, max size, allowed types)
- Middleware for partner and admin panels

## Usage

### Partner Panel

Register the partner resource in your partner panel provider:

```php
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->resources([
            PartnerSupportResource::class,
        ]);
}
```

### Admin Panel

Register the admin resource in your admin panel provider:

```php
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->resources([
            PartnerSupportResource::class,
        ]);
}
```

## Database Schema

### partner_support table
- `id`: Primary key
- `park_id`: Optional park identifier
- `user_id`: User who created the ticket
- `subject`: Ticket subject
- `content`: Ticket description
- `status`: Current status (open, in_progress, waiting_customer, waiting_admin, resolved, closed)
- `priority`: Ticket priority (low, normal, high, urgent)
- `assigned_to`: Admin user assigned to the ticket
- `closed_at`: When the ticket was closed
- `closed_by`: User who closed the ticket
- `created_at`, `updated_at`, `deleted_at`: Timestamps

### partner_support_replies table
- `id`: Primary key
- `partner_support_id`: Foreign key to partner_support
- `user_id`: User who created the reply
- `content`: Reply content
- `is_admin_reply`: Whether the reply is from an admin
- `is_internal_note`: Whether the reply is an internal note (admin-only)
- `attachments`: JSON array of attachment file paths
- `created_at`, `updated_at`, `deleted_at`: Timestamps

## Models

### PartnerSupport

```php
// Scopes
PartnerSupport::open()->get(); // Get open tickets
PartnerSupport::closed()->get(); // Get closed tickets
PartnerSupport::forUser($userId)->get(); // Get tickets for specific user
PartnerSupport::assignedTo($userId)->get(); // Get tickets assigned to user
PartnerSupport::byPriority('high')->get(); // Get tickets by priority

// Methods
$ticket->isOpen(); // Check if ticket is open
$ticket->isClosed(); // Check if ticket is closed
$ticket->close($userId); // Close the ticket
$ticket->reopen(); // Reopen the ticket
```

### PartnerSupportReply

```php
// Scopes
PartnerSupportReply::public()->get(); // Get public replies
PartnerSupportReply::internal()->get(); // Get internal notes
PartnerSupportReply::adminReplies()->get(); // Get admin replies
PartnerSupportReply::customerReplies()->get(); // Get customer replies

// Methods
$reply->hasAttachments(); // Check if reply has attachments
$reply->getAttachmentUrls(); // Get URLs for all attachments
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Visio Soft](https://github.com/visio-soft)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.