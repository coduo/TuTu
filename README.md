#TuTu

Flexible HTTP server mocking tool in PHP. TuTu can work with build in php server so everything that you need to use it is php.
It can be used to simulate any kind of web application behavior. We are going to use TuTu during api clients tests.

## How to use it

### Clone project from github

```
$ git clone git@github.com:coduo/TuTu.git
```

### Create responses for specific requests

TuTu can create any response for specific request but you need to teach him how to do it.
In order to do that you need to prepare simple ``responses.yml`` yaml file.

```
$ cd TuTu
$ cp config/responses.yml.dist config/responses.dist
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
$ web
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
