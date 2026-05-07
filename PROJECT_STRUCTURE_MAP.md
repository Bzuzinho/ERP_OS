# ERP OS - Comprehensive Project Structure Map

**Project Date:** May 7, 2026  
**Framework:** Laravel 11 + Inertia.js + React + TypeScript + Tailwind CSS  
**Organization:** Multi-tenant with organization isolation

---

## Table of Contents
1. [Models](#models)
2. [Controllers](#controllers)
3. [Routes](#routes)
4. [Policies](#policies)
5. [Services & Actions](#services--actions)
6. [Database Migrations](#database-migrations)
7. [React/Inertia Pages](#reactinertia-pages)
8. [Tests](#tests)

---

## Models

### Core Identity Models
- **User.php** - Base user account model with authentication
- **Organization.php** - Multi-tenant organization container
- **Contact.php** - External contact registry
- **ContactAddress.php** - Contact address details
- **Comment.php** - Generic comment system (morphable)
- **Attachment.php** - File attachment system (morphable)
- **ActivityLog.php** - Audit trail for activities

### Ticket Management (Ticketing Domain)
- **Ticket.php**
  - Properties: reference, created_by, contact_id, assigned_to, department_id, service_area_id, team_id, category, subcategory, priority, status (novo, em_analise, aguarda_informacao, encaminhado, em_execucao, agendado, resolvido, fechado, cancelado, indeferido), title, description, location_text, source (portal, internal, phone, email, presencial), visibility, due_date, closed_at, closed_by
  - Relations: belongsTo User (creator), belongsTo Department, belongsTo ServiceArea, belongsTo Team, hasMany Task, hasMany Comment, morphMany Attachment, hasMany TicketStatusHistory

- **TicketStatusHistory.php** - Status tracking for tickets

### Task Management
- **Task.php**
  - Properties: organization_id, ticket_id, space_reservation_id, assigned_to, created_by, title, description, status (pending, in_progress, waiting, done, cancelled), priority, start_date, due_date, completed_at, completed_by
  - Relations: belongsTo Organization, belongsTo Ticket, belongsTo SpaceReservation, belongsTo User (assignee, creator, completedBy), hasMany TaskChecklist, morphMany Attachment, morphMany Comment

- **TaskChecklist.php** - Task checklist container
- **TaskChecklistItem.php** - Individual checklist items

### Event Management
- **Event.php**
  - Properties: organization_id, space_id, title, description, event_type (meeting, appointment, visit, activity, maintenance, assembly, reservation), status (scheduled, confirmed, cancelled, completed), start_at, end_at, location_text, created_by, related_ticket_id, related_contact_id, visibility (public, internal, restricted)
  - Relations: belongsTo Space, belongsTo User (creator), belongsTo Ticket (related), belongsTo Contact (related), hasMany EventParticipant, hasMany MeetingMinute, hasMany SpaceReservation, morphMany Attachment, morphMany Comment

- **EventParticipant.php** - Event participation tracking (attendance_status: invited, confirmed, declined, attended, absent)

### Space Management
- **Space.php**
  - Properties: organization_id, name, slug, description, location_text, capacity, status (available, unavailable, maintenance, inactive), requires_approval, has_cleaning_required, has_deposit, deposit_amount, price, rules, is_public, is_active
  - Relations: hasMany SpaceReservation, hasMany SpaceMaintenanceRecord, hasMany SpaceCleaningRecord, hasMany InventoryMovement (related_space_id), hasMany OperationalPlan (related_space_id), hasMany RecurringOperation (related_space_id)

- **SpaceReservation.php**
  - Properties: organization_id, space_id, requested_by_user_id, contact_id, event_id, status (requested, approved, rejected, cancelled, completed), start_at, end_at, purpose, notes, internal_notes, approved_by, approved_at, rejected_by, rejected_at, rejection_reason, cancelled_by, cancelled_at, cancellation_reason
  - Relations: belongsTo Space, belongsTo User (requestedBy), belongsTo Contact, belongsTo Event, hasMany Task, hasMany SpaceReservationApproval, morphMany Attachment

- **SpaceReservationApproval.php** - Multi-level approval tracking
- **SpaceMaintenanceRecord.php** - Maintenance log for spaces
- **SpaceCleaningRecord.php** - Cleaning log for spaces

### Inventory Management
- **InventoryCategory.php** - Inventory categorization
- **InventoryLocation.php** - Physical storage locations
- **InventoryItem.php**
  - Properties: organization_id, inventory_category_id, inventory_location_id, name, slug, description, sku, item_type (consumable, equipment, vehicle, tool, furniture, document, other), unit (unit, box, pack, liter, kg, meter, hour, day, other), current_stock, minimum_stock, maximum_stock, unit_cost, status (active, inactive, damaged, lost, maintenance, retired), is_stock_tracked, is_loanable, is_active
  - Relations: belongsTo InventoryCategory, belongsTo InventoryLocation, hasMany InventoryMovement, hasMany InventoryLoan, hasMany InventoryRestockRequest

- **InventoryMovement.php** - Stock movement tracking (entry, exit, transfer)
- **InventoryLoan.php** - Equipment loan records
- **InventoryRestockRequest.php** - Restock request workflow
- **InventoryBreakage.php** - Damage/loss reporting

### Document Management
- **Document.php**
  - Properties: organization_id, document_type_id, title, description, file_path, file_name, original_name, mime_type, size, uploaded_by, visibility (public, portal, internal, restricted), related_type, related_id, current_version, status (draft, active, archived, cancelled), is_active
  - Relations: belongsTo DocumentType, belongsTo User (uploader), hasMany DocumentVersion, hasMany DocumentAccessRule, morphTo related, hasOne MeetingMinute, morphMany Attachment, morphMany Comment

- **DocumentType.php** - Document type classification
- **DocumentVersion.php** - Version control for documents
- **DocumentAccessRule.php** - Fine-grained access control

- **MeetingMinute.php**
  - Properties: organization_id, event_id, document_id, title, summary, status (draft, reviewed, approved, archived), approved_at, approved_by, created_by
  - Relations: belongsTo Event, belongsTo Document, belongsTo User (creator, approvedBy)

### HR Management
- **Employee.php**
  - Properties: organization_id, user_id, department_id, employee_number, role_title, employment_type (permanent, contract, temporary, volunteer, external, other), start_date, end_date, phone, emergency_contact_name, emergency_contact_phone, notes, is_active
  - Relations: belongsTo User, belongsTo Department, hasMany TeamMember, belongsToMany Team, hasMany EmployeeSchedule, hasMany AttendanceRecord, hasMany EmployeeEventAssignment, hasMany EmployeeTaskAssignment, hasMany LeaveRequest

- **Department.php** - Organizational departments
- **Team.php** - Team grouping within departments
- **TeamMember.php** - Team membership tracking (with role, joined_at, left_at, is_active)

- **AbsenceType.php** - Absence type definitions
- **AttendanceRecord.php** - Daily attendance tracking
- **EmployeeSchedule.php** - Employee work schedules
- **EmployeeEventAssignment.php** - Event participation assignment
- **EmployeeTaskAssignment.php** - Task assignment tracking
- **LeaveRequest.php** - Time-off request workflow

### Planning & Operations
- **OperationalPlan.php**
  - Properties: organization_id, title, slug, description, plan_type (activity, maintenance, cleaning, public_event, inspection, campaign, project, emergency, administrative, other), status (draft, pending_approval, approved, scheduled, in_progress, completed, cancelled, archived), visibility (public, portal, internal, restricted), start_date, end_date, owner_user_id, department_id, team_id, related_ticket_id, related_space_id, budget_estimate, progress_percent, approved_by, approved_at, cancelled_by, cancelled_at, cancellation_reason, completed_by, completed_at, created_by
  - Relations: belongsTo Organization, belongsTo User (owner, creator), belongsTo Department, belongsTo Team, hasMany OperationalPlanTask, hasMany OperationalPlanParticipant, hasMany OperationalPlanResource, morphMany Attachment, morphMany Comment

- **OperationalPlanTask.php** - Tasks within operational plan
- **OperationalPlanParticipant.php** - Team members involved
- **OperationalPlanResource.php** - Resource allocation

- **RecurringOperation.php**
  - Properties: organization_id, title, description, operation_type (task, event, maintenance, cleaning, inspection, other), status (active, paused, completed, cancelled), frequency (daily, weekly, monthly, yearly), interval, weekdays (array), day_of_month, start_date, end_date, next_run_at, last_run_at, owner_user_id, department_id, team_id, related_space_id, task_template (array), event_template (array), created_by
  - Relations: belongsTo Organization, belongsTo User, belongsTo Department, belongsTo Team, hasMany RecurringOperationRun

- **RecurringOperationRun.php** - Individual execution instances

### Notification System
- **Notification.php**
  - Properties: organization_id, type, title, message, notifiable_type, notifiable_id, action_url, priority, data (array), created_by
  - Relations: belongsTo Organization, belongsTo User (creator), hasMany NotificationRecipient, belongsToMany User (through notification_recipients), morphTo notifiable

- **NotificationRecipient.php** - User-specific notification receipt (with seen_at, read_at, archived_at)

### Supporting Models
- **ServiceArea.php** - Service department/area grouping
- **User.php** - Extended with roles and permissions

---

## Controllers

### Admin Controllers (app/Http/Controllers/Admin/)

#### Ticketing Domain
- **TicketController.php** - CRUD for tickets
- **TicketStatusController.php** - Status updates
- **TicketCommentController.php** - Ticket comments
- **TicketAttachmentController.php** - File attachments

#### Task Management
- **TaskController.php** - CRUD for tasks (includes complete action)
- **TaskStatusController.php** - Status workflow
- **TaskChecklistController.php** - Checklist management
- **TaskChecklistItemController.php** - Checklist items

#### Event Management
- **EventController.php** - CRUD for events
- **EventStatusController.php** - Status updates
- **EventParticipantController.php** - Participant management

#### Space Management
- **SpaceController.php** - CRUD for spaces
- **SpaceStatusController.php** - Space status
- **SpaceReservationController.php** - Reservation management
- **SpaceReservationApprovalController.php** - Approval/rejection workflow
- **SpaceReservationCancellationController.php** - Cancellation handling
- **SpaceMaintenanceRecordController.php** - Maintenance logs
- **SpaceMaintenanceStatusController.php** - Maintenance status
- **SpaceCleaningRecordController.php** - Cleaning logs
- **SpaceCleaningStatusController.php** - Cleaning status

#### Inventory Management
- **InventoryCategoryController.php** - Category management
- **InventoryLocationController.php** - Location management
- **InventoryItemController.php** - Item CRUD
- **InventoryItemStatusController.php** - Status updates
- **InventoryMovementController.php** - Movement tracking
- **InventoryLoanController.php** - Loan management
- **InventoryLoanReturnController.php** - Return processing
- **InventoryRestockRequestController.php** - Restock requests
- **InventoryRestockApprovalController.php** - Approval workflow
- **InventoryBreakageController.php** - Damage reporting
- **InventoryBreakageResolutionController.php** - Resolution workflow

#### Document Management
- **DocumentController.php** - CRUD for documents
- **DocumentTypeController.php** - Type management
- **DocumentVersionController.php** - Version control
- **DocumentAccessRuleController.php** - Access rules
- **DocumentDownloadController.php** - Download handling
- **MeetingMinuteController.php** - Meeting minutes
- **MeetingMinuteApprovalController.php** - Approval workflow

#### Planning & Operations
- **OperationalPlanController.php** - CRUD
- **OperationalPlanStatusController.php** - Status workflow
- **OperationalPlanApprovalController.php** - Approval
- **OperationalPlanCancellationController.php** - Cancellation
- **OperationalPlanCompletionController.php** - Completion
- **OperationalPlanParticipantController.php** - Participant management
- **OperationalPlanResourceController.php** - Resource allocation
- **OperationalPlanTaskController.php** - Task association
- **RecurringOperationController.php** - CRUD
- **RecurringOperationStatusController.php** - Status
- **RecurringOperationRunController.php** - Run management

#### HR Management (Admin/Hr/)
- **EmployeeController.php** - Employee management
- **DepartmentController.php** - Department management
- **TeamController.php** - Team management
- **AbsenceTypeController.php** - Absence types
- **AttendanceRecordController.php** - Attendance tracking
- **LeaveRequestController.php** - Leave workflow

#### Settings Management (Admin/Settings/)
- **UserController.php** - User management
- **UserPasswordController.php** - Password reset
- **UserStatusController.php** - User activation/deactivation
- **RoleController.php** - Role management
- **SettingsController.php** - System settings
- **OrganizationController.php** - Organization settings

#### Other Admin Controllers
- **DashboardController.php** - Admin dashboard with KPIs
- **ContactController.php** - Contact management
- **ServiceAreaController.php** - Service area management
- **ServiceAreaUserController.php** - Service area user assignment
- **NotificationController.php** - Notification management
- **NotificationReadController.php** - Mark notifications as read
- **ReportController.php** - Report generation
- **ReportExportController.php** - CSV/Excel export
- **AttachmentDownloadController.php** - Attachment downloads

### Portal Controllers (app/Http/Controllers/Portal/)
- **DashboardController.php** - Portal user dashboard
- **TicketController.php** - Citizen ticket creation/viewing
- **TicketCommentController.php** - Public ticket comments
- **TicketAttachmentController.php** - Ticket file uploads
- **EventController.php** - Public event viewing
- **SpaceController.php** - Space browsing
- **SpaceReservationController.php** - Reservation requests
- **SpaceReservationCancellationController.php** - Cancellation
- **DocumentController.php** - Document access
- **DocumentDownloadController.php** - Document downloads
- **MeetingMinuteController.php** - Meeting minute access
- **OperationalPlanController.php** - Plan visibility
- **NotificationController.php** - User notifications
- **NotificationReadController.php** - Mark read
- **AttachmentDownloadController.php** - File downloads

### Auth Controllers (app/Http/Controllers/Auth/)
- **AuthenticatedSessionController.php** - Login/logout
- **RegisteredUserController.php** - Registration
- **PasswordResetLinkController.php** - Password reset request
- **NewPasswordController.php** - Password reset completion
- **PasswordController.php** - Password update
- **EmailVerificationPromptController.php** - Email verification
- **VerifyEmailController.php** - Email verification completion
- **EmailVerificationNotificationController.php** - Resend verification
- **ConfirmablePasswordController.php** - Password confirmation

### Other Controllers
- **ProfileController.php** - User profile management

---

## Routes

### Route Files Location
- **routes/web.php** - Main application routes (AUTH + ADMIN + PORTAL)
- **routes/auth.php** - Authentication routes
- **routes/console.php** - Console commands

### Route Structure

#### Authentication Routes (routes/auth.php)
```
/register - User registration
/login - User login
/forgot-password - Password reset request
/reset-password/{token} - Password reset form
/verify-email - Email verification prompt
/verify-email/{id}/{hash} - Email verification link
/confirm-password - Confirm password before sensitive action
/logout - Logout (POST)
```

#### Root Routes (routes/web.php)
```
/ - Redirect to dashboard (admin or portal based on role)
/dashboard - Redirect to dashboard (admin or portal based on role)
/profile/edit - User profile edit
```

#### Admin Routes Prefix: /admin (middleware: auth, permission:admin.access)
```
GET  /
GET  /more

Contacts:
GET|POST|PUT|DELETE /contacts
GET /contacts/{contact}/edit

Tickets:
GET|POST|PUT|DELETE /tickets
GET /tickets/{ticket}/edit
PATCH /tickets/{ticket}/status
PATCH /tickets/{ticket}/assign
POST /tickets/{ticket}/comments
POST /tickets/{ticket}/attachments
GET /attachments/{attachment}/download

Tasks:
GET|POST|PUT|DELETE /tasks
PATCH /tasks/{task}/status
POST /tasks/{task}/complete
POST|PATCH|DELETE /tasks/{task}/checklists
POST|PATCH|DELETE /tasks/{task}/checklists/{checklist}/items

Events:
GET|POST|PUT|DELETE /events
PATCH /events/{event}/status
POST|DELETE /events/{event}/participants

Documents:
GET|POST|PUT|DELETE /documents
GET /documents/{document}/download
POST /documents/{document}/versions
POST|DELETE /documents/{document}/access-rules

Meeting Minutes:
GET|POST|PUT|DELETE /meeting-minutes
POST /meeting-minutes/{meetingMinute}/approve

Document Types:
GET|POST|PUT|DELETE /document-types

Spaces:
GET|POST|PUT|DELETE /spaces
PATCH /spaces/{space}/status

Space Reservations:
GET|POST|PUT|DELETE /space-reservations
POST /space-reservations/{spaceReservation}/approve
POST /space-reservations/{spaceReservation}/reject
POST /space-reservations/{spaceReservation}/complete
POST /space-reservations/{spaceReservation}/cancel

Space Maintenance:
GET|POST|PUT|DELETE /space-maintenance
PATCH /space-maintenance/{spaceMaintenance}/status

Space Cleaning:
GET|POST|PUT|DELETE /space-cleaning
POST /space-cleaning/{spaceCleaning}/complete

Inventory:
GET /inventory
GET|POST|PATCH|DELETE /inventory-categories
GET|POST|PATCH|DELETE /inventory-locations
GET|POST|PUT|DELETE /inventory-items
PATCH /inventory-items/{inventoryItem}/status
GET|POST /inventory-movements
GET|POST /inventory-loans
POST /inventory-loans/{inventoryLoan}/return
GET|POST /inventory-restock-requests
POST /inventory-restock-requests/{inventoryRestockRequest}/approve
POST /inventory-restock-requests/{inventoryRestockRequest}/reject
POST /inventory-restock-requests/{inventoryRestockRequest}/complete
GET|POST /inventory-breakages
POST /inventory-breakages/{inventoryBreakage}/resolve

Planning:
GET|POST|PUT|DELETE /operational-plans
PATCH /operational-plans/{operationalPlan}/status
POST /operational-plans/{operationalPlan}/approve
POST /operational-plans/{operationalPlan}/cancel
POST /operational-plans/{operationalPlan}/complete
GET|POST /operational-plans/{operationalPlan}/participants
GET|POST /operational-plans/{operationalPlan}/resources
GET|POST /operational-plans/{operationalPlan}/tasks
GET|POST|PUT|DELETE /recurring-operations
PATCH /recurring-operations/{recurringOperation}/status
GET|POST /recurring-operations/{recurringOperation}/runs

Reports:
GET /reports
GET /reports/tickets
GET /reports/tasks
GET /reports/events
GET /reports/spaces
GET /reports/inventory
GET /reports/hr
POST /reports/export

Notifications:
GET /notifications
POST /notifications/mark-all-read
POST /notifications/{notificationRecipient}/mark-read

Service Areas: (redirected to settings)
GET|POST|PUT|DELETE /service-areas
POST /service-areas/{serviceArea}/users
DELETE /service-areas/{serviceArea}/users/{userId}

Settings: (Admin/Settings/*)
GET|POST|PUT|DELETE /settings/users
POST /settings/users/{user}/password
PATCH /settings/users/{user}/status
GET|POST|PUT|DELETE /settings/roles
GET|POST|PUT|DELETE /settings/organizations
GET /settings/service-areas (redirected)
```

#### Portal Routes Prefix: /portal (middleware: auth, permission:portal.access)
```
GET  / - Dashboard

Tickets:
GET /tickets
POST /tickets
GET /tickets/{ticket}
GET /tickets/{ticket}/edit
POST /tickets/{ticket}/comments
POST /tickets/{ticket}/attachments

Events:
GET /events
GET /events/{event}

Spaces:
GET /spaces
GET /spaces/{space}

Space Reservations:
GET /space-reservations
POST /space-reservations
GET /space-reservations/{spaceReservation}
POST /space-reservations/{spaceReservation}/cancel

Documents:
GET /documents
GET /documents/{document}
GET /documents/{document}/download

Meeting Minutes:
GET /meeting-minutes
GET /meeting-minutes/{meetingMinute}

Operational Plans:
GET /operational-plans
GET /operational-plans/{operationalPlan}

Notifications:
GET /notifications
POST /notifications/{notificationRecipient}/mark-read
GET /attachments/{attachment}/download
```

#### Dedicated Route Files
- **routes/admin/hr.php** - HR-specific routes (included in admin)
- **routes/admin/planning.php** - Planning-specific routes (included in admin)
- **routes/admin/settings.php** - Settings-specific routes (included in admin)

---

## Policies

Authorization policies in `app/Policies/` (39 policy files):

### Core Policies
- **UserPolicy.php** - User access control
- **RolePolicy.php** - Role management

### Ticket & Communication Policies
- **TicketPolicy.php**
- **CommentPolicy.php**
- **AttachmentPolicy.php**

### Task Policies
- **TaskPolicy.php**
- **TaskChecklistPolicy.php** (inferred from controllers)

### Event Policies
- **EventPolicy.php**

### Space Policies
- **SpacePolicy.php**
- **SpaceReservationPolicy.php**
- **SpaceMaintenanceRecordPolicy.php**
- **SpaceCleaningRecordPolicy.php**

### Inventory Policies
- **InventoryItemPolicy.php**
- **InventoryCategoryPolicy.php**
- **InventoryLocationPolicy.php**
- **InventoryMovementPolicy.php**
- **InventoryLoanPolicy.php**
- **InventoryRestockRequestPolicy.php**
- **InventoryBreakagePolicy.php**

### Document Policies
- **DocumentPolicy.php**
- **DocumentVersionPolicy.php**
- **DocumentTypePolicy.php**
- **DocumentAccessRulePolicy.php**

### Planning Policies
- **OperationalPlanPolicy.php**
- **OperationalPlanParticipantPolicy.php**
- **OperationalPlanResourcePolicy.php**
- **OperationalPlanTaskPolicy.php**
- **RecurringOperationPolicy.php**
- **RecurringOperationRunPolicy.php**

### HR Policies
- **EmployeePolicy.php**
- **EmployeeAssignmentPolicy.php**
- **DepartmentPolicy.php**
- **AttendanceRecordPolicy.php**
- **LeaveRequestPolicy.php**
- **TeamPolicy.php**

### Other Policies
- **MeetingMinutePolicy.php**
- **NotificationPolicy.php**
- **ServiceAreaPolicy.php**
- **ContactPolicy.php**
- **AbsenceTypePolicy.php**

---

## Services & Actions

### Action Classes (75 total) - `app/Actions/`

#### Tickets Domain
- **CreateTicketAction.php** - Create new ticket
- **UpdateTicketStatusAction.php** - Status transitions
- **AssignTicketAction.php** - Assign to user/department

#### Tasks Domain
- **CreateTaskAction.php** - Create task
- **UpdateTaskStatusAction.php** - Status transitions
- **CompleteTaskAction.php** - Mark task complete

#### Spaces Domain
- **CreateSpaceAction.php**
- **UpdateSpaceStatusAction.php**
- **CreateSpaceReservationAction.php**
- **ApproveSpaceReservationAction.php**
- **RejectSpaceReservationAction.php**
- **CompleteSpaceReservationAction.php**
- **CancelSpaceReservationAction.php**
- **CreateSpaceMaintenanceRecordAction.php**
- **UpdateSpaceMaintenanceStatusAction.php**
- **CreateSpaceCleaningRecordAction.php**
- **CompleteSpaceCleaningRecordAction.php**

#### Events Domain
- **CreateEventAction.php**
- **UpdateEventStatusAction.php**

#### Inventory Domain
- **CreateInventoryItemAction.php**
- **UpdateInventoryItemStatusAction.php**
- **RegisterInventoryEntryAction.php**
- **RegisterInventoryExitAction.php**
- **RegisterInventoryTransferAction.php**
- **RegisterInventoryMovementAction.php**
- **CreateInventoryLoanAction.php**
- **ReturnInventoryLoanAction.php**
- **ReportInventoryBreakageAction.php**
- **ResolveInventoryBreakageAction.php**
- **CreateInventoryRestockRequestAction.php**
- **ApproveInventoryRestockRequestAction.php**
- **RejectInventoryRestockRequestAction.php**
- **CompleteInventoryRestockRequestAction.php**

#### Document Domain
- **CreateDocumentAction.php**
- **CreateDocumentVersionAction.php**
- **ArchiveDocumentAction.php**
- **GrantDocumentAccessAction.php**
- **RevokeDocumentAccessAction.php**
- **CreateMeetingMinuteAction.php**
- **ApproveMeetingMinuteAction.php**

#### Planning Domain
- **CreateOperationalPlanAction.php**
- **UpdateOperationalPlanStatusAction.php**
- **ApproveOperationalPlanAction.php**
- **CancelOperationalPlanAction.php**
- **CompleteOperationalPlanAction.php**
- **AttachTaskToOperationalPlanAction.php**
- **DetachTaskFromOperationalPlanAction.php**
- **GenerateTasksFromOperationalPlanAction.php**
- **CreateRecurringOperationAction.php**
- **CancelRecurringOperationAction.php**
- **PauseRecurringOperationAction.php**
- **ResumeRecurringOperationAction.php**
- **GenerateRecurringOperationRunAction.php**
- **ExecuteRecurringOperationRunAction.php**

#### HR Domain
- **CreateEmployeeAction.php**
- **UpdateEmployeeStatusAction.php**
- **CreateDepartmentAction.php**
- **CreateTeamAction.php**
- **AddEmployeeToTeamAction.php**
- **RemoveEmployeeFromTeamAction.php**
- **AssignEmployeeToEventAction.php**
- **AssignEmployeeToTaskAction.php**
- **CreateAttendanceRecordAction.php**
- **ValidateAttendanceRecordAction.php**
- **CreateLeaveRequestAction.php**
- **ApproveLeaveRequestAction.php**
- **RejectLeaveRequestAction.php**
- **CancelLeaveRequestAction.php**

#### Settings Domain
- **CreateUserAction.php**
- **UpdateUserAction.php**
- **ActivateUserAction.php**
- **DeactivateUserAction.php**
- **ResetUserPasswordAction.php**
- **UpdateUserRolesAction.php**
- **UpdateOrganizationAction.php**

### Service Classes (47 total) - `app/Services/`

#### Root Services
- **TeamService.php** - Team management utilities
- **EmployeeAssignmentService.php** - Employee assignment logic
- **EmployeeNumberGenerator.php** - Employee ID generation
- **AttendanceService.php** - Attendance utilities
- **LeaveRequestService.php** - Leave request utilities

#### Dashboard Services (`Dashboard/`)
- **AdminDashboardService.php** - Admin KPI aggregation
- **PortalDashboardService.php** - Portal KPI aggregation
- **TicketKpiService.php** - Ticket metrics
- **TaskKpiService.php** - Task metrics
- **EventKpiService.php** - Event metrics
- **SpaceKpiService.php** - Space metrics
- **InventoryKpiService.php** - Inventory metrics
- **HrKpiService.php** - HR metrics
- **DocumentKpiService.php** - Document metrics
- **PlanningKpiService.php** - Planning metrics

#### Document Services (`Documents/`)
- **DocumentStorageService.php** - File storage handling
- **DocumentVersionService.php** - Version management
- **DocumentAccessService.php** - Access control logic

#### Inventory Services (`Inventory/`)
- **InventoryStockService.php** - Stock level management
- **InventoryMovementService.php** - Movement tracking
- **InventoryLoanService.php** - Loan management
- **InventoryRestockService.php** - Restock workflow

#### Planning Services (`Planning/`)
- **OperationalPlanningDashboardService.php** - Planning dashboard
- **OperationalPlanProgressService.php** - Progress tracking
- **OperationalPlanTaskGenerator.php** - Task generation
- **RecurringOperationScheduler.php** - Scheduling logic
- **RecurringOperationExecutor.php** - Execution logic

#### Report Services (`Reports/`)
- **ReportFilterService.php** - Report filtering
- **CsvExportService.php** - CSV export
- **TicketReportService.php** - Ticket reports
- **TaskReportService.php** - Task reports
- **EventReportService.php** - Event reports
- **SpaceReportService.php** - Space reports
- **InventoryReportService.php** - Inventory reports
- **HrReportService.php** - HR reports
- **DocumentReportService.php** - Document reports
- **PlanningReportService.php** - Planning reports

#### Notification Services (`Notifications/`)
- **NotificationService.php** - Notification dispatch
- **NotificationRecipientResolver.php** - Determine recipients
- **TicketNotificationService.php** - Ticket-specific notifications
- **TicketNotificationRecipientResolver.php** - Ticket notification recipients

#### Space Services (`Spaces/`)
- **SpaceReservationService.php** - Reservation logic
- **SpaceReservationNotificationService.php** - Reservation notifications
- **SpaceAvailabilityService.php** - Availability checking
- **SpaceCleaningService.php** - Cleaning workflow

#### Ticket Services (`Tickets/`)
- **TicketReferenceGenerator.php** - Ticket reference numbering
- **ActivityLogger.php** - Ticket activity logging

---

## Database Migrations

All migrations in `database/migrations/` (35 files):

### Core Authentication & Organization
- `0001_01_01_000000_create_users_table.php`
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`
- `2026_04_30_113303_create_permission_tables.php`
- `2026_04_30_140000_create_organizations_and_update_users_table.php`
- `2026_05_05_100000_add_profile_fields_to_users_table.php`
- `2026_05_05_000001_add_extra_fields_to_organizations_table.php`

### Ticketing System
- `2026_04_30_151315_create_crm_and_ticketing_tables.php` (Ticket, Contact, Comment, Attachment, etc.)

### Task & Event Management
- `2026_04_30_151316_create_tasks_events_and_checklists_tables.php` (Task, Event, EventParticipant, TaskChecklist, etc.)

### Space Management
- `2026_04_30_151317_create_spaces_table.php`
- `2026_04_30_151319_create_space_reservations_table.php`
- `2026_04_30_151320_create_space_reservation_approvals_table.php`
- `2026_04_30_151321_create_space_maintenance_records_table.php`
- `2026_04_30_151322_create_space_cleaning_records_table.php`
- `2026_05_06_add_space_id_to_events_table.php`
- `2026_05_06_add_space_reservation_id_to_tasks_table.php`

### Inventory System
- `2026_04_30_210000_create_inventory_categories_table.php`
- `2026_04_30_210001_create_inventory_locations_table.php`
- `2026_04_30_210002_create_inventory_items_table.php`
- `2026_04_30_210003_create_inventory_movements_table.php`
- `2026_04_30_210004_create_inventory_loans_table.php`
- `2026_04_30_210005_create_inventory_restock_requests_table.php`
- `2026_04_30_210006_create_inventory_breakages_table.php`

### Document Management
- `2026_04_30_200000_create_documents_and_meeting_minutes_tables.php` (Document, DocumentType, DocumentVersion, DocumentAccessRule, MeetingMinute)

### HR Management
- `2026_05_01_000000_create_hr_tables.php` (Employee, Department, Team, TeamMember, AbsenceType, AttendanceRecord, EmployeeSchedule, LeaveRequest, EmployeeEventAssignment, EmployeeTaskAssignment)
- `2026_05_01_000001_add_department_foreign_key_to_tickets.php`

### Planning & Operations
- `2026_05_01_214220_create_operational_plans_table.php`
- `2026_05_01_214221_create_operational_plan_tasks_table.php`
- `2026_05_01_214223_create_operational_plan_participants_table.php`
- `2026_05_01_214224_create_operational_plan_resources_table.php`
- `2026_05_01_214225_create_recurring_operations_table.php`
- `2026_05_01_214226_create_recurring_operation_runs_table.php`

### Supporting Infrastructure
- `2026_05_05_120000_create_service_areas_table.php`
- `2026_05_05_120100_create_notifications_tables.php` (Notification, NotificationRecipient)
- `2026_05_05_120200_add_service_area_and_team_to_tickets_table.php`

---

## React/Inertia Pages

### Public Pages
- **Welcome.tsx** - Landing page
- **Dashboard.tsx** - Conditional dashboard (routes to admin or portal)

### Authentication Pages (`Pages/Auth/`)
- **Login.tsx** - Login form
- **Register.tsx** - User registration
- **ForgotPassword.tsx** - Password reset request
- **ResetPassword.tsx** - Password reset form
- **VerifyEmail.tsx** - Email verification
- **ConfirmPassword.tsx** - Password confirmation

### Profile Pages (`Pages/Profile/`)
- **Edit.tsx** - User profile editing
- **Partials/UpdateProfileInformationForm.tsx**
- **Partials/UpdatePasswordForm.tsx**
- **Partials/DeleteUserForm.tsx**

### Admin Pages (`Pages/Admin/`)

#### Dashboard & Navigation
- **Dashboard/Index.tsx** - Admin dashboard with KPIs
- **More/Index.tsx** - Additional admin options

#### Tickets Domain
- **Tickets/Index.tsx** - Ticket list
- **Tickets/Create.tsx** - Create ticket
- **Tickets/Show.tsx** - Ticket detail/comments
- **Tickets/Edit.tsx** - Edit ticket

#### Tasks Domain
- **Tasks/Index.tsx** - Task list
- **Tasks/Create.tsx** - Create task
- **Tasks/Show.tsx** - Task detail with checklists
- **Tasks/Edit.tsx** - Edit task

#### Events Domain
- **Events/Index.tsx** - Event list
- **Events/Create.tsx** - Create event
- **Events/Show.tsx** - Event detail
- **Events/Edit.tsx** - Edit event

#### Spaces Domain
- **Spaces/Index.tsx** - Space list
- **Spaces/Create.tsx** - Create space
- **Spaces/Show.tsx** - Space detail
- **Spaces/Edit.tsx** - Edit space
- **SpaceReservations/Index.tsx** - Reservations list
- **SpaceReservations/Create.tsx** - Create reservation
- **SpaceReservations/Show.tsx** - Reservation detail
- **SpaceReservations/Edit.tsx** - Edit reservation
- **SpaceMaintenance/Index.tsx** - Maintenance logs
- **SpaceMaintenance/Create.tsx** - Create maintenance record
- **SpaceMaintenance/Show.tsx** - Maintenance detail
- **SpaceMaintenance/Edit.tsx** - Edit maintenance
- **SpaceCleaning/Index.tsx** - Cleaning logs
- **SpaceCleaning/Create.tsx** - Create cleaning record
- **SpaceCleaning/Show.tsx** - Cleaning detail
- **SpaceCleaning/Edit.tsx** - Edit cleaning

#### Documents Domain
- **Documents/Index.tsx** - Document list
- **Documents/Create.tsx** - Create document
- **Documents/Show.tsx** - Document detail with versions
- **Documents/Edit.tsx** - Edit document
- **DocumentTypes/Index.tsx** - Document type list
- **DocumentTypes/Create.tsx** - Create type
- **DocumentTypes/Edit.tsx** - Edit type
- **MeetingMinutes/Index.tsx** - Meeting minutes list
- **MeetingMinutes/Create.tsx** (inferred) - Create minute
- **MeetingMinutes/Show.tsx** - Minute detail
- **MeetingMinutes/Edit.tsx** (inferred) - Edit minute

#### Inventory Domain
- **InventoryItems/Index.tsx** - Item list
- **InventoryItems/Create.tsx** - Create item
- **InventoryItems/Show.tsx** - Item detail
- **InventoryItems/Edit.tsx** - Edit item
- **InventoryCategories/Index.tsx** - Category list
- **InventoryCategories/Create.tsx** - Create category
- **InventoryCategories/Edit.tsx** - Edit category
- **InventoryLocations/Index.tsx** - Location list
- **InventoryLocations/Create.tsx** - Create location
- **InventoryLocations/Edit.tsx** - Edit location
- **InventoryMovements/Index.tsx** - Movement history
- **InventoryMovements/Create.tsx** - Record movement
- **InventoryMovements/Show.tsx** - Movement detail
- **InventoryLoans/Index.tsx** - Loan list
- **InventoryLoans/Create.tsx** - Create loan
- **InventoryLoans/Show.tsx** - Loan detail
- **InventoryRestockRequests/Index.tsx** - Restock requests
- **InventoryRestockRequests/Create.tsx** - Create request
- **InventoryRestockRequests/Show.tsx** - Request detail
- **InventoryBreakages/Index.tsx** - Damage reports
- **InventoryBreakages/Create.tsx** - Report damage
- **InventoryBreakages/Show.tsx** - Damage detail

#### HR Domain
- **Employees/Index.tsx** - Employee list
- **Employees/Create.tsx** - Create employee
- **Employees/Show.tsx** - Employee detail
- **Employees/Edit.tsx** - Edit employee
- **Departments/Index.tsx** - Department list
- **Departments/Create.tsx** - Create department
- **Departments/Show.tsx** - Department detail
- **Departments/Edit.tsx** - Edit department
- **Teams/Index.tsx** - Team list
- **Teams/Create.tsx** - Create team
- **Teams/Show.tsx** - Team detail
- **Teams/Edit.tsx** - Edit team
- **Attendance/Index.tsx** - Attendance tracking
- **Attendance/Create.tsx** - Record attendance
- **Attendance/Show.tsx** - Attendance detail
- **Attendance/Edit.tsx** - Edit attendance
- **AbsenceTypes/Index.tsx** (inferred) - Absence type management

#### Planning Domain
- **OperationalPlans/Index.tsx** - Plans list
- **OperationalPlans/Create.tsx** - Create plan
- **OperationalPlans/Show.tsx** - Plan detail
- **OperationalPlans/Edit.tsx** - Edit plan
- **RecurringOperations/Index.tsx** - Recurring ops list
- **RecurringOperations/Create.tsx** - Create recurring
- **RecurringOperations/Show.tsx** - Recurring detail
- **RecurringOperations/Edit.tsx** - Edit recurring

#### Other Admin Pages
- **Contacts/Index.tsx** - Contact list
- **Contacts/Create.tsx** - Create contact
- **Contacts/Show.tsx** - Contact detail
- **Contacts/Edit.tsx** - Edit contact
- **Notifications/Index.tsx** - Notification list
- **Reports/Index.tsx** - Report selection/generation (inferred)

### Portal Pages (`Pages/Portal/`)

#### Portal Navigation & Dashboard
- **Dashboard/Index.tsx** - Portal user dashboard
- **More/Index.tsx** - Additional portal options

#### Portal Tickets
- **Tickets/Index.tsx** - User's tickets
- **Tickets/Create.tsx** - Create ticket (citizen request)
- **Tickets/Show.tsx** - Ticket detail with comments

#### Portal Events
- **Events/Index.tsx** - Public events list
- **Events/Show.tsx** - Event detail

#### Portal Spaces
- **Spaces/Index.tsx** - Available spaces
- **Spaces/Show.tsx** - Space detail

#### Portal Space Reservations
- **SpaceReservations/Index.tsx** - User's reservations
- **SpaceReservations/Create.tsx** - Request reservation
- **SpaceReservations/Show.tsx** - Reservation detail

#### Portal Documents
- **Documents/Index.tsx** - Accessible documents
- **Documents/Show.tsx** - Document detail

#### Portal Meeting Minutes
- **MeetingMinutes/Index.tsx** - Accessible meeting minutes
- **MeetingMinutes/Show.tsx** - Meeting minute detail

#### Portal Operational Plans
- **OperationalPlans/Index.tsx** - Visible plans
- **OperationalPlans/Show.tsx** - Plan detail

#### Portal Notifications
- **Notifications/Index.tsx** - User notifications

---

## Tests

All tests in `tests/Feature/` (31 test files):

### Authentication Tests (`Tests/Feature/Auth/`)
- **AuthenticationTest.php** - Login functionality
- **RegistrationTest.php** - User registration
- **PasswordResetTest.php** - Password reset flow
- **PasswordUpdateTest.php** - Password changes
- **PasswordConfirmationTest.php** - Password confirmation
- **EmailVerificationTest.php** - Email verification

### Profile Tests
- **ProfileTest.php** - User profile operations

### Sprint Tests (Organized by Development Sprint)

#### Sprint 2
- **Sprint2/AdminEventsFeatureTest.php** - Admin event management
- **Sprint2/AdminTasksFeatureTest.php** - Admin task management
- **Sprint2/PortalEventsVisibilityFeatureTest.php** - Portal event visibility

#### Sprint 4
- **Sprint4/SpacesFeatureTest.php** - Space functionality

#### Sprint 5
- **Sprint5/InventoryFeatureTest.php** - Inventory management

#### Sprint 6
- **Sprint6/HrFeatureTest.php** - HR operations (employees, departments, teams, attendance, leave)

#### Sprint 7
- **Sprint7/OperationalPlanningFeatureTest.php** - Operational planning

#### Sprint 8
- **Sprint8/DashboardAndReportsFeatureTest.php** - Dashboard and reporting

#### Sprint 9
- **Sprint9/SecurityAndSmokeTest.php** - Security checks and smoke tests

#### Sprint 11
- **Sprint11/AdminTicketsFeatureTest.php** - Admin ticket management
- **Sprint11/PortalTicketsFeatureTest.php** - Portal ticket creation
- **Sprint11/AdminContactsFeatureTest.php** - Admin contact management
- **Sprint11/CommentsAndAttachmentsFeatureTest.php** - Comments and file attachments

#### Sprint 12
- **Sprint12/NotificationsFeatureTest.php** - Notification system

#### Sprint 14
- **Sprint14/NavigationStructureTest.php** - Navigation and routing

#### Sprint 15
- **Sprint15/PortalStabilizationTest.php** - Portal stability testing

#### Sprint 18
- **Sprint18/SmokeDemoUxTest.php** - Demo and UX testing

### Domain-Specific Tests

#### Ticketing Domain
- **Tickets/TicketCommunicationFlowTest.php** - Ticket workflow and communication

#### Space Reservations
- **SpaceReservations/SpaceReservationFlowTest.php** - Reservation workflow

### Security Tests
- **Security/OrganizationIsolationTest.php** - Multi-tenant isolation verification

### Settings Tests
- **Settings/UserManagementTest.php** - User management operations
- **Settings/Sprint12SettingsOrganizationTest.php** - Organization settings

### Shared Test Utilities
- **Concerns/BuildsUsersWithPermissions.php** - Helper trait for creating test users with permissions

### Other Tests
- **ExampleTest.php** - Example/template test

---

## Architecture Highlights

### Multi-Tenancy
- Organization isolation at the model level using `BelongsToOrganization` trait
- All tables include `organization_id` for data segregation
- Verified through Organization Isolation Tests

### Authorization Strategy
- Role-based access control using Spatie Laravel Permissions
- Policy classes for fine-grained authorization
- Service areas enable role assignment and grouping

### Workflow Management
- Status-based workflows for: Tickets, Tasks, Events, Spaces, SpaceReservations, Documents, MeetingMinutes, OperationalPlans, InventoryItems, etc.
- Action classes encapsulate business logic for state transitions
- Approval workflows for: SpaceReservations, MeetingMinutes, InventoryRestockRequests, OperationalPlans

### Notification System
- Morphable notifications tied to multiple entity types
- Recipient resolution services for intelligent notification routing
- Support for ticket-specific and general notifications

### Recurring Operations
- Scheduler service for managing recurring operational tasks
- Support for daily, weekly, monthly, yearly frequencies
- Template-based task/event generation

### Reporting & Analytics
- Dashboard KPI services aggregating metrics across domains
- Report filtering and CSV export capabilities
- Domain-specific report services for each major module

### Document Management
- Version control with DocumentVersion
- Access control rules for fine-grained permissions
- File storage service abstraction

### File Handling
- Morphable attachment system for tickets, tasks, documents, etc.
- Storage abstraction with DocumentStorageService
- Download controllers with security checks

### Frontend Architecture
- Inertia.js for server-driven React components
- TypeScript for type safety
- Tailwind CSS for styling
- Organized page structure by domain and action

---

## Key Dependencies & Patterns

- **Framework:** Laravel 11 with Inertia.js
- **Authorization:** Spatie Laravel Permissions (roles/permissions)
- **File Storage:** Laravel Storage (configurable filesystem)
- **HTTP Client:** Guzzle HTTP (for external APIs)
- **Testing:** PHPUnit with Laravel testing utilities
- **Frontend:** React 18+ with TypeScript
- **Styling:** Tailwind CSS 3+
- **Build Tool:** Vite

---

**End of Project Structure Map**
