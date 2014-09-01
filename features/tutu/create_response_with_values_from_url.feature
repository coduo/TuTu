Feature: Create response for specific body
  As a api client developer
  In order to simulate simple api behavior
  I need to create be able to create different responses based on request body

  Background:
    Given TuTu is running on host "localhost" at port "8000"

  Scenario: Create response that contains variables from url placeholder
    When there is a responses config file "responses.yml" with following content:
    """
    request_with_variables_in_url:
      request:
        path: "/users/{email}/tasks/{taskId}"
      response:
        content: "Get task with ID {{path.taskId}} from user with email {{path.email}}"
    """
    And http client sends GET request on "http://localhost:8000/index.php/users/norbert@coduo.pl/tasks/1"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Get task with ID 1 from user with email norbert@coduo.pl
    """
