# feip-php-project

### Getting Started

Run this commands to setup environment:

```
cp .env .env.local
cp .env.dev .env.dev.local
cp .env.test .env.test.local
```

Build Docker images:

```
make build
```

Install dependencies:

```
make composer-install
```

Make migrations:

```
make doctrine-diff
make doctrine-migrate
```

Run the application:

```
make up-logs
```

_TESTS_

Run tests:

```
make test-all
```

or...

```
make test-services
make test-controllers
```

### Api Documentation

- `GET /api/summerhouse/list` - Retrieves a list of all summer houses

- `POST /api/summerhouse/create` - Creates a new summer house

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

- `PUT /api/summerhouse/change/{houseId}` - Updates the details of an existing summer house (full request body must be provided)

- `DELETE /api/summerhouse/delete/{houseId}` - Deletes a summer house by its ID

- `GET /api/booking/list` - Retrieves a list of all bookings

- `POST /api/booking/create` - Creates a new booking

  Request body:

  ```json
  {
    "phoneNumber": "+12223334455",
    "houseId": 1,
    "startDate": "2023-01-20 13:30:00",
    "endDate": "2024-01-20 13:30:00",
    "comment": "Two-floor loft in the middle of the city"
  }
  ```

- `PUT /api/booking/change/{bookingId}` - Updates the details of an existing booking (full request body must be provided)

- `DELETE /api/booking/delete/{bookingId}` - Deletes a booking by its ID

_OPTIONAL_

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
    "redhat.vscode-yaml"
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
    "editor.defaultFormatter": "bmewburn.vscode-intelephense-client"
  }
}
```
