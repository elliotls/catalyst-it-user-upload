# Catalyst IT Developer Exercise
Submission by Elliot Lines Smith

Prerequisites for the `user_upload` command:
 - PHP@8.1 and MySQL@5.7
 - DB user passed to the script must have CREATE and INSERT privileges for all databases (or at minimum for the `user_upload` table to be created)

### Usage
Run the following command line prompt:
```./user_upload.php --file=users.csv -u <user> -p <password> -h localhost:3306```

### Improvements
Potential improvements to the health of this script could be:
 - Build a main class to house the calling of separate functions
 - To abstract error handling to a helper class to be passed an error message and exit the script gracefully
 - Sanitise user input (use functions like `strip_tags()`, `htmlspecialchars()`, `real_escape_string()`)
 - Limit the size of the csv file that can be opened depending on the hardware the script is to be run on
 - Sanitise the csv content to protect against SQL injection
 - Capitalise surnames properly after apostraphes and look for European names to keep lower case (_van der_ etc)
 - Extend the script to make the table and database names configurable with further input variables

## Logic Test
The FOOBAR number evaulator can be run using the following command line prompt:
```./foobar.php```