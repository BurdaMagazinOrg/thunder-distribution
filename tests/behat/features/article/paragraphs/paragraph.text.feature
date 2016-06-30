@api
@javascript

Feature: Paragraph Text

  Scenario: As Administrator I make Article with Text as Paragraph
    Given I am logged in as a user with the "administrator" role

    # Create basic channel term.
    Given I am on "admin/structure/taxonomy/manage/channel/add"
    And I fill in "Behat channel" for "name[0][value]"
    And I press "op"

    # Create basic article structure.
    Given I am on "/node/add/article"
    And I fill in "Behat Test Title" for "title[0][value]"
    And I fill in "This is the page title" for "field_seo_title[0][value]"
    And I select "Behat channel" from "edit-field-channel"
    And I click on the element with css selector "[aria-controls='edit-path-settings']"
    And I uncheck the box "edit-path-0-pathauto"
    And I fill in "/behat/article" for "path[0][alias]"

    # Add 'Text' paragraph.
    Then I click on the element with css selector "#edit-field-paragraphs-wrapper .dropbutton-toggle button"
    And I click on the element with css selector "#edit-field-paragraphs-wrapper input[data-drupal-selector=edit-field-paragraphs-add-more-add-more-button-text]"
    And I wait for AJAX to finish
    Then I fill in wysiwyg on field "field_paragraphs[0][subform][field_text][0][value]" with "Simple Text Paragraph Test"
    And I click on the element with css selector "[data-drupal-selector=edit-save] .dropbutton-toggle button"
    And I click on the element with css selector "[data-drupal-selector=edit-save] .publish .button"

    # Check node page.
    Given I am on "/behat/article"
    Then I should see the text "Simple Text Paragraph Test"
