<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\Tests\thunder\Functional\ThunderTestBase;

/**
 * Tests password policy integration.
 *
 * @group Thunder
 */
class PasswordPolicyTest extends ThunderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'password_policy_character_types',
    'password_policy_history',
    'password_policy_length',
    'password_policy',
  ];

  /**
   * Tests redirect from old URL to new one.
   */
  public function testPasswordPolicies() {
    $editor = $this->logWithRole('editor');

    $current_password = $editor->passRaw;
    $valid_password = 'This is 1 valid password!';
    $another_valid_password = 'This is 1 valid password 2!';

    $invalid_passwords = [
      'This is no valid password!' => 'Password must contain at least 4 types of characters from the following character types: lowercase letters, uppercase letters, digits, special characters.',
      'This is not 1 valid password' => 'Password must contain at least 4 types of characters from the following character types: lowercase letters, uppercase letters, digits, special characters.',
      'this is not 1 valid password!' => 'Password must contain at least 4 types of characters from the following character types: lowercase letters, uppercase letters, digits, special characters.',
      'short' => 'Password length must be at least 8 characters.',
    ];

    $edit['current_pass'] = $current_password;
    $edit['pass[pass2]'] = $valid_password;
    $edit['pass[pass1]'] = $valid_password;

    $this->drupalPostForm("user/" . $editor->id() . "/edit", $edit, $this->t('Save'));
    $this->assertSession()->responseContains('The changes have been saved.');

    // Testing reusing of password.
    $edit['current_pass'] = $valid_password;
    $edit['pass[pass2]'] = $another_valid_password;
    $edit['pass[pass1]'] = $another_valid_password;

    $this->drupalPostForm("user/" . $editor->id() . "/edit", $edit, $this->t('Save'));
    $this->assertSession()->responseContains('The changes have been saved.');

    $edit['current_pass'] = $another_valid_password;
    $edit['pass[pass2]'] = $valid_password;
    $edit['pass[pass1]'] = $valid_password;

    $this->drupalPostForm("user/" . $editor->id() . "/edit", $edit, $this->t('Save'));
    $this->assertSession()->responseNotContains('The changes have been saved.');

    // Testing invalid character type combinations and length restriction.
    foreach ($invalid_passwords as $password => $response) {
      $edit['pass[pass2]'] = $password;
      $edit['pass[pass1]'] = $password;

      $this->drupalPostForm("user/" . $editor->id() . "/edit", $edit, $this->t('Save'));
      $this->assertSession()->responseContains($response);
    }

  }

}
