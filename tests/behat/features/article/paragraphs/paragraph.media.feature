@api
@javascript
@critical
@article

Feature: Paragraph Media

  Scenario: As Administrator I make Article with Media Paragraph
    Given I am logged in as a user with the "administrator" role

    # Create basic channel term.
    Given I am on "admin/structure/taxonomy/manage/channel/add"
    And I fill in "Behat channel" for "Name"
    And I press "Save"

    # Create basic article structure.
    Given I am on "/node/add/article"
    And I select "Behat channel" from "Channel"
    And I fill in "Behat Test Title" for "Title"
    And I fill in "This is the page title" for "SEO Title"
    And I wait for page to load content
    And I expand "URL path settings" option in the "Right Sidebar" region
    And I uncheck the box "Generate automatic URL alias"
    And I fill in "/behat/article-media" for "URL alias" in the "Right Sidebar" region

    # Add 'Media' paragraph.
    And I press "Add Media" for drop-down button in the "Paragraphs" region
    And I wait for page to load content
    And I press "Select entities" in the "Paragraphs" region
    And I wait for page to load content
    And I drop the file "thunder-main-1.png" in drop zone and select it
    And I wait for page to load content
    And I should see an image in the "Paragraphs" region
    And I press "Save and publish" for drop-down button in the "Footer Bar" region

    # Check node page.
    Given I am on "/behat/article-media"
    And I wait for "Behat Test Title"
    Then I should see an image in the "Content" region
