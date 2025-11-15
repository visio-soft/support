# Architecture Documentation

## Overview

The Partner Support plugin is a comprehensive FilamentPHP 3 package that provides a complete support ticket system with separate interfaces for partners (customers) and administrators.

## Database Schema

### Tables

#### partner_support
Main table storing support tickets.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| park_id | bigint | Optional park identifier |
| user_id | bigint | User who created the ticket |
| subject | string | Ticket subject/title |
| content | text | Detailed ticket description |
| status | string | Current status (enum) |
| priority | string | Ticket priority (enum) |
| assigned_to | bigint | Admin user assigned to ticket |
| closed_at | timestamp | When ticket was closed |
| closed_by | bigint | User who closed the ticket |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

#### partner_support_replies
Table storing all replies and internal notes.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| partner_support_id | bigint | Foreign key to partner_support |
| user_id | bigint | User who created the reply |
| content | text | Reply content |
| is_admin_reply | boolean | Whether reply is from admin |
| is_internal_note | boolean | Whether reply is internal only |
| attachments | json | Array of attachment file paths |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

## Models

### PartnerSupport
Main model for support tickets.

**Relationships:**
- `user()`: BelongsTo - The customer who created the ticket
- `assignedTo()`: BelongsTo - The admin assigned to the ticket
- `closedBy()`: BelongsTo - The user who closed the ticket
- `replies()`: HasMany - All replies to the ticket
- `publicReplies()`: HasMany - Public replies (not internal notes)
- `internalNotes()`: HasMany - Internal admin notes

**Scopes:**
- `open()`: Tickets in open states
- `closed()`: Tickets in closed states
- `forUser($userId)`: Tickets created by user
- `assignedTo($userId)`: Tickets assigned to user
- `byPriority($priority)`: Tickets of specific priority

**Methods:**
- `isOpen()`: Check if ticket is open
- `isClosed()`: Check if ticket is closed
- `close($userId)`: Close the ticket
- `reopen()`: Reopen a closed ticket
- `assignTo($userId)`: Assign ticket to admin

### PartnerSupportReply
Model for ticket replies and internal notes.

**Relationships:**
- `partnerSupport()`: BelongsTo - The parent ticket
- `user()`: BelongsTo - User who created the reply

**Scopes:**
- `public()`: Public replies (visible to customers)
- `internal()`: Internal notes (admin only)
- `adminReplies()`: Replies from admins
- `customerReplies()`: Replies from customers

**Methods:**
- `hasAttachments()`: Check if reply has attachments
- `getAttachmentUrls()`: Get URLs for all attachments

## Enums

### SupportStatus
Available ticket statuses:
- `OPEN`: New ticket awaiting response
- `IN_PROGRESS`: Ticket being worked on
- `WAITING_CUSTOMER`: Waiting for customer response
- `WAITING_ADMIN`: Customer replied, waiting for admin
- `RESOLVED`: Issue resolved
- `CLOSED`: Ticket closed

### SupportPriority
Available priority levels:
- `LOW`: Low priority
- `NORMAL`: Normal priority
- `HIGH`: High priority
- `URGENT`: Urgent priority

## Filament Resources

### Partner Panel

**PartnerSupportResource**
- Allows partners to create support tickets
- Partners can only view their own tickets
- Can add replies to open tickets
- Cannot modify closed tickets

**Pages:**
- `ListPartnerSupports`: List view of user's tickets
- `CreatePartnerSupport`: Create new ticket
- `ViewPartnerSupport`: View ticket details and replies
- `EditPartnerSupport`: Edit open ticket

### Admin Panel

**PartnerSupportResource**
- Allows admins to manage all tickets
- Can assign tickets to admins
- Can change status and priority
- Can add replies and internal notes
- Can close/reopen tickets

**Pages:**
- `ListPartnerSupports`: List view with tabs and filters
- `CreatePartnerSupport`: Create ticket on behalf of user
- `ViewPartnerSupport`: View full ticket details
- `EditPartnerSupport`: Edit any ticket field

**Tabs:**
- All Tickets
- Open
- In Progress
- Waiting for Admin
- My Tickets
- Unassigned
- Closed

## Events

### SupportTicketCreated
Dispatched when a new ticket is created.

**Payload:**
- `ticket`: The created PartnerSupport model

**Use Cases:**
- Send notification to admins
- Log ticket creation
- Trigger external integrations

### SupportReplyAdded
Dispatched when a reply is added to a ticket.

**Payload:**
- `ticket`: The PartnerSupport model
- `reply`: The created PartnerSupportReply model

**Use Cases:**
- Notify customer when admin replies
- Notify assigned admin when customer replies
- Update ticket status

### SupportTicketClosed
Dispatched when a ticket is closed.

