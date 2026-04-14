# Admin Mode Guide

## How to Use

### 1. Enable Admin Mode
- Click the blue lock icon next to the PDF button
- Enter the admin password
- Lock turns green when admin mode is active

### 2. Edit Table Cells
- Click any cell in the table
- Edit it
- Click outside the cell to save (flashes green when saved)

### 3. Add a New Row
- Click "Add Row" button
- A new empty row will be added to the table
- Edit the cells to fill in data
- IDs are now globally unique across all tables (`... 49, 50, 51 ...`)
- If the row represents the same person/project as in another table, the system reuses that existing ID

### 4. Delete a Row
- Click "Delete Row" button
- Enter the ID of the row you want to delete
- Confirm deletion

### 5. Add a Column
- Click "Add Column" button
- Enter column name (e.g., "email", "phone")
- Enter column type (default: VARCHAR(255))
  - Common types: VARCHAR(255), INT, TEXT, DATE, DECIMAL(10,2)
- Column will be added to the table

### 6. Delete a Column
- Click "Delete Column" button
- Enter the column name to delete
- Confirm deletion 

### 7. Create a New Table
- Click "New Table" button
- Enter table name (e.g., "projects", "clients")
- Confirm creation
- Default columns: id, name, created_at

### 8. Delete a Table
- Click "Delete Table" button (red)
- Type the exact table name to confirm
- Table and all data will be deleted

### 9. Exit Admin Mode
- Click the green lock icon
- Page will reload and admin mode will be disabled

## Security Notes
- Change the default password immediately
- For production use, implement server-side authentication
- Consider adding user roles and permissions
- Log all admin actions for audit purposes

## Database Columns Types
- `VARCHAR(255)` - Text up to 255 characters
- `TEXT` - Long text
- `INT` - Whole numbers
- `DECIMAL(10,2)` - Decimal numbers (e.g., prices)
- `DATE` - Date values
- `DATETIME` - Date and time
- `BOOLEAN` - True/false values
