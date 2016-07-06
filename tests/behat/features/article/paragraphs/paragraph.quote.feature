@api
@javascript
@critical
@article

Feature: Paragraph Quote

  Scenario: As Administrator I make Article with Quote Paragraph
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
    And I fill in "/behat/article-quote" for "URL alias" in the "Right Sidebar" region

    # Add 'Quote' paragraph.
    And I press "Add Quote" for drop-down button in the "Paragraphs" region
    And I wait for page to load content
    And I fill CKEditor with "Simple Quote Paragraph Test" in the "Paragraphs" region
    And I press "Save and publish" for drop-down button in the "Footer Bar" region

    # Check node page.
    Given I am on "/behat/article-quote"
    Then I should see the text "Simple Quote Paragraph Test"
