# feip-php-project

### Getting Started

Run this commands to setup environment:

```
cp .env .env.local
cp .env.dev .env.dev.local
cp .env.test .env.test.local
```

Build Docker images:

```sh
make build
```

Install dependencies:

```sh
make composer-install
```

Run the application:

```sh
make up-logs
```

Generate JWT keys:

```sh
make generate-keypair
```

Setup database:

```sh
# make migrations-clean
# make doctrine-drop
make doctrine-create
make doctrine-diff
make doctrine-migrate
```

### Tests

Run tests:

```sh
make test-all
```

or...

```sh
make test-services
make test-controllers
```

### Formatting and linting

```sh
make phpcs
make phpcbf
make php-cs-fixer
make psalm
```

### Api Documentation

Getting token:

- `POST /api/register` - Registers a new user

  Request body:

  ```json
  {
    "phoneNumber": "+76665554433",
    "password": "poE@mTqPY9k4L9fC"
  }
  ```

- `POST /api/login` - Returns access and refresh tokens

  Request body:

  ```json
  {
    "phoneNumber": "+76665554433",
    "password": "poE@mTqPY9k4L9fC"
  }
  ```

- `POST /api/token/refresh` - Returns access and refresh tokens

  Request body:

  ```json
  {
    "refreshToken": "token"
  }
  ```

- `POST /api/logout` - Deletes refresh token

  Request body:

  ```json
  {
    "refreshToken": "token"
  }
  ```

You can create admin user by running this command:

```
make create-admin PHONE=+72223334455 PASSWORD=poE@mTqPY9k4L9fC
```

Booking API (_Bearer Token_ must me provided):

- `POST /api/profile` - Returns profile - _ROLE_USER_

- `GET /api/summerhouse/list` - Retrieves a list of all summer houses - _PUBLIC_ACCESS_

- `POST /api/summerhouse/create` - Creates a new summer house - _ROLE_ADMIN_

  Request body:

  ```json
  {
    "address": "123 Main St, City, ST 12345",
    "price": 10000,
    "bedrooms": 3,
    "distanceFromSea": 500,
    "hasShower": true,
    "hasBathroom": true
  }
  ```

- `PUT /api/summerhouse/change/{houseId}` - Updates the details of an existing summer house (full request body must be provided) - _ROLE_ADMIN_

- `DELETE /api/summerhouse/delete/{houseId}` - Deletes a summer house by its ID - _ROLE_ADMIN_

- `GET /api/booking/list` - Retrieves a list of all bookings - _ROLE_USER_

- `POST /api/booking/create` - Creates a new booking - _ROLE_USER_

  Request body:

  ```json
  {
    "houseId": 1,
    "startDate": "2023-01-20 13:30:00",
    "endDate": "2024-01-20 13:30:00",
    "comment": "Two-floor loft in the middle of the city"
  }
  ```

- `PUT /api/booking/change/{bookingId}` - Updates the details of an existing booking (full request body must be provided) - _ROLE_USER_

- `DELETE /api/booking/delete/{bookingId}` - Deletes a booking by its ID - _ROLE_USER_

### Optional

Create `.vscode/launch.json` file to configure Xdebug:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/project/": "${workspaceFolder}"
      }
    }
  ]
}
```

Format settings:

`.vscode/extensions.json`:

```json
{
  "recommendations": [
    "esbenp.prettier-vscode",
    "foxundermoon.shell-format",
    "xdebug.php-debug",
    "getpsalm.psalm-vscode-plugin",
    "redhat.vscode-xml",
    "redhat.vscode-yaml",
    "junstyle.php-cs-fixer"
  ]
}
```

`.vscode/settings.json`:

```json
{
  "[shellscript]": {
    "editor.defaultFormatter": "foxundermoon.shell-format"
  },
  "[dotenv]": {
    "editor.defaultFormatter": "foxundermoon.shell-format"
  },
  "[dockerfile]": {
    "editor.defaultFormatter": "ms-azuretools.vscode-docker"
  },
  "[gitinore]": {
    "editor.defaultFormatter": "foxundermoon.shell-format"
  },

  "xml.preferences.quoteStyle": "single",
  "xml.format.enforceQuoteStyle": "preferred",

  "[xml]": {
    "editor.defaultFormatter": "redhat.vscode-xml",
    "editor.tabSize": 4,
    "editor.insertSpaces": true,
    "editor.detectIndentation": false
  },

  "yaml.format.singleQuote": true,

  "[yaml]": {
    "editor.defaultFormatter": "redhat.vscode-yaml",
    "editor.insertSpaces": true,
    "editor.tabSize": 4,
    "editor.detectIndentation": false
  },

  "intelephense.format.enable": true,

  "[php]": {
    "editor.defaultFormatter": "junstyle.php-cs-fixer"
  },

  "php-cs-fixer.executablePath": "${workspaceFolder}/vendor/bin/php-cs-fixer",
  "php-cs-fixer.config": ".php-cs-fixer.dist.php",
  "php-cs-fixer.onsave": true,
  "php-cs-fixer.allowRisky": true,
  "php-cs-fixer.ignorePHPVersion": true
}
```
