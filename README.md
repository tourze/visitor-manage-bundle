# Visitor Management Bundle

[English](README.md) | [中文](README.zh-CN.md)

A comprehensive Symfony bundle for managing visitors, visitor registrations, approvals, and access control in enterprise environments.

## Features

- **Visitor Registration**: Complete visitor registration with validation
- **Approval Workflow**: Multi-level approval system for visitor access
- **Pass Management**: Digital visitor passes with QR codes and expiration
- **Invitation System**: Visitor invitation management with status tracking
- **Event System**: Domain events for visitor lifecycle management
- **Logging**: Comprehensive audit trail for all visitor activities
- **Reporting**: Statistical reports and visitor analytics

## Installation

```bash
composer require tourze/visitor-manage-bundle
```

Add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    Tourze\VisitorManageBundle\VisitorManageBundle::class => ['all' => true],
];
```

## Core Entities

### Visitor
Core visitor entity with:
- Personal information (name, mobile, company, ID card)
- Visit details (reason, appointment time, contact person)
- Status tracking (pending, approved, rejected, cancelled)
- Vehicle information (optional)

### VisitorApproval
Approval workflow tracking:
- Approval status and approver information
- Rejection reasons and timestamps
- Multi-level approval support

### VisitorPass
Digital access passes:
- Unique pass codes and QR codes
- Validity periods and usage tracking
- Pass generation and validation

### VisitorInvitation
Invitation management:
- Invitation codes and expiration
- Inviter and visitor relationships
- Status transitions (pending, confirmed, rejected, cancelled)

### VisitorLog
Comprehensive audit logging:
- All visitor actions and state changes
- Operator tracking and timestamps
- Detailed remarks and context

## Services

### VisitorService
Main visitor management operations:
- Registration and updates
- Status management (approve/reject/sign-in/sign-out)
- Search and pagination

### VisitorRegistrationService
Handles visitor registration:
- Data validation and persistence
- Bulk registration support
- Registration logging

### VisitorApprovalService
Approval workflow management:
- Submit visitors for approval
- Approve/reject operations
- Batch approval processing

### VisitorPassService
Digital pass management:
- Pass generation and validation
- Usage tracking and expiration
- Pass code uniqueness

### VisitorInvitationService
Invitation lifecycle:
- Create and send invitations
- Confirmation and rejection handling
- Expiration management

### VisitorLogService
Audit trail management:
- Action logging with context
- Batch logging operations
- Query and reporting support

### VisitorValidationService
Data validation:
- Registration data validation
- Mobile number and ID card format validation
- Business rule validation

### VisitorReportService
Analytics and reporting:
- Visitor statistics and trends
- Daily/periodic reports
- Excel export functionality

## Events

The bundle dispatches domain events for integration:

- `VisitorRegisteredEvent`: When a visitor is registered
- `VisitorApprovedEvent`: When a visitor is approved
- `PassUsedEvent`: When a visitor pass is used

## Configuration

Default configuration parameters:

```yaml
# config/packages/visitor_manage.yaml
visitor_manage:
    default_pass_validity_hours: 8    # Pass validity duration
    max_invitation_days: 30           # Maximum invitation validity
    enable_auto_approval: false       # Automatic approval for certain conditions
```

## Usage Examples

### Register a Visitor

```php
use Tourze\VisitorManageBundle\DTO\VisitorRegistrationData;

$data = new VisitorRegistrationData();
$data->name = 'John Doe';
$data->mobile = '13800138000';
$data->company = 'Example Corp';
$data->reason = 'Business meeting';
$data->appointmentTime = new \DateTime('+1 day');

$visitor = $visitorRegistrationService->registerVisitor($data, $operatorId);
```

### Approve a Visitor

```php
$approval = $visitorApprovalService->approveVisitor($visitor, $approverId);
```

### Generate a Pass

```php
$pass = $visitorPassService->generatePass($visitor);
echo $pass->getPassCode(); // Unique pass code
echo $pass->getQrCodeContent(); // QR code data
```

### Search Visitors

```php
use Tourze\VisitorManageBundle\DTO\VisitorSearchCriteria;

$criteria = new VisitorSearchCriteria();
$criteria->company = 'Example Corp';
$criteria->status = VisitorStatus::APPROVED;
$criteria->page = 1;
$criteria->size = 20;

$visitors = $visitorService->searchVisitors($criteria);
```

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/visitor-manage-bundle
```

## Requirements

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM

## Technical Architecture

### Design Principles
- **Flat Service Layer**: Business logic concentrated in Service layer without additional layers
- **Anemic Domain Model**: Entities contain only data and getter/setter methods, no business logic
- **Dependency Injection**: Use Symfony DI container for service dependency management
- **Environment Variable Configuration**: All configuration read through $_ENV

### Core Features
- Complete visitor lifecycle management
- Multi-level approval workflow support
- Digital passes and QR code integration
- Comprehensive audit logging
- Flexible event system extensions
- Batch operations and performance optimization

### Extension Capabilities
- Event-based plugin architecture
- Replaceable service interface implementations
- Environment variable configuration support
- Database field extension points

## License

MIT License. See LICENSE file for details.