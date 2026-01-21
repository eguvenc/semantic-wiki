

```php
<?php

wfLoadExtension( 'ConfirmEdit' );
wfLoadExtension( 'ConfirmEdit/QuestyCaptcha' ); // Alt modülü mutlaka yükleyin

// Anonymous users can create accounts
$wgGroupPermissions['*']['createaccount'] = true;
// API üzerinden hesap açmaya izin ver
$wgEnableWriteAPI = true;

$apiSecret = 'XXX'; // Strong secret token for API write operations
if (
    isset($_SERVER['HTTP_X_API_SECRET']) 
    && $apiSecret == trim($_SERVER['HTTP_X_API_SECRET'])
) {
    // Basit bir soru-cevap tanımlayın (Hata almamak için şarttır)
    $wgCaptchaQuestions = [
        'Türkiye\'nin başkenti neresidir?' => 'Ankara',
    ];
    // Tetikleyicileri isteğinize göre açık/kapalı yapın
    $wgCaptchaTriggers['edit']          = true; 
    $wgCaptchaTriggers['create']        = true; 
    $wgCaptchaTriggers['addurl']        = true; 
    $wgCaptchaTriggers['createaccount'] = false;
    $wgCaptchaTriggers['badlogin']      = true;
}
```