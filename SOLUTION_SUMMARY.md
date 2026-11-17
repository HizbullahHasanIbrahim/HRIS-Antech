Exec Summary:

1. Migration Structure Fix
   - 0001_create_departments_table.php: Fixed departments schema (timestamp columns)
   - 0002_create_roles_table.php: Fixed roles schema (timestamps -> created_at/updated_at/deleted_at)
   - 0003_create_leave_types_table.php: Fixed leave_types schema (type, name, annual_quota, applies_to, is_active)
   - 0004_create_allowance_types_table.php: Fixed allowance_types schema
   - ... and all other migration files

2. Model Fixes
   - app/Models/Department.php: Fixed parent() and children() relationships
   - app/Models/Role.php: Fixed userRoles() and users() relationships
   - app/Models/LeaveType.php: Fixed leaveBalances() and leaveRequests() relationships
   - app/Models/AllowanceType.php: Fixed allowanceRequests() relationship
   - ... and all other models

3. Factory Fixes
   - database/factories/DepartmentFactory.php: Fixed factory definition
   - database/factories/RoleFactory.php: Fixed factory definition
   - database/factories/LeaveTypeFactory.php: Fixed factory definition
   - ... and all other factories

4. Pivot Models for Many-to-Many
   - Added app/Models/UserRole.php pivot model
   - Added app/Models/EmployeeAssignment.php pivot model
   - ... and all other pivot models

5. Relationship Fixes
   - Updated Employee.php hasOne(user)
   - Updated Employee.php hasOne(application)
   - Updated Employee.php hasMany(workloadReports)
   - ... and all other relationships

6. Tests and Service
   - Created tests/Feature/LeaveRequestServiceTest.php with comprehensive tests
   - Created app/Services/Attendance/LeaveRequestService.php

7. Running Tests
   - Test suite: 7 tests passed, 39 assertions
   - Pint formatter: All files formatted correctly

All migration files have been fixed to align with the current database schema and model definitions.
