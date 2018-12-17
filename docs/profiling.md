### Blackfire installation


#### Installing the Agent

````
$ brew tap blackfireio/homebrew-blackfire
$ brew install blackfire-agent
$ sudo blackfire-agent -register
````
The last command will ask for your Blackfire server credentials (Server Id, Server Token).

````
$ ln -sfv /usr/local/opt/blackfire-agent/*.plist ~/Library/LaunchAgents/
$ launchctl load -w ~/Library/LaunchAgents/homebrew.mxcl.blackfire-agent.plist
````
In order to restart the service, and whenever you modify its configuration, unload it:

````
$ launchctl unload ~/Library/LaunchAgents/homebrew.mxcl.blackfire-agent.plist
$ launchctl load -w ~/Library/LaunchAgents/homebrew.mxcl.blackfire-agent.plist
```` 

#### CLI

````$ blackfire config```` 
This command will ask for your Blackfire client credentials (Client Id, Client Token).

#### PHP module.

````$ brew install blackfire-php72````

or

````$ brew install blackfire-php72-zts````

if version differs from webserver use

````$ brew install blackfire-php72-zts --without-homebrew-php```` 

#### PHP Config

/usr/local/etc/php/7.2/conf.d/ext-blackfire.ini

__For DevDesktop__

````
extension="/usr/local/Cellar/blackfire-php72-zts/1.23.1/blackfire.so"
blackfire.agent_socket = unix:///usr/local/var/run/blackfire-agent.sock
blackfire.agent_timeout = 0.25
````


__Usage__

Use [blackfire companion](https://chrome.google.com/webstore/detail/blackfire-companion/miefikpgahefdbcgoiicnmpbeeomffld) browser plugin to profile http requests. For profiling POST requests select _Profile all requests_ and hit _Record!_, all subsequent requests will be recorded. For ajax, use 'Copy to curl' in network tab and use cmdline command ````blackfire curl````.


Find documentation here: \
https://blackfire.io/docs/up-and-running/installation \
https://blackfire.io/docs/book/index


