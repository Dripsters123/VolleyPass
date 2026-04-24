# How to Run VolleyPass Locally

### Installation Steps

1.  **Clone the codebase**
    ```bash
    git clone https://github.com/Dripsters/VolleyPass
    ```

2.  **Navigate to the codebase directory**
    ```bash
    cd VolleyPass
    ```

3.  **Copy the example environment file**
    ```bash
    cp .env.example .env
    ```

4.  **Install the dependencies**
    Install the PHP and Node.js packages:
    ```bash
    composer install
    ```
    And
    ```bash
    npm install
    ```

5.  **Generate a local application key**
    ```bash
    php artisan key:generate
    ```

6.  **Configure your database credentials**
    Open the `.env` file and set up the following database connections. Make sure you have filled in the required credentials for your local database servers.

    *   `DB_CONNECTION=`
    *   `DB_HOST=`
    *   `DB_PORT=`
    *   `DB_DATABASE=`
    *   `DB_USERNAME=`
    *   `DB_PASSWORD=`
    *   `DB_FORMS_CONNECTION=mysql`
    *   `DB_FORMS_HOST=`
    *   `DB_FORMS_PORT=3306`
    *   `DB_FORMS_DATABASE=cst_forms`
    *   `DB_FORMS_USERNAME=`
    *   `DB_FORMS_PASSWORD=`

7.  **Run database migrations and seed the database**
    This will create all the necessary tables and populate them with initial data, including an admin user.
    ```bash
    php artisan migrate:fresh --seed
    ```

8.  **Start the local development server**
    ```bash
    php artisan serve
    ```
   
