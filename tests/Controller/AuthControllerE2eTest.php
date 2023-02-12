<?php

declare(strict_types=1);

namespace SimpleWebApps\Tests\Controller;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Uid\Uuid;

class AuthControllerE2eTest extends PantherTestCase
{
  private const BASE_URI = 'http://127.0.0.1:9080'; // TODO unbelievably janky

  public function testRegisterLoginAndLogout(): void
  {
    $username = (string) Uuid::v1();
    $password = (string) Uuid::v1();

    // Go to main page, verify it redirects to login page
    $client = static::createPantherClient();
    $client->request('GET', '/');
    static::assertSame(self::BASE_URI.'/login', $client->getCurrentURL());

    // Click sign up button, verify it redirects to registration page
    $client->clickLink('Sign Up');
    $client->wait()->until(WebDriverExpectedCondition::urlIs(self::BASE_URI.'/register'));
    $this->assertPageTitleSame('Simple Web Apps');
    $this->assertSelectorTextSame('p.title', 'Simple Web Apps');

    // Register, verify it redirects to login page
    $client->submitForm('Register', [
        'registration_form[username]' => $username,
        'registration_form[plainPassword]' => $password,
        'registration_form[agreeTerms]' => true,
    ]);
    $client->wait()->until(WebDriverExpectedCondition::urlIs(self::BASE_URI.'/login'));
    $this->assertPageTitleSame('Simple Web Apps');
    $this->assertSelectorTextSame('p.title', 'Simple Web Apps');

    // Log in, verify it redirects to index page
    $client->submitForm('Log In', [
        '_username' => $username,
        '_password' => $password,
    ]);
    $client->wait()->until(WebDriverExpectedCondition::urlIs(self::BASE_URI.'/'));

    // Log out, verify it redirects to login page
    $client->clickLink('Log Out');
    $client->wait()->until(WebDriverExpectedCondition::urlIs(self::BASE_URI.'/login'));
  }
}
