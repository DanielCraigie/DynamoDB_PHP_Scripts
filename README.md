# DynoDogs - messing about with DynamoDB & PHP
## Setup
### Requirements
- Docker
- PHP >=7.4
- AWS CLI

### Configuration
- `docker-compose up -d`
- `aws dynamodb --endpoint-url http://localhost:8000 create-table --table-name Dogs --attribute-definitions AttributeName=KennelName,AttributeType=S --key-schema AttributeName=KennelName,KeyType=HASH --provisioned-throughput ReadCapacityUnits=5,WriteCapacityUnits=5`
