pd@min
======

by Sven Hofrichter
<http://www.hofrichter.net/>

# Introduction

## Project gools
This tiny project was made to find some best practices for "sugarfree.im". The
second gaol - in your eyes the main goal - was, to implement something, that
makes the configuration of a mailserver based on a combination of postix
and dovecot, which find its configuration in files only. The hardest thing, to
hit this second goal, was, to configure these two servers to work with each
other - and so a third goal was born ;-)

## Features
The easest way to summurize all the features of this application can be done, by
listing the views with a short description of them:

### Domains 
Every mail server is responsible for e-mails sent to a specific domain or a set
of domains. These can be configured at this view. All other views depending on
this view.

### Accounts
An alternative word for accounts is users. Every user has its in postbox, where
mail can received in or send from. Every account has its own postbox and its own
password, which is required to login into the mail server.

### Addresses
This view implements the management of E-Mail-Addresses. All those addresses
are configured in here.

### Tests & Releases
A special feature of this application is the implementation of tests. Tests are
useful to check the configuration and means the checks against expected results.
Those expected results can be defined in here. The application runs all the
tests as soon, as you open this view or press the selfexplaining button.

Another button on this page, initiates the rollout, which can be done independed
to the result of the test results. The rollout means, that the current
configuration will be copied into a special folder, where a scheduled task will
fetch them to overwrite the configuration of your postfix-dovecot-installtion.

### History
This view shows ALL releases (whether successful or not) done by this
application. Every configuration can be used as startpoint of a new
configuration workflow, which can be tested and deployed too.

## The underlying technics
This application is a webbased administration frontend. Its architecture
implements a strict separation of the postfix-dovecot-installation from the
webfrontend. The application itselfs does NOT need any rights, to do changes in
the folders of the mailserver. This is done by a shell script, which runs as a
backgroundjob, ones it was started. It schedules the tasks:
* checking the release folder for a new configuration
* rollout the new configuration in the mailserver
* restart the server components
* copy this current configuration into the backup-folder (History-view)
* (on success) mark the folder as a working configuration (for any rollback
  scenarius)

The separation of the webapplication from the rollout-mechanism ensures, that
no one can use the frontend to inject some bad things into your mail server
directly.

You also can not add new administration accounts to this solution, whithout
having access rights to the filesystem.


# Requirements

This application requires PHP 5.3 or later.

The project depends on these modules, which are included in the application:

*    [AngularJs 1.4.5](<http://angularjs.org>)
*    [AngularUI 0.13.4](<http://angularui.org>)
*    [Bootstrap 3.3.5](<http://getbootstrap.com>)
*    [jsSHA](<http://caligatio.github.com/jsSHA/>)
*    [HTTP Auth Interceptor Module for AngularJS](<https://github.com/witoldsz/angular-http-auth>)


# Installation

Note: this installation guide partialy bases on the result of
      "./mailserver-setup-description/Setup.md".

* Download pd@min
* unpack the archive to a folder managed by your webserver
  We'll use the alias "INST_DIR" in the next steps
* check the URL for correct interpretation of the '.htaccess'

  $ wget http://[YOUR-SERVER]/pd@min/config
  The response MUST be an error whith HTTP-Status 403 Forbidden
  STOP here, if the expected result does not occures.

* edit [INST_DIR]/config/pdadmin.cfg and set the correct pathes
* run the shell script:
  $ [INST_DIR]/pdadmin.sh list-config
  It will print some useful details, which should be checked and corrected in the
  configuration-file [INST_DIR]/config/pdadmin.cfg

* edit the file [INST_DIR]/config/work/admistrators and add a account to
  interact as a administrator in the webfronted:
  $ user=testuser; echo "$user=$(doveadm pw -s SHA512 -u $user)"
  Paste the output into file [INST_DIR]/config/work/admistrators
  Multiple administrators are possible - every user must be placed in its own line

* finished!

# Bugs

To file bug reports please send email to:
<pdadmin@hofrichter.net>

# Feature Requests
* rollout only, if a difference was found in the configuration
* pd@min should send an email to a special account, as soon, as a new
  configuration was rolled out


# Version History
* 15.11: initial version


# Copyright and License
MIT-license
