# UTA-IT-Ticket-System

## Database setup

Create a MySQL/MariaDB database named `users_staff_db` and `ticket_comments`, then import `users_staff.sql`.
The SQL file creates the user and ticket tables for a new database. If you already
have a `tickets` table, run `migrations/001_add_ticket_user_id.sql` once to add
ticket ownership. Existing tickets remain unassigned; new tickets are associated
with the logged-in user.

The database connection settings are in `config.php`. Set them to match your local
database server before running the PHP application.

The user dashboard lists each user's tickets under New, In Progress, Resolved, and
Canceled filters. Staff can view all tickets in the same status groups and sort by
submission time or priority. Users can cancel their own active tickets from the
ticket list.

Staff accounts are redirected to `IT_Staff_Dashboard.php` after login. The staff
dashboard uses the same `users_staff_db` database as login and ticket submission,
and allows staff to search tickets and update their status.
