[![PHP version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg?style=for-the-badge)](https://php.net)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/martin1982/oauth2-soundcloud/Continuous%20Integration?style=for-the-badge)](https://github.com/martin1982/oauth2-soundcloud/actions/workflows/continuous-integration.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/martin1982/oauth2-soundcloud.svg?style=for-the-badge)](https://packagist.org/packages/martin1982/oauth2-soundcloud)
[![Latest Stable Version](https://img.shields.io/packagist/v/martin1982/oauth2-soundcloud.svg?style=for-the-badge)](https://packagist.org/packages/martin1982/oauth2-soundcloud)

# SoundCloud Provider for OAuth 2.0 Client

This package provides SoundCloud OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

You can install this package using Composer:

```
composer require martin1982/oauth2-soundcloud
```

You will then need to:
* run ``composer install`` to get these dependencies added to your vendor directory
* add the autoloader to your application with this line: ``require('vendor/autoload.php');``

## Usage

Usage is the same as The League's OAuth client, using `\Martin1982\OAuth2\Client\Provider\SoundCloud` as the provider.

### Authorization Code Flow

```php
$provider = new Martin1982\OAuth2\Client\Provider\SoundCloud([
    'clientId'     => '{soundcloud-client-id}',
    'clientSecret' => '{soundcloud-client-secret}',
    'redirectUri'  => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    
    $_SESSION['oauth2state'] = $provider->getState();
    
    header('Location: ' . $authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    echo 'Invalid state.';
    exit;

}

// Try to get an access token (using the authorization code grant)
$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

// Optional: Now you have a token you can look up a users profile data
try {

    // We got an access token, let's now get the user's details
    /** @var \Martin1982\OAuth2\Client\Provider\SoundCloudResourceOwner $user */
    $user = $provider->getResourceOwner($token);

    // Use these details to create a new profile
    printf('Hello %s!', $user->getFirstname());
    
    echo '<pre>';
    var_dump($user);
    echo '</pre>';

} catch (Exception $e) {

    // Failed to get user details
    exit('Damned...');
}

echo '<pre>';
// Use this to interact with an API on the users behalf
var_dump($token->getToken());
# string(217) "CAADAppfn3msBAI7tZBLWg...

// The time (in epoch time) when an access token will expire
var_dump($token->getExpires());
# int(1436825866)
echo '</pre>';
```

## Credits

- Based on oauth2-deezer by [Julien Bornstein](https://github.com/julienbornstein)

## License

The MIT License (MIT). Please see [License File](https://github.com/martin1982/oauth2-soundcloud/blob/main/LICENSE) for more information.
