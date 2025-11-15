# Usage Examples

This directory contains example code showing how to customize and extend the Partner Support plugin.

## Files

### SendTicketCreatedNotification.php
Example listener that handles the `SupportTicketCreated` event. Shows how to:
- Send email notifications to admins
- Send notifications to admin users
- Log ticket creation
- Send Slack notifications

### SendReplyNotification.php
Example listener that handles the `SupportReplyAdded` event. Shows how to:
- Differentiate between admin and customer replies
- Send notifications to appropriate parties
- Handle internal notes
- Log reply activity

### CustomPartnerSupportPolicy.php
Example custom policy that extends the base `PartnerSupportPolicy`. Shows how to:
- Customize admin checking logic for different authentication systems
- Support various role/permission packages (Spatie Laravel Permission, etc.)
- Add custom authorization rules
- Override default policy methods

## How to Use These Examples

1. Copy the relevant example to your application's directory
2. Update the namespace to match your application
3. Customize the logic to fit your needs
4. Register listeners in `EventServiceProvider` or policies in `AuthServiceProvider`

## Registering Event Listeners

In `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \VisioSoft\Support\Events\SupportTicketCreated::class => [
        \App\Listeners\SendTicketCreatedNotification::class,
    ],
    \VisioSoft\Support\Events\SupportReplyAdded::class => [
        \App\Listeners\SendReplyNotification::class,
    ],
    \VisioSoft\Support\Events\SupportTicketClosed::class => [
        \App\Listeners\HandleTicketClosed::class,
    ],
    \VisioSoft\Support\Events\SupportTicketAssigned::class => [
        \App\Listeners\NotifyAssignedAdmin::class,
    ],
];
```

## Registering Custom Policies

In `app/Providers/AuthServiceProvider.php`:

```php
use App\Policies\CustomPartnerSupportPolicy;
use VisioSoft\Support\Models\PartnerSupport;

protected $policies = [
    PartnerSupport::class => CustomPartnerSupportPolicy::class,
];
```

Or using the Gate facade:

```php
use Illuminate\Support\Facades\Gate;
use App\Policies\CustomPartnerSupportPolicy;
use VisioSoft\Support\Models\PartnerSupport;

public function boot()
{
    Gate::policy(PartnerSupport::class, CustomPartnerSupportPolicy::class);
}
```
