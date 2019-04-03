module.exports = {
  '@tags': ['Thunder'],
  createArticleWithParagraphs(browser) {
    browser
      .drupalRelativeURL('/user/login')
      .performanceMeasurementStart('http://localhost:8200', 'NightwatchJS - Test', 'Create an article with paragraphs', '.thunder.dd')
      .performanceMarkStart('full task')
      .performanceMarkStart('login')
      .drupalLogin({name: 'admin', password: 'admin'})
      .performanceMark('create article')
      .drupalRelativeURL('/node/add/article')
      .waitForElementVisible('#field-paragraphs-values', 1000)
      // Set base vaues for an Article.
      .performanceMarkStart('create article basic fields')
      .click('#edit-field-channel > option:nth-child(2)')
      .setValue('#edit-title-0-value', 'Lorem Cat Sum 10. Reasons why cats ipsum')
      .setValue('#edit-field-seo-title-0-value', '10 Reasons why cats')
      .setValue('#edit-field-tags-wrapper input.select2-search__field', 'Performance')
      .waitForElementVisible('.select2-results__option--highlighted', 5000)
      .keys([browser.Keys.ENTER])
      .setValue('#edit-field-tags-wrapper input.select2-search__field', 'Testing')
      .waitForElementVisible('.select2-results__option--highlighted', 5000)
      .keys([browser.Keys.ENTER])
      .setValue('#edit-field-tags-wrapper input.select2-search__field', 'Cats')
      .waitForElementVisible('.select2-results__option--highlighted', 5000)
      .keys([browser.Keys.ENTER])
      // Set teaser information for an Article.
      .performanceMark('create article teaser information')
      .setValue('#edit-field-teaser-text-0-value', 'The cat (Felis catus) is a small carnivorous mammal. It is the only domesticated species in the family Felidae and often referred to as the domestic cat to distinguish it from wild members of the family. The cat is either a house cat, kept as a pet, or a feral cat, freely ranging and avoiding human contact.[5] A house cat is valued by humans for companionship and for its ability to hunt rodents. About 60 cat breeds are recognized by various cat registries.')
      // Create paragraphs for an Article.
      .performanceMark('create paragraphs')
      .useXpath()
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[1]//input')
      .click('//*[@name="field_paragraphs_text_add_more"]')
      .waitForElementVisible('//*[contains(@id, "cke_edit-field-paragraphs-0-subform-field-text-0-value")]//iframe', 5000)
      .executeAsync(
        function (done) {
          CKEDITOR.instances[jQuery('[name="field_paragraphs[0][subform][field_text][0][value]"]')[0].id].insertHtml('<p><strong><span>1: Lorem ipsum dolor sit amet</span></strong></p><p><span>an est tacimates molestiae, vel eu animal suscipit. Populo accusam ad has, cu libris disputando voluptatibus ius, feugiat nusquam instructior id pro?</span></p><p><span>Vel possim invidunt ex, est facer erant phaedrum ea? Ei ancillae detraxit mei, antiopam euripidis vim in? Vel ea amet movet fastidii. Magna oratio molestie eum ea, ius cu odio cibo?</span></p>');

          done();
        },
        [],
        function () {
        }
      )
      .performanceMarkEnd()
      .click('//*[@id="edit-submit"]')
      .performanceMeasurementEnd();

    browser.end();
  },
};
