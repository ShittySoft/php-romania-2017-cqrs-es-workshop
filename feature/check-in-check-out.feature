Feature: Users check-in and check-out of a building

  Scenario: check-in
    Given a building has been registered
    When the user checks into the building
    Then the user was checked into the building

  Scenario: double check-in causes a check-in anomaly
    Given a building has been registered
    And the user checked into the building
    When the user checks into the building
    Then the user was checked into the building
    And a check-in anomaly was detected

  Scenario: check-in (with examples)
    Given the "Hilton hotel xyz" has been registered as a building
    When "bob" checks into the building
    Then the "bob" was checked into the building
