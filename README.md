# Scandiweb Backend

## How to build and run locally

Make sure you have docker and docker compose installed
and then run the following command:

```
docker compose up -d --build
```

## How to create and populate the database

After running the services locally

Run the following command to attach to a bash shell inside the container:

```
docker exec -it php-apache bash
```

Run the following command to install composer dependencies:

```
composer install
```

Run the following command to navigate to the database migration/seed files directory:

```
cd src/database
```

Run the following command to create the database:

```
php migrate_database.php
```

Run the following command to populate the database:

```
php populate_database.php
```

## How to stop

Run the following command:

```
docker compose down --volumes
```

Please beware that the database will persist in the `mysql-data` directory.
