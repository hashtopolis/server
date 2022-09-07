Feature: Health

  Scenario: Checking that the hashtopolis v2 service is up and running
    When I request "api/v2/health"
    Then the response status code should be 200
    And the response should be JSON
    And the response equals {"status":"UP"}