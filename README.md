# Messing about with DynamoDB & PHP
## Setup
### Requirements
- Docker
- PHP >=7.4
- AWS CLI

### Install
- `composer install`
- `cp .env.example .env`

note: when using the DynamoDB container in a local environment, you must provide AWS CLI credentials (even if they aren't real)
otherwise the SDK will assume it's running in the cloud and attempt to connect to http://169.254.169.254 for IAM details.
[See Documentation](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html)
  
## Usage
- `docker-compose up -d`
- `cd scripts`
- Execute script files

The default configuration uses a local instance of DynamoDB (running in Docker).
To connect to DynamoDB in the cloud, edit the `.env` variables and skip the `docker-compose` invocation.

### Scripts
There are a number of scripts that can be run from the Command Line:

#### Create/Delete Table
- `php createTable.php`
- `php deleteTable.php`

The script creates a simple Table for storing lots of Pii data...
Currently doesn't support Secondary Indexes.

#### Get/Put Items
- `php putItems.php`
- `php putItem.php -p "HASH Value" -s "RANGE Value"`
- `php getItem.php "HASH Value" "RANGE Value"`

The `putItems` script auto-populates the Table with Faker data.

#### Scan Table/Query Items
- `php scanTable.php`
- `php queryTable.php -p "HASH Value"`
- `php queryTable.php -p "HASH Value" -s "RANGE Value"`

#### Update Table Items
- `php setItemAttribute.php -p "HASH Value" -s "RANGE Value" -a Attribute -v Value`
- `php deleteItemAttribute.php -p "HASH Value" -s "RANGE Value" -a Attribute`

#### Delete Table Item
- `php deleteItem.php -p "HASH Value" -s "RANGE Value"`

### Example
- `php createTable.php`
- `php putItems.php`
- `php scanTable.php`
- `php deleteTable.php`

## Known Issues
- [ ] getItem.php needs to be moved and updated
- [ ] scripts are hard coded to use Partition & Sort Keys
- [ ] Secondary Indexes are not supported
- [ ] ADD & DELETE Actions for sets are not supported

## AWS CLI Examples

The actions available in the AWS-PHP-SDK can also be carried out in the AWS CLI.

`aws dynamodb create-table --table-name People --attribute-definitions AttributeName=PK,AttributeType=S AttributeName=SK,AttributeType=S --key-schema AttributeName=PK,KeyType=HASH AttributeName=SK,KeyType=RANGE --provisioned-throughput ReadCapacityUnits=5,WriteCapacityUnits=5 --endpoint-url http://localhost:8000`

`aws dynamodb list-tables --endpoint-url http://localhost:8000`

`aws dynamodb describe-table --table-name People --endpoint-url http://localhost:8000`

`aws dynamodb delete-table --table-name People --endpoint-url http://localhost:8000`
