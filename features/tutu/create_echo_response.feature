Feature: Create echo response
  As a api client developer
  In order to simulate simple api behavior
  I need to create response configuration that returns data from request

  Scenario: Create response from request parameters
    Given there is a routing file "responses.yml" with following content:
    """
    hello_world:
      path: /hello/world
      content: |
        Hello {{ request.request.get('name') }}!
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send POST request on "http://localhost:8000/hello/world" with following parameters:
      | Parameter | Value   |
      | name      | Norbert |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Norbert!
    """

    Scenario: Create response from request parameters with placeholders
      Given there is a routing file "responses.yml" with following content:
      """
      item_details:
        path: /products/{id}/photos/{photoId}
        content: |
          Hello From photo
      """
      And TuTu is running on host "localhost" at port "8000"
      When http client send POST request on "http://localhost:8000/products/1/photos/1"
      Then response status code should be 200
      And the response content should be equal to:
      """
      Hello From photo
      """

  Scenario: Create response from request query parameters
    Given there is a routing file "responses.yml" with following content:
    """
    hello_world:
      path: /hello/world
      content: |
        Hello {{ request.query.get('name') }}!
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/hello/world?name=Norbert"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Norbert!
    """
