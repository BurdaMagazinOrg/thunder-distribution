@api
@javascript
@critical

Feature: Paragraph Media

  Scenario: As Administrator I make Article with Media Paragraph
    Given I am logged in as a user with the "administrator" role

    # Create basic channel term.
    Given I am on "admin/structure/taxonomy/manage/channel/add"
    And I fill in "Behat channel" for "Name"
    And I press "Save"

    # Create basic article structure.
    Given I am on "/node/add/article"
    And I fill in "Behat Test Title" for "Title"
    And I fill in "This is the page title" for "SEO Title"
    And I select "Behat channel" from "Channel"
    And I click "URL path settings"
    And I fill in "/behat/media-article" for "URL alias" in the "Right Sidebar" region

    # Add 'Media' paragraph.
    And I press "Add Media" for drop-down button in the "Paragraphs" region
    And I wait for page to load content
    And I press "Add new Media"
    And I wait for page to load content
    And I fill in "Test Behat Media" for "Media name" in the "Paragraphs" region
    And I attach the file "thunder-main-1.png" to "Image"
    And I wait for page to load content
    And I fill in "Alt text for Test Behat Media" for "Alternative text" in the "Paragraphs" region
    And I fill in "Title for Test Behat Media" for "Title" in the "Paragraphs" region
    And I press "Create Media"
    And I wait for page to load content
    And I press "Save and publish" for drop-down button in the "Footer Bar" region

    # Check node page.
    Given I am on "/behat/media-article"
    Then I should see the image alt "Alt text for Test Behat Media" in the "Content" region
    Then I should see an image in the "Content" region
