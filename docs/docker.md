# Create docker development environment

This documentation is for developers, that want do develop for the distribution, and not necessarily use it for a 
project.

## Preparation
To start developing install the following tools

- composer: https://getcomposer.org/download/
- docker und docker-compose: https://docs.docker.com/engine/installation/


## Installation

To install with docker just run:

```
$ ./scripts/development/build-thunder-docker.sh
```

This will install thunder ind th ~/tmp/installations/thunder directory. To be able to access this installation you 
additionally need to create an instance of traefik. To do so, just copy the provided 
scripts/development/docker-compose.traefik.yml file to an emtpy directory, rename it to docker-compose.yml and run
docker-compose up -d.
After the script has run, you can access your site at http://thunder.localhost, you might need to provide 
an /etc/hosts entry to have this sub domain point to 127.0.0.1 (not necessary on Macs).

If you want to simultaneously run multiple installations call the script like this:

```
$ ./scripts/development/build-thunder-docker.sh thunder2
```

This will install the project into the thunder2 directory. Now go back to your traefik docker-compose.yml and add a
thunder2 network for this installation. The file should now look like the following:

```
version: '2'

services:
  traefik:
    image: traefik
    restart: unless-stopped
    command: -c /dev/null --web --docker --logLevel=DEBUG
    networks:
      - thunder1
      - thunder2
    ports:
      - '80:80'
      - '8080:8080'
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock

networks:
  thunder1:
    external:
      name: thunder1_default
  thunder2:
    external:
      name: thunder2_default      
```

save the file and run 

``` 
$ docker-compose down -v
$ docker-compose up 
```

You can now access the installation by calling thunder2.localhost in your browser.


### Docker helpers

You can connect to the php docker container to execute drush commands inside the container. To do so, go
to the projects directory (this should contain a docker-compose.yml file) and execute:

``` 
$ docker-compose exec --user 82 php sh
```

Now you are inside the container and you can call drush on the installation. To make life a bit easier 
it is recommended to have the following alias inside your hosts .profile file:

```
alias ddrush='docker-compose exec --user 82 php drush -r /var/www/html/docroot'
```

now you do not have to manually enter the docker container to call drush, just go to the projects root 
folder and call ddrush instead of drush to automatically use the current php
container.

### Install Thunder in docker container

With the aliases above you can install thunder the following way 

```
§ ddrush si thunder --account-name=admin --account-pass=admin
```

### Permissions

To prevent problems with file permission sie [this page](https://docker4drupal.readthedocs.io/en/latest/permissions/) 
for more informations.  
