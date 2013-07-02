# Toxic Release Inventory
This data mart tracks toxic chemicals that are used, manufactured, treated,
transported, or released into the environment in the state of California.

The app works from a list of records in CSV format as available from the EPA at
https://explore.data.gov/Environment/EPA-Toxics-Release-Inventory-Program/wma8-v5fi

## Usage
 * Pull down a CSV file from the above site
 * Use util/clean_tri.py to sanitize the data
 * Use the stored procedure in superload.sql to populate the databse

DB connection is established in db.php.
