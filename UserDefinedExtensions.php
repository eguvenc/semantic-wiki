<?php
// ************************************************************************
// Wikibase Suite Deploy Extension.php
// ************************************************************************
//
// File to load MediaWiki extension.
//
// This file will be loaded after all other extensions have been loaded,
// just like as if this code would be at the end of LocalSettings.php.
//
// Make sure to prefix the extensions name with "extensions/" when loading.
// e.g. when extension installation instructions state you need to put
//   wfLoadExtension( 'WikibaseLexeme' );
// here in Wikibase Suite Deploy you need to put
//   wfLoadExtension( 'extensions/WikibaseLexeme' );

error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );

wfLoadExtension( 'ConfirmAccount' );
wfLoadExtension( 'ConfirmEdit' );
wfLoadExtension( 'ConfirmEdit/QuestyCaptcha' ); // Alt modülü mutlaka yükleyin
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

$apiSecret = 'xxx'; // Strong secret token for API write operations
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

/**
 * OAuth settings for Wikibase suite.
 */
/*
$wgRedirectScript = "$wgScriptPath/index.php";
$wgOAuthCallbackUrl = true;
$wgUseCanonicalUrl = true;
$wgGroupPermissions['sysop']['mwoauthmanageconsumer'] = true;
$wgGroupPermissions['bureaucrat']['mwoauthmanageconsumer'] = true;
$wgGroupPermissions['user']['oauth'] = true;
$wgUseHTTPS = true;
// $wgForceHTTPS = true;
$wgCanonicalServer = "https://wikibase.everydayjazz.com";

if ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
    $_SERVER['HTTPS'] = 'on';
}
*/
## E-posta Ayarları
$wgEnableEmail = true; // E-posta özelliklerini etkinleştirir.
$wgEnableUserEmail = true; // Kullanıcıların birbirine e-posta göndermesine izin verir.
$wgEnotifUserTalk = true; // Tartışma sayfaları güncellendiğinde e-posta bildirimi gönderir.
$wgEnotifWatchlist = true; // İzleme listesi sayfaları güncellendiğinde e-posta bildirimi gönderir.
$wgEmailAuthentication = true; // E-posta adreslerinin doğrulanmasını etkinleştirir.

## SMTP Ayarları
$wgSMTP = [
    'host'     => 'smtp.gmail.com',   // Gmail SMTP sunucusu
    'IDHost'   => 'gmail.com',   // Domain adınız (örn: everydayjazz.com)
    'localhost' => 'gmail.com',
    'port'     => 587,                // TLS için port
    'auth'     => true,
    'username' => 'eguvenc@gmail.com',  // Gmail adresiniz
    'password' => 'xxx',     // Gmail uygulama şifresi
    'secure'   => 'tls',               // TLS kullanımı
];
// Giden e-postaların 'Kimden' adresi (GMAIL adresi ile aynı olmalıdır)
$wgPasswordSender = 'eguvenc@gmail.com';
$wgNoReplyAddress = 'eguvenc@gmail.com';

# Development error & debug settings
$wgDevelopmentWarnings = true;
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = false;
$wgDebugToolbar = false;

# OAuth ve email loglarını etkinleştir
$wgDebugLogGroups['oauth'] = '/var/log/mediawiki/oauth.log';
$wgDebugLogGroups['email'] = '/var/log/mediawiki/email.log';

# Tüm debug logları tek dosyada da toplayabilirsin
// $wgDebugLogFile = '/var/log/mediawiki/mw.debug.log';
# OAuth debug
$wgOAuthDebug = true;

# Elasticsearch configuration for FacetedApiSearch and WikibaseSearch
$wgWikibaseRootPath = "/var/www/html";
$wgElasticsearchIndexName = "wikibase_content_first";
$wgElasticsearchBaseUrl = "http://wbs-deploy-elasticsearch-1:9200";
$wgFacetedSuggestProperties = [
    'P1',
];
$wgFacetedSecretToken = $apiSecret; // Set a strong secret token for API write operations
$wgGroupPermissions['bot']['noratelimit'] = true;

// https://github.com/wmde/wikibase-release-pipeline/issues/383

$wgCirrusSearchIndexUpdates = true;
$wgWBCSIndexBaseName = 'wikibase';
$wgWBRepoSettings['searchIndexProperties'] = ['P1', 'P2', 'P3', 'P4', 'P5'. 'P6', 'P7'];
$wgCirrusSearchRescoreProfiles['wikibase'] = [
    'i18n_msg' => 'cirrussearch-qi-profile-wikibase',
    'supported_namespaces' => '0, 120', // 0 = Main, 120 = Wikibase items
    'supported_syntax' => ['simple_bag_of_words'],
    'rescore' => [
        [
            'window' => 8192,
            'query_weight' => 1.0,
            'rescore_query_weight' => 1.0,
            'score_mode' => 'total',
            'type' => 'function_score',
            'function_chain' => 'wsum_inclinks_pv'
        ]
    ]
];

// Make sure that it's also mounted into the jobrunner since that container is doing the actual indexing. 
// (see https://github.com/wmde/wikibase-release-pipeline/pull/390/files)

// ************************************************************************
