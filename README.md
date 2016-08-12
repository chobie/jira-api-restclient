# JIRA REST API Client

[![Build Status](https://secure.travis-ci.org/chobie/jira-api-restclient.png)](http://travis-ci.org/chobie/jira-api-restclient)

You all know that JIRA supports REST API, right? It can be very useful, for example, during automation job creation and notification sending.

This library will ensure unforgettable expirience when working with JIRA through REST API. Hope you'll enjoy it.

## Usage

* JIRA Rest API Documents: https://docs.atlassian.com/jira/REST/latest/

```php
<?php
use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Issues\Walker;

$api = new Api(
    'https://your-jira-project.net',
    new Basic('yourname', 'password')
);

$walker = new Walker($api);
$walker->push(
	'project = "YOURPROJECT" AND (status != "closed" AND status != "resolved") ORDER BY priority DESC'
);

foreach ( $walker as $issue ) {
    var_dump($issue);
    // Send custom notification here.
}
```

## Installation

```
php composer.phar require chobie/jira-api-restclient ^2.0@dev
```

## Requirements

* [Composer](https://getcomposer.org/download/)

## License

JIRA REST API Client is released under the MIT License. See the bundled [LICENSE](LICENSE) file for details.
