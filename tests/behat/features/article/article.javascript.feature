@api
@javascript
@critical
@article

Feature: Article Teaser

  Scenario: As Administrator I make Article with Teaser
    Given I am logged in as a user with the "administrator" role

    # Create basic channel term.
    Given I am on "admin/structure/taxonomy/manage/channel/add"
    And I fill in "Behat Teaser channel" for "Name"
    And I fill in "/behat-teasers" for "URL alias"
    And I press "Save"

    # Create basic article structure.
    Given I am on "/node/add/article"
    And I select "Behat Teaser channel" from "Channel"
    And I fill in "Behat Teaser Title" for "Title"
    And I fill in "This is the page title" for "SEO Title"
    And I wait for page to load content
    And I expand "URL path settings" option in the "Right Sidebar" region
    And I uncheck the box "Generate automatic URL alias"
    And I fill in "/behat-teasers/article-teaser" for "URL alias" in the "Right Sidebar" region

    # Add 'Teaser'.
    And I press "Select entities" in the "Teaser" region
    And I wait for page to load content
    And I drop the file "thunder-main-1.png" in drop zone and select it
    And I wait for page to load content
    And I should see an image in the "Teaser" region
    And I press "Save and publish" for drop-down button in the "Footer Bar" region

    # Check channel page.
    Given I am on "/behat-teasers"
    And I wait for "Behat Teaser Title"
    Then I should see the text "Behat Teaser Title"
    Then I should see an image in the "Content" region