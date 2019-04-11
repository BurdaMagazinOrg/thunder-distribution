/**
 * @file
 * Testing of an article creation with 15 paragraphs.
 */

const path = require("path");

/**
 * Module "elastic-apm-node" has to be installed for core.
 *
 * You can use Yarn command for that: yarn add elastic-apm-node --dev
 * and it will install that module with it's requirements.
 *
 * We are using "process.cwd()" to get core directory.
 */
// eslint-disable-next-line import/no-dynamic-require
const apm = require(`${process.cwd()}/node_modules/elastic-apm-node`);

module.exports = {
  "@tags": ["Thunder"],
  before(browser, done) {
    browser.apm = apm;

    done();
  },
  createAnArticleWithParagraphs(browser) {
    browser
      .resizeWindow(1024, 1024)
      .drupalRelativeURL("/user/login")
      .performanceMeasurementStart(
        process.env.THUNDER_APM_URL,
        "NightwatchJS - Test",
        "Create an article with paragraphs",
        `.${process.env.THUNDER_SITE_HOSTNAME}`
      )
      .performanceMarkStart("full task")
      .performanceMarkStart("login")
      .drupalLogin({ name: "test-editor", password: "test-editor" })
      .performanceMark("create article")
      .drupalRelativeURL("/node/add/article")
      // Start using XPATH!!!
      .useXpath()
      .waitForElementVisible('//*[@id="field-paragraphs-values"]', 1000)
      // Set base vaues for an Article.
      .performanceMarkStart("create article basic fields")
      .click('//*[@id="edit-field-channel"]/option[3]')
      .setValue(
        '//*[@id="edit-title-0-value"]',
        "Lorem Cat Sum 10. Reasons why cats ipsum"
      )
      .setValue(
        '//*[@id="edit-field-seo-title-0-value"]',
        "10 Reasons why cats"
      )
      .thunderSelectTag(
        '//*[@id="edit-field-tags-wrapper"]//input',
        "Performance",
        '//*[@id="select2-edit-field-tags-results"]/li[contains(@class, "highlighted")]'
      )
      .thunderSelectTag(
        '//*[@id="edit-field-tags-wrapper"]//input',
        "Testing",
        '//*[@id="select2-edit-field-tags-results"]/li[contains(@class, "highlighted")]'
      )
      .thunderSelectTag(
        '//*[@id="edit-field-tags-wrapper"]//input',
        "Cats",
        '//*[@id="select2-edit-field-tags-results"]/li[contains(@class, "highlighted")]'
      )
      // Set teaser information for an Article.
      .performanceMark("create article teaser information")
      .setValue(
        '//*[@id="edit-field-teaser-text-0-value"]',
        "The cat (Felis catus) is a small carnivorous mammal. It is the only domesticated species in the family Felidae and often referred to as the domestic cat to distinguish it from wild members of the family. The cat is either a house cat, kept as a pet, or a feral cat, freely ranging and avoiding human contact.[5] A house cat is valued by humans for companionship and for its ability to hunt rodents. About 60 cat breeds are recognized by various cat registries."
      )
      .click(
        '//*[@id="edit-field-teaser-media-entity-browser-entity-browser-open-modal"]'
      )
      .waitForElementVisible(
        '//*[@id="entity_browser_iframe_image_browser"]',
        10000
      )
      .frame("entity_browser_iframe_image_browser")
      .waitForElementVisible(
        '//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]/div[1]/span/img',
        10000
      )
      .click(
        '//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[13]'
      )
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible(
        '//*[contains(@id, "edit-field-teaser-media-current-items-0")]/article/div/img',
        10000
      )
      // Create paragraphs for an Article.
      .performanceMark("create paragraphs")

      .performanceMarkStart("create paragraphs - set 1")
      .paragraphsAdd("field_paragraphs", "text", 1, {
        text:
          "<p><strong><span>1: Lorem ipsum dolor sit amet</span></strong></p><p><span>an est tacimates molestiae, vel eu animal suscipit. Populo accusam ad has, cu libris disputando voluptatibus ius, feugiat nusquam instructior id pro?</span></p><p><span>Vel possim invidunt ex, est facer erant phaedrum ea? Ei ancillae detraxit mei, antiopam euripidis vim in? Vel ea amet movet fastidii. Magna oratio molestie eum ea, ius cu odio cibo?</span></p>"
      })
      .paragraphsAdd("field_paragraphs", "image", 2, {
        uploadImage: path.resolve(`${__dirname}/../../../fixtures/cat1.jpg`)
      })
      .paragraphsAdd("field_paragraphs", "instagram", 3, {
        url:
          "https://www.instagram.com/p/BtlH0ysgGLs/?utm_source=ig_web_copy_link"
      })

      .performanceMark("create paragraphs - set 2")
      .paragraphsAdd("field_paragraphs", "text", 4, {
        text:
          "<p><strong><span>2: Ex cotidieque intellegebat nec</span></strong></p><p><span>quo cu quis ridens, ei  cibo omnes complectitur duo. Cu sed deleniti indoctum assueverit. Elit eligendi senserit eu nam. Velit delectus ut cum, no vim habeo veniam mentitum, eos id eros senserit.</span></p>"
      })
      .paragraphsAdd("field_paragraphs", "image", 5, {
        uploadImage: path.resolve(`${__dirname}/../../../fixtures/cat2.jpg`)
      })
      .paragraphsAdd("field_paragraphs", "instagram", 6, {
        url:
          "https://www.instagram.com/p/BtSRBAgAYod/?utm_source=ig_web_copy_link"
      })

      .performanceMark("create paragraphs - set 3")
      .paragraphsAdd("field_paragraphs", "text", 7, {
        text:
          "<p><strong><span>3: Ne cum copiosae praesent, feugait quaestio inciderint eos ad.</span></strong></p><p><span>Odio salutatus constituto eam ea. Mel zril cotidieque dissentiunt ea, erant inimicus convenire sit cu, ea nam oratio vituperatoribus. Noster invenire instructior ex pro. Duo ad mutat fierent.</span></p>"
      })
      .paragraphsAdd("field_paragraphs", "image", 8, {
        uploadImage: path.resolve(`${__dirname}/../../../fixtures/cat3.jpg`)
      })
      .paragraphsAdd("field_paragraphs", "instagram", 9, {
        url:
          "https://www.instagram.com/p/BtH8DB3g3GL/?utm_source=ig_web_copy_link"
      })

      .performanceMark("create paragraphs - set 4")
      .paragraphsAdd("field_paragraphs", "text", 10, {
        text:
          "<p><strong><span>4: Commune accumsan deleniti ad duo, cum cibo lorem delicatissimi ex!</span></strong></p><p><span>Cum mundi nostro forensibus id. At eos elitr fabulas intellegebat! Eleifend praesent mea no, id stet deseruisse pro!</span></p>"
      })
      .paragraphsAdd("field_paragraphs", "image", 11, {
        uploadImage: path.resolve(`${__dirname}/../../../fixtures/cat4.jpg`)
      })
      .paragraphsAdd("field_paragraphs", "instagram", 12, {
        url:
          "https://www.instagram.com/p/Btv_rtKF8mU/?utm_source=ig_web_copy_link"
      })

      .performanceMark("create paragraphs - set 5")
      .paragraphsAdd("field_paragraphs", "text", 13, {
        text:
          "<p><strong><span>5: Ex persecuti argumentum pri, phaedrum cotidieque mel te?</span></strong></p><p><span>Vix choro nusquam molestiae eu. Pro ei prodesset honestatis, an duo omnes dictas meliore. Fastidii reformidans sea ne. Modus mucius per et, audiam partiendo eu sea! Eam ea augue signiferumque.</span></p>"
      })
      .paragraphsAdd("field_paragraphs", "image", 14, {
        uploadImage: path.resolve(`${__dirname}/../../../fixtures/hermine.jpg`)
      })
      .paragraphsAdd("field_paragraphs", "instagram", 15, {
        url:
          "https://www.instagram.com/p/BtSRBAgAYod/?utm_source=ig_web_copy_link"
      })

      // End creation of paragraphs
      // Close: create paragraphs - set 5.
      .performanceMarkEnd()
      // Close: create paragraphs.
      .performanceMarkEnd()

      // Submit form.
      .click('//*[@id="edit-submit"]')
      .waitForElementVisible(
        '//*[@id="block-thunder-base-content"]/div/article/div/div[1]/div[13]/div/div/p[1]/strong/span',
        60000
      )
      .performanceMeasurementEnd();

    browser.end();
  }
};
