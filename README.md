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

Install dependencies:

```
make composer-install
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
