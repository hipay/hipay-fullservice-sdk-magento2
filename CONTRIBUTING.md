# Contributing to the HiPay Fullservice module for Magento 2.0

> :warning: This repository is a mirror of a private repository for this plugin, so we are not able to merge your PRs directly in GitHub. Any open PRs will be added to the main repository and closed in GitHub. Any contributor will be credited in the plugin's changelog.

Contributions to the HiPay Fullservice module for Magento 2.0 should be made via GitHub [pull requests][pull-requests] and discussed using GitHub [issues][issues].

## Contributing

### Before you start

If you would like to make a significant change, please open an issue to discuss it, in order to minimize duplication of effort.

### Install

Installation with Docker for testing

If you are a developer or a QA developer, you can use this project with Docker and Docker Compose.
Requirements for your environment:

- Git (<https://git-scm.com/>)
- Docker (<https://docs.docker.com/engine/installation/>)
- Docker Compose (<https://docs.docker.com/compose/>)

Here is the procedure to be applied to a Linux environment:

Open a terminal and select the folder of your choice.

Clone the HiPay Enterprise Magento2 project in your environment with Git:

```sh
git clone https://github.com/hipay/hipay-fullservice-sdk-magento2.git
```

1. Copy the content from the file `bin/docker/conf/development/auth.env.sample` and paste it in `bin/docker/conf/development/auth.env` file. Then, fill it with your personal tokens.
2. Copy the content from the file `bin/docker/conf/development/auth.json.sample` and paste it in `bin/docker/conf/development/auth.json` file. Then, fill it with your personal magento credentials.
3. Copy the content from the file `bin/docker/conf/development/hipay.env.sample` and paste it in `bin/docker/conf/development/hipay.env` file. Then, fill it with your personal vars.
4. Copy the content from the files `bin/docker/conf/development/mage.env.sample` and `bin/docker/conf/development/module.env.sample` then paste it in `bin/docker/conf/development/mage.env` and `bin/docker/conf/development/module.env` files.

Go in the project root folder and enter this command:

```sh
./magento.sh init
```

Your container is loading: wait for a few seconds while Docker installs Magento2 and the HiPay module.*

You can now test the HiPay Enterprise module in a browser with this URL: <http://localhost:8096>

To connect to the back office, go to this URL: <http://localhost:8096/admin>

The login and password are demo / hipay123.
You can test the module with your account configuration.

### Debug

If you want to debug locally our CMS module, here are the steps :

- Verify the value of `XDEBUG_CONFIG.client_host` in your `hipay.env` file you have copied in last step.
  - For Linux users, it should be `172.17.0.1` (value by default)
  - For MacOS users, replace it by `host.docker.internal`
- Then, create a Xdebug launch according to your IDE (here is for VSCode) :

  ```json
  {
    "name": "Magento2",
    "type": "php",
    "request": "launch",
    "hostname": "172.17.0.1", // Only for Linux users
    "port": 9003,
    "pathMappings": {
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Api": "${workspaceFolder}/Api",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Block": "${workspaceFolder}/Block",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Controller": "${workspaceFolder}/Controller",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/etc": "${workspaceFolder}/etc",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Helper": "${workspaceFolder}/Helper",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Model": "${workspaceFolder}/Model",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Console": "${workspaceFolder}/Console",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Observer": "${workspaceFolder}/Observer",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Plugin": "${workspaceFolder}/Plugin",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Ui": "${workspaceFolder}/Ui",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/view": "${workspaceFolder}/view",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Setup": "${workspaceFolder}/Setup",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Cron": "${workspaceFolder}/Cron",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/i18n": "${workspaceFolder}/i18n",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Logger": "${workspaceFolder}/Logger",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/Test": "${workspaceFolder}/Test",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/composer.json": "${workspaceFolder}/composer.json",
        "/bitnami/magento/app/code/HiPay/FullserviceMagento/registration.php": "${workspaceFolder}/registration.php",
        "/bitnami/magento/tests": "${workspaceFolder}/tests",
        "/bitnami/magento/var/log": "${workspaceFolder}/log",
        "/bitnami/magento/sdk": "${workspaceFolder}/conf/sdk",
        "/bitnami/magento": "${workspaceFolder}/web"
    }
  }
  ```

### Making the request

Development takes place against the `develop` branch of this repository and pull requests should be opened against that branch.

### Testing

Any contributions should pass all tests.

## Licensing

The HiPay Fullservice module for Magento 2.0 is released under an [Apache 2.0][project-license] license. Any code you submit will be released under that license.

[project-license]: LICENSE.md

[pull-requests]: https://github.com/hipay/hipay-fullservice-sdk-magento2/pulls
[issues]: https://github.com/hipay/hipay-fullservice-sdk-magento2/issues
