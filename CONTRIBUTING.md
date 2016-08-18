# Contributing
JIRA REST API Client is an open source, community-driven project. If you'd like to contribute, feel free to do this, but remember to follow these few simple rules:

## Submitting an issues
- A reproducible example is required for every bug report, otherwise it will most probably be __closed without warning__.
- If you are going to make a big, substantial change, let's discuss it first.

## Working with Pull Requests
1. Create your feature addition or a bug fix branch based on __`master`__ branch in your repository's fork.
2. Make necessary changes, but __don't mix__ code reformatting with code changes on topic.
3. Add entry in `CHANGELOG.md` file following http://keepachangelog.com/ format (if applicable).
4. Add tests for those changes (please look into `tests/` folder for some examples). This is important so we don't break it in a future version unintentionally.
5. Check your code using "Coding Standard" (see below).
6. Commit your code.
7. Squash your commits by topic to preserve a clean and readable log.
8. Create Pull Request.

## Running the Tests

To be able to run integration tests locally (optional) please follow these steps once:

1. make sure repository is located in web server document root (or it's sub-folder)
2. copy `phpunit.xml.dist` file into `phpunit.xml` file
3. in the `phpunit.xml` file:
 * uncomment part, where `REPO_URL` environment variable is defined
 * set `REPO_URL` environment variable value to URL, from where repository can be accessed (e.g. `http://localhost/path/to/repository/`)

Make sure that you don't break anything with your changes by running:

```bash
$> ./vendor/bin/phpunit
```

## Checking coding standard violations

This library uses [Coding Standard](https://github.com/aik099/CodingStandard) to ensure consistent formatting across the code base. Make sure you haven't introduced any Coding Standard violations by running following command in the root folder of the library:

```bash
$> ./vendor/bin/phpcs --standard="vendor/aik099/coding-standard/CodingStandard" src tests
```

or by making your IDE ([instructions for PhpStorm](http://www.jetbrains.com/phpstorm/webhelp/using-php-code-sniffer-tool.html)) to check them automatically.
