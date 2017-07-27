## How to pull screenshots from Travis CI

When some layout is changed and screenshots for tests have to be updated, then screenshots have to fetched from Travis CI.
Other solution is to have local docker Travis CI environment to create new screenshots.

#### 1. Start Travis CI debug sessions

To start Travis CI job in debug mode ([more information here](https://docs.travis-ci.com/user/running-build-in-debug-mode/#Restarting-a-job-in-debug-mode-via-API)), following command should be executed (with providing token and travis job id):

```
$ curl -s -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Travis-API-Version: 3" \
  -H "Authorization: token <TRAVIS_CI_TOKEN>" \
  -d '{ "quiet": true }' \
  https://api.travis-ci.org/job/<TRAVIS_CI_JOB_ID>/debug
```

After that Travis CI job will run in debug mode and in log output on travis.org, it will provide ssh link to access it.
It looks like: ssh <SOME_HASH>@ny2.tmate.io

Copy that command and execute in console, that will make connection to tmux for Travis CI debug session.

#### 2. Execute `.travis.yml` steps

To execute usual Travis CI steps, run following commands:

```
$ travis_run_before_install
$ travis_run_install
$ travis_run_before_script
```

#### 3. Prepare run tests script

Open new bash session in tmux (Ctrl-b c).

Then edit script for running of tests:
```
$ vi scripts/travis/06-run-tests.sh
```

Prepend line that executes tests with `generateMode=true`, that flag is used to create screenshot files during test execution. Line in script should look like this: ```generateMode=true thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --verbose --color --url http://localhost:8080 Thunder,thunder_updater```

(optional) Speed-Up solution is to add additional filtering. In that way it's possible to run only tests that should be updated, then generation of screenshots will execute faster.
For example: ```generateMode=true thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --verbose --color --url http://localhost:8080 --class "Drupal\Tests\thunder\FunctionalJavascript\Update\ThunderMediaTest"```

#### 4. Run tests

Go back to default bash session (Ctrl-b 0) and execute script to run tests.
```
$ bash -x -e ./scripts/travis/06-run-tests.sh
```

They have to finish successfully.

#### 5. Serve screenshots

Start new bash session in tmux (Ctrl-b c) and go to `screenshots` directory.

```
$ cd ../test-dir/docroot/profiles/contrib/thunder/tests/fixtures/screenshots/
```
or for `drush make` build:
```
$ cd ../test-dir/docroot/profiles/thunder/tests/fixtures/screenshots/
```

And run HTTP server to server files from that folder.
```
$ python -m SimpleHTTPServer 8001
```

#### 6. ngrok (to pull them all)

Start new bash session in tmux (Ctrl-b c).
Download `ngrok` Linux 64-Bit version from [https://ngrok.com/download](https://ngrok.com/download) and start it.

```
$ wget https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip
$ unzip ngrok-stable-linux-amd64.zip
$ ./ngrok http 8001
```

That will create ngrok url, that you can use to access served screenshots directory.

#### 7. Fetch screenshots

When `ngrok` url is open in browser, list of all screenshots should be available for download.

#### 8. (optional) Verify that screenshots are correct

Go to bash session (Ctrl-b 1).
And remove `generateMode=true` from test execution line in script that run tests.

So open file:
```
$ vi scripts/travis/06-run-tests.sh
```

And set something like this: ```thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --verbose --color --url http://localhost:8080 Thunder,thunder_updater```

Save and switch to default bash session (Ctrl-b 0), where tests should be executed.

#### 9. Exit them ALL

After everything is finished, all tmux sessions should be closed to finish Travis CI debug session.
