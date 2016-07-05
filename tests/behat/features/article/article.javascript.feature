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
    And I wait for page to load content
    And I fill in "/behat-teasers/article-teaser" for "URL alias" in the "Right Sidebar" region

    # Add 'Teaser'.
    And I press "Add new Image" in the "Teaser" region
    And I wait for page to load content
    And I fill in "Test Behat Teaser" for "Media name" in the "Teaser" region
    And I attach the file "thunder-main-1.png" to "Image"
    And I wait for page to load content
    And I fill in "Alt text for Test Behat Teaser" for "Alternative text" in the "Teaser" region
    And I fill in "Title for Test Behat Teaser" for "Title" in the "Teaser" region
    And I press the "Create Image" button
    And I wait for page to load content
    And I press "Save and publish" for drop-down button in the "Footer Bar" region

    # Check channel page.
    Given I am on "/behat-teasers"
    Then I should see the text "Behat Teaser Title"
    Then I should see the image alt "Alt text for Test Behat Teaser" in the "Content" region
    Then I should see an image in the "Content" region