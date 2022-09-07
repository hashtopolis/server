@requiresDB

# TODO: preferably 404 instead of 401?
Feature: Hashlist functionality

  Scenario: Requesting all hashlists without an API key results in 'unauthorized'
    When I request "api/v2/hashlists"
    Then the response status code should be 401

  Scenario: Requesting all hashlists with an invalid API key results in 'unauthorized'
    When I request "api/v2/hashlists?api_key=invalidKey"
    Then the response status code should be 401

  Scenario: Requesting all hashlists when none are available results in empty list
    When I request "api/v2/hashlists?api_key=mykey"
    Then the response status code should be 200
    And the response is JSON
    And the response equals []

  Scenario: Requesting a hashlist without an API key results in 'unauthorized'
    When I request "api/v2/hashlists/1"
    Then the response status code should be 401

  Scenario: Requesting a hashlist with an invalid API key results in 'unauthorized'
    When I request "api/v2/hashlists/1?api_key=invalidKey"
    Then the response status code should be 401

  Scenario: Requesting a non-existing hashlist results in 'not found'
    When I request "api/v2/hashlists/1?api_key=mykey"
    Then the response status code should be 404

  Scenario: Creating a hashlist with pasted hashes succeeds
    Given that I send:
    """
    {
      "name": "list-1",
      "format": 0,
      "hashtypeId": 0,
      "saltSeparator": ":",
      "isSecret": false,
      "isHexSalted": false,
      "isSalted": false,
      "accessGroupId": 1,
      "useBrain": false,
      "brainFeatures": 0,
      "dataSourceType": "paste",
      "dataSource": "49f68a5c8493ec2c0bf489821c21fc3b"
    }
    """
    When I request "api/v2/hashlists?api_key=mykey"
    Then the response status code should be 200
    And the response is JSON "int"
    And the response equals 1

  # TODO: create a second hashlist, because when deleting a hashlist when there is only one, seems to trigger a bug:
  #       in HashlistUtils delete, in case of a single hashlist in the table, it will truncate the whole table
  #           -> Factory::getAgentFactory()->getDB()->query("TRUNCATE TABLE Hash");
  #       this however implicitly closes the transaction, which will result in a 'transaction already closed' later on
  #           -> Factory::getAgentFactory()->getDB()->commit();
  Scenario: Create another hashlist, else the delete will fail later on
    Given that I send:
    """
    {
      "name": "list-1",
      "format": 0,
      "hashtypeId": 0,
      "saltSeparator": ":",
      "isSecret": false,
      "isHexSalted": false,
      "isSalted": false,
      "accessGroupId": 1,
      "useBrain": false,
      "brainFeatures": 0,
      "dataSourceType": "paste",
      "dataSource": "49f68a5c8493ec2c0bf489821c21fc3b"
    }
    """
    When I request "api/v2/hashlists?api_key=mykey"
    Then the response status code should be 200
    And the response is JSON "int"
    And the response equals 2

  Scenario: Retrieving created list returns expected values
    When I request "api/v2/hashlists/1?api_key=mykey"
    Then the response status code should be 200
    And the response equals JSON:
    """
    {
      "name": "list-1",
      "format": 0,
      "hashtypeId": 0,
      "saltSeparator": ":",
      "isSecret": false,
      "isHexSalted": false,
      "isSalted": false,
      "accessGroupId": 1,
      "useBrain": false,
      "brainFeatures": 0,
      "id": 1,
      "hashCount": 1,
      "crackedCount": 0,
      "notes": ""
    }
    """

  Scenario: Updating a hashlist succeeds
    Given that I send a patch:
    """
    {
      "name": "list-1-patched",
      "notes": "some added notes"
    }
    """
    When I request "api/v2/hashlists/1?api_key=mykey"
    Then the response status code should be 204

  Scenario: Retrieving patched list returns expected values
    When I request "api/v2/hashlists/1?api_key=mykey"
    Then the response status code should be 200
    And the response equals JSON:
    """
    {
      "name": "list-1-patched",
      "format": 0,
      "hashtypeId": 0,
      "saltSeparator": ":",
      "isSecret": false,
      "isHexSalted": false,
      "isSalted": false,
      "accessGroupId": 1,
      "useBrain": false,
      "brainFeatures": 0,
      "id": 1,
      "hashCount": 1,
      "crackedCount": 0,
      "notes": "some added notes"
    }
    """

  Scenario: Deleting a hashlist succeeds
    Given that I want to delete a "hashlist"
    When I request "api/v2/hashlists/1?api_key=mykey"
    Then the response status code should be 204

  Scenario: Requesting a deleted hashlist results in 'not found'
    When I request "api/v2/hashlists/1?api_key=mykey"
    Then the response status code should be 404