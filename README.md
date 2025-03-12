# feip-php-project

### Getting Started

Run this commands to setup environment:

```
cp .env .env.local
cp .env.dev .env.dev.local
```

Build Docker images:

```
make build
```

Run the application:

```
make up-logs
```

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
