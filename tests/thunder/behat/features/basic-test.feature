Feature: basic functionalities

  Scenario: Open the front page
    Given I am on the homepage
    Then the response status code should be 200
