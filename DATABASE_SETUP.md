# SquidSphere Database Setup Instructions

## Step 1: Create Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. The database "squid" should already exist
3. If not, create it by clicking "New" and naming it "squid"

## Step 2: Create Players Table
1. Click on the "squid" database in phpMyAdmin
2. Click on the "SQL" tab
3. Copy and paste the contents of `database/create_players_table.sql`
4. Click "Go" to execute the SQL

Or alternatively:
1. Click on the "squid" database
2. Click "Import" tab
3. Choose the file `database/create_players_table.sql`
4. Click "Go"

## Step 3: Verify Database Configuration
1. Open `config/db_config.php`
2. Verify the database credentials:
   - DB_HOST: localhost
   - DB_USER: root
   - DB_PASS: (empty for default XAMPP)
   - DB_NAME: squid

## Step 4: Test the Application
1. Start XAMPP (Apache and MySQL)
2. Navigate to http://localhost/SquidSphere/
3. Click "Enter" button
4. Click "Add Player" button
5. Fill in the form:
   - Name: Player name
   - Age: Must be 18 or older
   - Gender: M, F, or Other
   - Debt Amount: Must be greater than 0
   - Nationality: Any country
   - Alliance Group: Optional (leave empty or enter a number)

## Database Schema

### Players Table
- player_id (Primary Key, Auto Increment)
- player_number (Unique, VARCHAR(3), e.g., "001", "002")
- name (VARCHAR)
- age (INT, must be >= 18)
- gender (ENUM: M, F, Other)
- status (ENUM: alive, eliminated, winner) - Default: alive
- debt_amount (DECIMAL, must be > 0)
- nationality (VARCHAR)
- registration_date (DATETIME, Default: Current Timestamp)
- alliance_group (INT, NULL allowed)

## API Endpoints (player_api.php)

### Add Player
- Method: POST
- Action: add
- Parameters: name, age, gender, debt_amount, nationality, alliance_group (optional)

### Get All Players
- Method: GET
- Action: get_all

### Get Single Player
- Method: GET
- Action: get_one
- Parameters: player_id

### Update Player
- Method: POST
- Action: update
- Parameters: player_id, name, age, gender, status, debt_amount, nationality, alliance_group

### Delete Player
- Method: POST
- Action: delete
- Parameters: player_id

### Get Next Player Number
- Method: GET
- Action: get_next_number

## Troubleshooting

If you encounter errors:
1. Make sure XAMPP Apache and MySQL are running
2. Verify database credentials in config/db_config.php
3. Check that the players table was created successfully
4. Check browser console for JavaScript errors
5. Check PHP errors in XAMPP error logs
