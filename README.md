#TuTu

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
  path: /hello/world
  methods: ['GET']
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
  path: /hello/world
  methods: ['GET']
  content: "This is nothing more than twig template"
  headers:
    "Content-type": "application/json"

```

As you can see there are few was to customize TuTu responses.

* ``path`` - required option, it represents route. You can use placeholders to create route, for example ``/hello/{name}``
* ``methods`` - optional. When empty any method is allowed. Must be a valid array
* ``content`` - optional. Content is nothing more that twig template rendered before passed to response.
* ``headers`` - optional. Headers added to response Must be a valid array

In content template you have access to all twig features. You can also use ``request`` variable to access request data.

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
