#TuTu

[![Build Status](https://travis-ci.org/coduo/TuTu.svg?branch=master)](https://travis-ci.org/coduo/TuTu)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/coduo/TuTu/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/coduo/TuTu/?branch=master)

Flexible HTTP server mocking tool in PHP. TuTu can work with build in php server so everything that you need to use it is php.
It can be used to simulate any kind of web application behavior. We are going to use TuTu during api clients tests.

## How to use it

### Install with composer

```
$ composer create-project coduo/tutu --stability=dev
```

**TuTu is still under development that's why you need to set stability to dev**

### Create responses for specific requests

TuTu can create any response for specific request but you need to teach him how to do it.
In order to do that you need to prepare simple ``responses.yml`` yaml file.

```
$ cd tutu
$ cp config/responses.yml.dist config/responses.yml
```

Lets assume that we would like to create some kind of "Hello world" response

```
hello_world:
  request:
    path: /hello/world
    methods: ['GET']
  response:
    content: |
      Hello {{ request.query.get('name') }}!
```

### Run TuTu

You can use php build in webserver to run TuTu

```
$ cd web
$ php -S localhost:8000
```

### Test TuTu

How when you send a GET request on "http://localhost:8000/hello/world?name=Norbert" TuTu should give you response
with following content

```Hello Norbert!```

## Responses configuration

```
# config/responses.yml

hello_world_get:
  request:
    path: /hello/world
    methods: ['GET']
    query: []
    request: []
    headers: []
    body: ""
  response:
    content: "This is nothing more than twig template"
    status: 200
    headers:
      "Content-type": "application/json"
```

As you can see there are few was to customize TuTu responses.

* ``request.path`` - required option, it represents route. You can use placeholders to create route, for example ``/hello/{name}``
* ``request.methods`` - optional. When empty any method is allowed. Must be a valid array
* ``request.request`` - optional. When not empty it will match only request that contain body ($_POST) parameters.
* ``request.query`` - optional. When not empty it will match only request that contain query ($_GET) parameters.
* ``request.headers`` - optional. When not empty it will match only request that contain specified headers.
* ``request.body`` - optional. When not empty it will match only request that contain specified body.
* ``response.content`` - optional. Content is nothing more that twig template rendered before passed to response.
* ``response.status`` - optional. Response code
* ``response.headers`` - optional. Headers added to response Must be a valid array

In content template you have access to all twig features. You can also use ``request`` variable to access request data.

**Request query, headers, request and body configuration may contain [coduo/php-matcher patterns](https://github.com/coduo/php-matcher#available-patterns).**

## Load response content from file

In order to keep your config files as small as possible you can move response content into separated file.

```
# config/responses.yml

hello_world_get:
  request:
    path: /hello/world
    methods: ['GET']
  response:
    content: @resources/hello_world.twig.html
```

Above configuration is going to load content from hello_world.twig.html file present in ``@resources`` namespace.

```
{# resources/hello_world.twig.html #}
Hello {{ request.request.get('name') }}!
```

## Configuration
TuTu have also configuration file where you can set up few things.
```
# config/config.yml
parameters:
  resources_path: '/var/www/tutu/custom_resources' # empty by default
  responses_file_path: '/var/www/tutu/config/custom_responses.yml' # empty by default
  twig: # optional, must be a valid array. Passed to twig env
    debug: true
    auto_reload: true

extensions:
  Coduo\TuTu\Extension\Faker: ~
```

## Extensions

Because there is no such thing as perfect tool TuTu allows you to create extensions.
To enable extension you just need to prepare ``config.yml`` file.

```yml
# config/config.yml
extensions:
    Coduo\TuTu\Extension\Faker: ~
```

Above example show how to load Faker extension (available in this repository).
You can also pass arguments to extension during initialization.

```yml
# config/config.yml
extensions:
    Coduo\TuTu\Extension\Faker:
        - "pl_PL"
```

In above example extension ``Coduo\TuTu\Extension\Faker`` is going to be created with one argument with value "pl_PL".

**Keep in mind that Faker extension is available in TuTu by default. You don't need to enable it manually.**

## Few Examples


```
# config/responses.yml

client_token_grant:
  request:
    path: "/oauth/v2/token"
    query:
      "client_id": "CLIENT_VALID_ID"
      "client_secret": "CLIENT_VALID_SECRET"
      "grant_type": "client_credentials"
  response:
    content: "@resources/oauth2/client_token_grant.json.twig"
    headers:
      Content-Type: application/json

missing_grant_type:
  request:
    path: "/oauth/v2/token"
  response:
    content: "@resources/oauth2/missing_grant_type.json.twig"
    status: 400
    headers:
      Content-Type: "application/json"

register_customer:
  request:
    path: "/api/customer"
    methods: ['POST']
    headers:
      Authorization: "Bearer VALID_CLIENT_ACCESS_TOKEN"
    body: |
      {
        "email": @string@,
        "plainPassword": @string@,
        "firstName": @string@,
        "lastName": @string@
      }
  response:
    content: "@resources/api/customer/register_customer.json.twig"
    headers:
      Content-Type: application/json

register_with_missing_parameters:
  request:
    path: "/api/customer"
    methods: ['POST']
    headers:
      Authorization: "Bearer VALID_CLIENT_ACCESS_TOKEN"
  response:
    content: "@resources/api/customer/register_missing_parameters.json.twig"
    status: 422
    headers:
      Content-Type: application/json
```
