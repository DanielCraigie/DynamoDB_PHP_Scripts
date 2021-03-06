# Messing about with DynamoDB & PHP
## Setup
### Requirements
- Docker
- PHP >=7.4
- AWS CLI

### Install
- `composer install`
- `cp .env.example .env`
  
## Usage
- `docker-compose up -d`

The default configuration uses a local instance of DynamoDB (running in Docker).  To connect to DynamoDB in the cloud, edit the `.env` variables and skip the `docker-compose` invocation.

### Scripts

There are a number of scripts that can be run from the Command Line:

#### Create/Delete Table
- `php createTable.php`
- `php deleteTable.php`

The script creates a simple Table for storing lots of Pii data...

Currently doesn't support Secondary Indexes.

#### Get/Put Items
- `php putItems.php`
- `php getItem.php "HASH Value" "RANGE Value"`

The `putItems` script auto-populates the Table with Faker data.

#### Scan Table/Query Items
- `php scanTable.php`
- `php queryTable.php '{"hash":"HASH Value"}'`
- `php queryTable.php '{"hash":"HASH Value", "range":"RANGE Value"}'`

#### Update Table Items
- `php updateItem.php '{"key":{"hash":"Value","range":"Value"},"actions":{"set":{"Attribute":"Value"}}}'`
- `php updateItem.php '{"key":{"hash":"Value","range":"Value"},"actions":{"remove":"Attribute"}}'`
- `php updateItem.php '{"key":{"hash":"Value","range":"Value"},"actions":{"remove":{"0":"Attribute"}}}'`

The update script currently only supports the SET & REMOVE actions.

### Example

- `php createTable.php`
- `php putItems.php`
- `php scanTable.php`
- `php deleteTable.php`

## Known Issues

- Secondary Indexes are not supported
- Update script does not support ADD & DELETE Actions
