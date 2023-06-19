# SimpleWebApps

A collection of simple web apps written in PHP and Symfony that accidentally went a bit too much overboard.

Goals include:
* Implement every app listed on [this sample web app ideas page](https://flaviocopes.com/sample-app-ideas/)
* A relationship system where users are able to read and write data of other users if they're given permission to do so
* Stream data changes in real time
* Host all of it on a free PHP hosting provider

## Developing

Easiest way to start developing is on Visual Studio Code with Dev Containers. Use the docker-composer.yaml file included in this project. Whenever you start a new project, or do massive changes, you should run these commands:
```
php bin/console doctrine:database:drop -f --if-exists
php bin/console doctrine:database:create -n --if-exists
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
apache2ctl restart
```
Navigate to http://localhost:8080 on your browser to see the app. The fixtures create some default users and data, see [AppFixtures.php](https://github.com/wooky/SimpleWebApps/blob/master/src/DataFixtures/AppFixtures.php#L47-L53) for a list of users (their passwords are the same as the usernames).

### Cleanliness

There's a level of quality that needs to be maintained. Run `composer fix` to lint the source files, and `composer check` to verify.

## License

Licensed under the Unlicense license, see [LICENSE](LICENSE).
