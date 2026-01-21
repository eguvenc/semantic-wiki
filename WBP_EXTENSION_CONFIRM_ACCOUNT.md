
## Installation Of Confirm Account Extension


```sh
git clone -b REL1_44 https://gerrit.wikimedia.org/r/mediawiki/extensions/ConfirmAccount
```

```sh
php maintenance/update.php
```


```php
error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );

wfLoadExtension( 'ConfirmAccount' );
wfLoadExtension( 'WikibaseFacetedSearch' );
wfLoadExtension( 'extensions/FacetedApiSearch' );

$wgGroupPermissions['*']['createaccount'] = false; // REQUIRED to enforce account requests via this extension
$wgGroupPermissions['bureaucrat']['createaccount'] = true; // optional to allow account creation by this trusted user group
$wgGroupPermissions['bureaucrat']['confirmaccount'] = true;
$wgGroupPermissions['sysop']['confirmaccount'] = true;

// disable Captcha for account creation and requestaccount by default
$wgCaptchaClass = 'QuestyCaptcha';
$wgCaptchaTriggers['createaccount'] = false;
$wgCaptchaTriggers['requestaccount'] = false;

# ConfirmAccount is enabled
$wgConfirmAccountRequestFormItems = [
    'UserName'   => [ 'enabled' => true ],
    'RealName'   => [ 'enabled' => true ],
    'Email'      => [ 'enabled' => true ],
    'Biography'  => [ 'enabled' => false ],
    'AreasOfInterest' => [ 'enabled' => false ],
];
# Mail confirmation is mandatory
$wgConfirmAccountEmailEnabled = true;
# Admin confirmation is mandatory
$wgConfirmAccountApproval = true;
# Mail confirmation open
$wgEmailAuthentication = true;
# RequestAccount API open
$wgEnableWriteAPI = true;
```

## Confirm Account Hook Extension


{
  "name": "ConfirmAccountHook",
  "version": "1.0.0",
  "author": "Ersin Güvenç",
  "description": "ConfirmAccount Hook Extension for MediaWiki Confirm Account.",
  "type": "extension",

  "AutoloadNamespaces": {
    "ConfirmAccountHook\\": "src/"
  },

  "Hooks": {
    "ConfirmAccount::approved": "ConfirmAccountHook\\ConfirmAccountApprovedHook::onApproved"
  },

  "manifest_version": 2
}



## Checking HOOKS


```bash
root@9ca5ddd2b35d:/var/www/html# echo "print_r(array_keys(\$GLOBALS['wgHooks']));" | php maintenance/run.php eval.php | grep -i Confirm
    [28] => ConfirmAccount::approved
```

## Checking ACCOUNT REQUESTS


```bash
root@9ca5ddd2b35d:/var/www/html# echo "SELECT acr_name, acr_email FROM account_requests;" | php maintenance/run.php sql.php
stdClass Object
(
    [acr_name] => TestUserX
    [acr_email] => xturknet@hotmail.com
)
stdClass Object
(
    [acr_name] => Guvenc
    [acr_email] => me@me.com
)
```