**Payload:**
- `ticket`: The closed PartnerSupport model

**Use Cases:**
- Send closure notification to customer
- Update statistics
- Archive ticket

### SupportTicketAssigned
Dispatched when a ticket is assigned to an admin.

**Payload:**
- `ticket`: The PartnerSupport model
- `assignedToUserId`: ID of assigned admin

**Use Cases:**
- Notify assigned admin
- Update workload metrics
- Log assignment

## Policies

### PartnerSupportPolicy
Controls access to support tickets.

**Methods:**
- `viewAny()`: Can view ticket list
- `view()`: Can view specific ticket
- `create()`: Can create new ticket
- `update()`: Can update ticket
- `delete()`: Can delete ticket
- `assign()`: Can assign ticket to admin
- `close()`: Can close ticket
- `reopen()`: Can reopen ticket
- `addReply()`: Can add reply
- `addInternalNote()`: Can add internal note

### PartnerSupportReplyPolicy
Controls access to replies.

**Methods:**
- `view()`: Can view reply (internal notes hidden from customers)
- `create()`: Can create reply
- `update()`: Can update own reply
- `delete()`: Can delete reply

## Widgets

### SupportStatsWidget
Dashboard widget showing support statistics.

**Metrics:**
- Open Tickets: New tickets awaiting response
- In Progress: Tickets being worked on
- Waiting for Admin: Customer replied tickets
- My Tickets: Tickets assigned to current user
- Closed Today: Tickets resolved today

## File Attachments

Files can be attached to both tickets and replies.

**Configuration:**
- Storage disk: Configurable (default: public)
- Path: Configurable (default: support-attachments)
- Max size: Configurable (default: 10MB)
- Allowed types: Configurable (default: images, PDFs, Office docs, ZIPs)

**Storage:**
- Files stored as JSON array in `attachments` column
- Files uploaded to configured disk and path
- URLs generated on-demand using Storage facade

## Workflow

### Customer Workflow
1. Customer creates ticket with subject, description, priority
2. Ticket status set to "Open"
3. Customer can view ticket and add replies
4. Customer receives notification when admin replies
5. Customer can view when ticket is closed

### Admin Workflow
1. Admin sees new ticket in "Open" tab
2. Admin can assign ticket to themselves or others
3. Status changes to "In Progress"
4. Admin adds reply to customer
5. If customer replies, status changes to "Waiting for Admin"
6. Admin can add internal notes (not visible to customer)
7. Admin closes ticket when resolved
8. Ticket moved to "Closed" tab

## Customization Points

### Extending Models
Add custom fields, relationships, or methods by extending the base models.

### Custom Policies
Override the `isAdmin()` method or extend policies to implement custom authorization logic.

### Event Listeners
Listen to events to implement custom notifications, integrations, or workflows.

### Custom Resources
Extend or replace Filament resources to customize the UI and behavior.

### Configuration
Modify `config/support.php` to customize table names, statuses, priorities, and attachment settings.

## Integration Points

### Authentication
Uses Laravel's default authentication system via `config('auth.providers.users.model')`.

### Storage
Uses Laravel's Storage facade with configurable disk.

### Notifications
Provides events for implementing custom notification systems.

### Permissions
Compatible with any role/permission package (Spatie Laravel Permission, etc.).

## Performance Considerations

### Database Indexes
- `user_id`, `assigned_to`, `status`, `priority` are indexed
- `partner_support_id` in replies table is indexed

### Query Optimization
- Uses `withCount('replies')` for efficient counting
- Scopes use indexed columns for filtering

### File Storage
- Files stored in efficient JSON format
- Lazy loading of attachment URLs

### Caching Opportunities
- Statistics widget data can be cached
- Ticket counts can be cached
- Badge counts can be cached

## Security Features

### Access Control
- Policies prevent unauthorized access
- Partners can only see their own tickets
- Internal notes hidden from customers

### Input Validation
- Required fields enforced in forms
- File upload restrictions (size, type)
- HTML sanitization in rich text editor

### Soft Deletes
- Tickets and replies use soft deletes
- Data recovery possible
- Audit trail maintained

## Testing Strategy

### Unit Tests
- Model scopes and methods
- Enum functionality
- Policy authorization logic

### Feature Tests
- Ticket creation and management
- Reply creation
- File uploads
- Status transitions

### Integration Tests
- FilamentPHP resource functionality
- Event dispatching
- Policy enforcement

## Future Enhancements

Potential areas for extension:
- Email/SMS notifications
- Ticket templates
- Auto-assignment rules
- SLA tracking
- Customer satisfaction ratings
- Multi-language support
- Ticket merging/splitting
- Canned responses
- Knowledge base integration
