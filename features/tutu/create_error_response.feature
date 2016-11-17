Feature: Create response when cant match request
  As a api client developer
  In order know when my request cant be handled
  I expect response with constant message and specific header in such situation

  Background:
    Given TuTu is running on host "localhost" at port "8000"

  Scenario: Create response when cant match request
    When there is a empty responses config file "responses.yml"
    And http client sends GET request on "http://localhost:8000/hello/world"
    Then response status code should be 404
    And the response content should be equal to:
    """
    TuTu don't know how to response for "GET" "/hello/world" request :(
    """
    And response should have following hedaers:
      | Name        | Value            |
      | TuTu-Error  | Request mismatch |

  Scenario: Create response for internal error
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: |
          Hello {% rwqrqwr %}
    """
    And http client sends GET request on "http://localhost:8000/hello/world"
    Then response status code should be 500
    And the response content should be equal to:
    """
    There was a internal server error with message: Unknown "rwqrqwr" tag in "Hello {% rwqrqwr %}" at line 1.
    """
    And response should have following hedaers:
      | Name        | Value    |
      | TuTu-Error  | Internal |
