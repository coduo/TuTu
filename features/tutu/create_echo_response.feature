Feature: Create echo response
  As a api client developer
  In order to simulate simple api behavior
  I need to create response configuration that returns data from request

  Background:
    Given  TuTu is running on host "localhost" at port "8000"

  Scenario: Create response from request parameters
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: |
          Hello {{ request.request.get('name') }}!
    """

    And http client sends POST request on "http://localhost:8000/hello/world" with following parameters:
      | Parameter | Value   |
      | name      | Norbert |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Norbert!
    """

  Scenario: Create response from request parameters with placeholders
    When there is a responses config file "responses.yml" with following content:
    """
    item_details:
      request:
        path: /products/{id}/photos/{photoId}
      response:
        content: |
          Hello From photo
    """
    And http client sends POST request on "http://localhost:8000/products/1/photos/1"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello From photo
    """

  Scenario: Create response from request query parameters
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: |
          Hello {{ request.query.get('name') }}!
    """
    And http client sends GET request on "http://localhost:8000/hello/world?name=Norbert"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Norbert!
    """
