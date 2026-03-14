# GlobenTech - Laboratory Order Management System

**CPSY 301-D School Project Prototype**

This is a Phase 3 prototype for a Laboratory Order Management System designed to streamline the ordering, processing, and delivery of chemical compounds for GMJ Global Energy. This prototype demonstrates the login system, account management, and basic class structure as outlined in the project design document.

**Note:** This is a school project prototype created for educational purposes. It demonstrates core functionality including user authentication, role-based access control, and basic order management features.

## Technologies Used

- **HTML5/CSS3**: Structure and styling
- **JavaScript**: Client-side interactivity
- **PHP**: Server-side processing
- **MySQL**: Database for user accounts and system data

## Features (Prototype)

- User login and authentication system
- Account registration with role assignment
- Role-based access control (Customer, Laboratory Technician, Administrator)
- Basic dashboard layout
- Object-oriented class structure with properties and method signatures

## Installation Instructions

### Step 1: Install Laragon

1. Download Laragon from [https://laragon.org/download/](https://laragon.org/download/)
2. Install Laragon (default location: `C:\laragon`)
3. Open Laragon and click **Start All** to start Apache and MySQL

### Step 2: Install Composer (you can skip this step because we are currently not using any packages)

1. Download and install Composer from [https://getcomposer.org/](https://getcomposer.org/)
2. During installation, make sure it detects your `php.exe` from `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64`
3. Restart your computer to finish installing Composer
4. Open Command Prompt and run `composer -V` to verify Composer is installed

### Step 3: Set Up the Project

1. Place the project files directly in Laragon's `www` directory: `C:\laragon\www\project-phase-3`
   - The folder name will become part of your URL (e.g., folder `project-phase-3` → URL `localhost/project-phase-3`)
   - Avoid spaces in the folder name
2. Open Command Prompt and navigate to that directory:

```bash
cd C:\laragon\www\project-phase-3
```

3. Run the following command to install PHP dependencies:

```bash
composer install
```

This will download all required dependencies into the `vendor/` folder.

### Step 4: Set Up the Database

You need to create a MySQL database and import the schema.

**What is HeidiSQL?** HeidiSQL is a database management tool that comes with Laragon. It lets you manage MySQL databases through a visual interface (similar to phpMyAdmin).

1. **Open HeidiSQL**:
   - In Laragon, click the **Database** button
   - HeidiSQL will open and connect automatically

2. **Create the Database**:
   - Right-click in the left sidebar
   - Select **Create new → Database**
   - Name it: `globentech_db`
   - Click **OK**

3. **Import the Schema**:
   - Click on the **globentech_db** database in the left sidebar
   - Go to **File → Run SQL file...**
   - Navigate to your project folder and select: `database/schema.sql`
   - The tables will be created automatically

4. **Run Migrations (required for existing databases)**:
   - Execute files in `database/migrations/` in order (001, 002, 003, 004)
   - This ensures order-type and payment gateway tables/columns exist

5. **Verify the Import**:
   - Expand the **globentech_db** database in the left sidebar
   - You should see all the tables listed

## Running Locally

1. Open Laragon and click **Start All**
2. Navigate to http://localhost/project-phase-3 in your browser (adjust the folder name if different)
3. The website should now be running locally
4. To view emails sent by the application, open http://localhost:8025

## Default Credentials

For testing purposes, the following accounts are pre-configured:

**Administrator:**

- Email: `admin@globentech.com`
- Password: `admin123`

**Laboratory Technician:**

- Email: `tech@globentech.com`
- Password: `tech123`

**Customer:**

- Email: `customer@globentech.com`
- Password: `customer123`

## Project Structure

```
GlobenTech/
├── index.php                 # Entry point / Login page
├── register.php              # Account registration
├── dashboard.php             # Role-based dashboard
├── logout.php                # Logout handler
├── config/
│   └── database.php          # Database configuration
├── classes/                  # Object-oriented class files
│   ├── User.php
│   ├── Order.php
│   ├── Sample.php
│   ├── Equipment.php
│   └── Queue.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── css/
│   └── style.css
├── js/
│   └── main.js
├── database/
│   └── schema.sql            # Database schema
└── vendor/                   # Composer dependencies (gitignored)
```

## Class Structure (Prototype)

This prototype includes the following classes with properties and empty method signatures:

- **User**: Handles user accounts, authentication, and role management
- **Order**: Manages chemical compound orders and status tracking
- **Sample**: Represents individual samples for testing
- **Equipment**: Tracks laboratory equipment specifications and schedules
- **Queue**: Manages order queue and priority processing

## License

This is a school project created for educational purposes. All rights reserved.
