module.exports = {
  '@tags': ['Thunder'],
  createArticleWithParagraphs(browser) {
    browser
      .resizeWindow(1024, 1024)
      .drupalRelativeURL('/user/login')
      // TODO - move 'http://localhost:8202', '.thunder.dd' into some configuration!!!
      .performanceMeasurementStart('http://localhost:8200', 'NightwatchJS - Test', 'Create an article with paragraphs', '.thunder.dd')
      .performanceMarkStart('full task')
      .performanceMarkStart('login')
      .drupalLogin({name: 'test-editor', password: 'test-editor'})
      .performanceMark('create article')
      .drupalRelativeURL('/node/add/article')
      // Start using XPATH!!!
      .useXpath()
      .waitForElementVisible('//*[@id="field-paragraphs-values"]', 1000)
      // Set base vaues for an Article.
      .performanceMarkStart('create article basic fields')
      .click('//*[@id="edit-field-channel"]/option[3]')
      .setValue('//*[@id="edit-title-0-value"]', 'Lorem Cat Sum 10. Reasons why cats ipsum')
      .setValue('//*[@id="edit-field-seo-title-0-value"]', '10 Reasons why cats')
      .thunderSelectTag('//*[@id="edit-field-tags-wrapper"]//input', 'Performance', '//*[@id="select2-edit-field-tags-results"]/li[contains(@class, "highlighted")]')
      .thunderSelectTag('//*[@id="edit-field-tags-wrapper"]//input', 'Testing', '//*[@id="select2-edit-field-tags-results"]/li[contains(@class, "highlighted")]')
      .thunderSelectTag('//*[@id="edit-field-tags-wrapper"]//input', 'Cats', '//*[@id="select2-edit-field-tags-results"]/li[contains(@class, "highlighted")]')
      // Set teaser information for an Article.
      .performanceMark('create article teaser information')
      .setValue('//*[@id="edit-field-teaser-text-0-value"]', 'The cat (Felis catus) is a small carnivorous mammal. It is the only domesticated species in the family Felidae and often referred to as the domestic cat to distinguish it from wild members of the family. The cat is either a house cat, kept as a pet, or a feral cat, freely ranging and avoiding human contact.[5] A house cat is valued by humans for companionship and for its ability to hunt rodents. About 60 cat breeds are recognized by various cat registries.')
      .click('//*[@id="edit-field-teaser-media-entity-browser-entity-browser-open-modal"]')
      .waitForElementVisible('//*[@id="entity_browser_iframe_image_browser"]', 10000)
      .frame('entity_browser_iframe_image_browser')
      .waitForElementVisible('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]/div[1]/span/img', 10000)
      .click('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]')
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible('//*[contains(@id, "edit-field-teaser-media-current-items-0")]/article/div/img', 10000)
      // Create paragraphs for an Article.
      .performanceMark('create paragraphs')

      .performanceMarkStart('create paragraphs - set 1')
      // 1. Text Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[1]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[1]//input')
      .click('//*[@name="field_paragraphs_text_add_more"]')
      .waitForElementVisible('//*[contains(@id, "cke_edit-field-paragraphs-0-subform-field-text-0-value")]//iframe', 10000)
      .thunderFillCKEditor('//*[@name="field_paragraphs[0][subform][field_text][0][value]"]', '<p><strong><span>1: Lorem ipsum dolor sit amet</span></strong></p><p><span>an est tacimates molestiae, vel eu animal suscipit. Populo accusam ad has, cu libris disputando voluptatibus ius, feugiat nusquam instructior id pro?</span></p><p><span>Vel possim invidunt ex, est facer erant phaedrum ea? Ei ancillae detraxit mei, antiopam euripidis vim in? Vel ea amet movet fastidii. Magna oratio molestie eum ea, ius cu odio cibo?</span></p>')
      // 2. Image Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[3]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[3]//input')
      .click('//*[@name="field_paragraphs_image_add_more"]')
      .waitForElementVisible('//*[contains(@id, "edit-field-paragraphs-1-subform-field-image-entity-browser-entity-browser-open-modal")]', 10000)
      .click('//*[contains(@id, "edit-field-paragraphs-1-subform-field-image-entity-browser-entity-browser-open-modal")]')
      .waitForElementVisible('//*[@id="entity_browser_iframe_image_browser"]', 10000)
      .frame('entity_browser_iframe_image_browser')
      .waitForElementVisible('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]/div[1]/span/img', 10000)
      .click('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]')
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[4]//img', 10000)
      // 3. Instagram Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[5]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[5]//input')
      .click('//*[@name="field_paragraphs_instagram_add_more"]')
      .waitForElementVisible('//input[contains(@id, "edit-field-paragraphs-2-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 10000)
      .setValue('//input[contains(@id, "edit-field-paragraphs-2-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 'https://www.instagram.com/p/BtlH0ysgGLs/?utm_source=ig_web_copy_link')

      .performanceMark('create paragraphs - set 2')
      // 4. Text Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[7]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[7]//input')
      .click('//*[@name="field_paragraphs_text_add_more"]')
      .waitForElementVisible('//*[contains(@id, "cke_edit-field-paragraphs-3-subform-field-text-0-value")]//iframe', 10000)
      .thunderFillCKEditor('//*[@name="field_paragraphs[3][subform][field_text][0][value]"]', '<p><strong><span>2: Ex cotidieque intellegebat nec</span></strong></p><p><span>quo cu quis ridens, ei  cibo omnes complectitur duo. Cu sed deleniti indoctum assueverit. Elit eligendi senserit eu nam. Velit delectus ut cum, no vim habeo veniam mentitum, eos id eros senserit.</span></p>')
      // 5. Image Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[9]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[9]//input')
      .click('//*[@name="field_paragraphs_image_add_more"]')
      .waitForElementVisible('//*[contains(@id, "edit-field-paragraphs-4-subform-field-image-entity-browser-entity-browser-open-modal")]', 10000)
      .click('//*[contains(@id, "edit-field-paragraphs-4-subform-field-image-entity-browser-entity-browser-open-modal")]')
      .waitForElementVisible('//*[@id="entity_browser_iframe_image_browser"]', 10000)
      .frame('entity_browser_iframe_image_browser')
      .waitForElementVisible('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[14]/div[1]/span/img', 10000)
      .click('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[14]')
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[10]//img', 10000)
      // 6. Instagram Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[11]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[11]//input')
      .click('//*[@name="field_paragraphs_instagram_add_more"]')
      .waitForElementVisible('//input[contains(@id, "edit-field-paragraphs-5-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 10000)
      .setValue('//input[contains(@id, "edit-field-paragraphs-5-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 'https://www.instagram.com/p/BtSRBAgAYod/?utm_source=ig_web_copy_link')

      .performanceMark('create paragraphs - set 3')
      // 7. Text Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[13]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[13]//input')
      .click('//*[@name="field_paragraphs_text_add_more"]')
      .waitForElementVisible('//*[contains(@id, "cke_edit-field-paragraphs-6-subform-field-text-0-value")]//iframe', 10000)
      .thunderFillCKEditor('//*[@name="field_paragraphs[6][subform][field_text][0][value]"]', '<p><strong><span>3: Ne cum copiosae praesent, feugait quaestio inciderint eos ad.</span></strong></p><p><span>Odio salutatus constituto eam ea. Mel zril cotidieque dissentiunt ea, erant inimicus convenire sit cu, ea nam oratio vituperatoribus. Noster invenire instructior ex pro. Duo ad mutat fierent.</span></p>')
      // 8. Image Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[15]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[15]//input')
      .click('//*[@name="field_paragraphs_image_add_more"]')
      .waitForElementVisible('//*[contains(@id, "edit-field-paragraphs-7-subform-field-image-entity-browser-entity-browser-open-modal")]', 10000)
      .click('//*[contains(@id, "edit-field-paragraphs-7-subform-field-image-entity-browser-entity-browser-open-modal")]')
      .waitForElementVisible('//*[@id="entity_browser_iframe_image_browser"]', 10000)
      .frame('entity_browser_iframe_image_browser')
      .waitForElementVisible('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[12]/div[1]/span/img', 10000)
      .click('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[12]')
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[16]//img', 10000)
      // 9. Instagram Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[17]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[17]//input')
      .click('//*[@name="field_paragraphs_instagram_add_more"]')
      .waitForElementVisible('//input[contains(@id, "edit-field-paragraphs-8-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 10000)
      .setValue('//input[contains(@id, "edit-field-paragraphs-8-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 'https://www.instagram.com/p/BtH8DB3g3GL/?utm_source=ig_web_copy_link')

      .performanceMark('create paragraphs - set 4')
      // 10. Text Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[19]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[19]//input')
      .click('//*[@name="field_paragraphs_text_add_more"]')
      .waitForElementVisible('//*[contains(@id, "cke_edit-field-paragraphs-9-subform-field-text-0-value")]//iframe', 10000)
      .thunderFillCKEditor('//*[@name="field_paragraphs[9][subform][field_text][0][value]"]', '<p><strong><span>4: Commune accumsan deleniti ad duo, cum cibo lorem delicatissimi ex!</span></strong></p><p><span>Cum mundi nostro forensibus id. At eos elitr fabulas intellegebat! Eleifend praesent mea no, id stet deseruisse pro!</span></p>')
      // 11. Image Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[21]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[21]//input')
      .click('//*[@name="field_paragraphs_image_add_more"]')
      .waitForElementVisible('//*[contains(@id, "edit-field-paragraphs-10-subform-field-image-entity-browser-entity-browser-open-modal")]', 10000)
      .click('//*[contains(@id, "edit-field-paragraphs-10-subform-field-image-entity-browser-entity-browser-open-modal")]')
      .waitForElementVisible('//*[@id="entity_browser_iframe_image_browser"]', 10000)
      .frame('entity_browser_iframe_image_browser')
      .waitForElementVisible('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]/div[1]/span/img', 10000)
      .click('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]')
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[22]//img', 10000)
      // 12. Instagram Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[23]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[23]//input')
      .click('//*[@name="field_paragraphs_instagram_add_more"]')
      .waitForElementVisible('//input[contains(@id, "edit-field-paragraphs-11-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 10000)
      .setValue('//input[contains(@id, "edit-field-paragraphs-11-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 'https://www.instagram.com/p/Btv_rtKF8mU/?utm_source=ig_web_copy_link')

      .performanceMark('create paragraphs - set 5')
      // 13. Text Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[25]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[25]//input')
      .click('//*[@name="field_paragraphs_text_add_more"]')
      .waitForElementVisible('//*[contains(@id, "cke_edit-field-paragraphs-12-subform-field-text-0-value")]//iframe', 10000)
      .thunderFillCKEditor('//*[@name="field_paragraphs[12][subform][field_text][0][value]"]', '<p><strong><span>5: Ex persecuti argumentum pri, phaedrum cotidieque mel te?</span></strong></p><p><span>Vix choro nusquam molestiae eu. Pro ei prodesset honestatis, an duo omnes dictas meliore. Fastidii reformidans sea ne. Modus mucius per et, audiam partiendo eu sea! Eam ea augue signiferumque.</span></p>')
      // 14. Image Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[27]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[27]//input')
      .click('//*[@name="field_paragraphs_image_add_more"]')
      .waitForElementVisible('//*[contains(@id, "edit-field-paragraphs-13-subform-field-image-entity-browser-entity-browser-open-modal")]', 10000)
      .click('//*[contains(@id, "edit-field-paragraphs-13-subform-field-image-entity-browser-entity-browser-open-modal")]')
      .waitForElementVisible('//*[@id="entity_browser_iframe_image_browser"]', 10000)
      .frame('entity_browser_iframe_image_browser')
      .waitForElementVisible('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[14]/div[1]/span/img', 10000)
      .click('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[14]')
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[28]//img', 10000)
      // 15. Instagram Paragraph
      .thunderScrollIntoView('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[29]//input')
      .click('//*[contains(@class, "field-multiple-table--paragraphs-experimental--add-in-between")]/tbody/tr[29]//input')
      .click('//*[@name="field_paragraphs_instagram_add_more"]')
      .waitForElementVisible('//input[contains(@id, "edit-field-paragraphs-14-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 10000)
      .setValue('//input[contains(@id, "edit-field-paragraphs-14-subform-field-media-0-inline-entity-form-field-url-0-uri")]', 'https://www.instagram.com/p/BtSRBAgAYod/?utm_source=ig_web_copy_link')

      // End creation of paragraphs
      // Close: create paragraphs - set 5
      .performanceMarkEnd()
      // Close: create paragraphs
      .performanceMarkEnd()

      // Submit form
      .click('//*[@id="edit-submit"]')
      .waitForElementVisible('//*[@id="block-thunder-base-content"]/div/article/div/div[1]/div[13]/div/div/p[1]/strong/span', 60000)
      .performanceMeasurementEnd();

    browser.end();
  },
};
