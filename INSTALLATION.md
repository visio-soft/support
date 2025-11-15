# Installation Guide

This guide will help you integrate the Partner Support plugin into your FilamentPHP 3 application.

## Prerequisites

- PHP 8.2 or higher
- Laravel 11.0 or 12.0
- FilamentPHP 3.0 or higher

## Step 1: Install the Package

```bash
composer require visio/support
```

## Step 2: Publish and Run Migrations

Publish the migration files:

```bash
php artisan vendor:publish --tag="support-migrations"
```

Run the migrations:

```bash
php artisan migrate
```

## Step 3: Publish Configuration (Optional)

If you want to customize the plugin configuration:

```bash
php artisan vendor:publish --tag="support-config"
```

This will create a `config/support.php` file where you can customize:
- Table names
- Status and priority options
- File attachment settings
- Middleware configuration

## Step 4: Register Resources in Your Panels

### For Partner Panel

In your partner panel provider (e.g., `app/Providers/Filament/PartnerPanelProvider.php`):

```php
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('partner')
        // ... other configuration
        ->resources([
            PartnerSupportResource::class,
        ]);
}
```

### For Admin Panel

In your admin panel provider (e.g., `app/Providers/Filament/AdminPanelProvider.php`):

```php
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource;
use VisioSoft\Support\Filament\Widgets\SupportStatsWidget;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        // ... other configuration
        ->resources([
            PartnerSupportResource::class,
        ])
        ->widgets([
            SupportStatsWidget::class, // Optional: Add support statistics widget
        ]);
}
```

## Step 5: Configure Storage for Attachments

Make sure you have configured the storage disk for file attachments. By default, the plugin uses the `public` disk.

If you haven't already, link your storage:

```bash
php artisan storage:link
```

To use a different disk, update the `config/support.php` file:

```php
'attachments' => [
    'enabled' => true,
    'disk' => 'public', // Change to your preferred disk
    'path' => 'support-attachments',
    'max_size' => 10240, // KB
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
],
```

## Step 6: Customize User Model (Optional)

The plugin uses Laravel's default user model. If you're using a custom user model, make sure it's properly configured in `config/auth.php`.

## Step 7: Configure Policies (Optional)

The plugin includes built-in policies for access control. To customize the admin check logic, you can extend the policies:

```php
// In your AppServiceProvider.php
use VisioSoft\Support\Policies\PartnerSupportPolicy;

Gate::policy(PartnerSupport::class, YourCustomPolicy::class);
```

Or modify the `isAdmin()` method in the policy classes to match your application's admin logic.

## Step 8: Listen to Events (Optional)

The plugin dispatches several events that you can listen to:

- `SupportTicketCreated`: When a new ticket is created
- `SupportReplyAdded`: When a reply is added to a ticket
- `SupportTicketClosed`: When a ticket is closed
- `SupportTicketAssigned`: When a ticket is assigned to an admin

Create listeners in your application:

```php
// In EventServiceProvider.php
protected $listen = [
    \VisioSoft\Support\Events\SupportTicketCreated::class => [
        \App\Listeners\SendTicketCreatedNotification::class,
    ],
    \VisioSoft\Support\Events\SupportReplyAdded::class => [
        \App\Listeners\SendReplyNotification::class,
    ],
];
```

## Customization

### Changing Table Names

In `config/support.php`:

```php
'tables' => [
    'partner_support' => 'custom_support_table',
    'partner_support_replies' => 'custom_replies_table',
],
```

### Customizing Statuses and Priorities

You can modify the available statuses and priorities in `config/support.php` or by extending the enum classes.

### Adding Custom Fields

To add custom fields to the support tickets, extend the models and migrations:

1. Create a new migration to add your custom fields
2. Extend the `PartnerSupport` model and add the fields to `$fillable`
3. Customize the Filament resources to include your new fields

## Troubleshooting

### Issue: File uploads not working

**Solution**: Make sure you have run `php artisan storage:link` and that your storage disk is properly configured.

### Issue: Policies not working

**Solution**: Clear your application cache:
```bash
php artisan optimize:clear
```

### Issue: Resources not showing in panel

**Solution**: Make sure you've registered the resources in the correct panel provider and cleared the cache.

## Support

For issues, questions, or contributions, please visit:
https://github.com/visio-soft/support
