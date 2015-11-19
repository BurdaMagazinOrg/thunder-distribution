Feature: basic functionalities

  Scenario: Open the front page
    Given I am on the homepage
    Then the response status code should be 200

  Scenario: Login Error messages
    Given I am an anonymous user
    And I am on "user"
    Then I fill in "admin" for "edit-name"
    And I fill in "admin" for "edit-pass"
    And I press "edit-submit"
    And I should not see the following error messages:
      | error messages                                                                |
      | Password field is required                                                    |
      | Sorry, unrecognized username or password                                      |
      | Unable to send e-mail. Contact the site administrator if the problem persists |

