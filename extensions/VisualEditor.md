
# ------------------------------------------------------------------------------------------------------------
# VisualEditor Extension Start
# ------------------------------------------------------------------------------------------------------------
wfLoadExtension( 'VisualEditor' );

// VisualEditor ayarları
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgDefaultUserOptions['visualeditor-editor'] = 'visualeditor';
$wgVisualEditorEnableWikitext = true;
$wgVisualEditorEnableDiffPage = true;
$wgVisualEditorSupportedSkins = ['vector', 'vector-2022', 'monobook'];

// Parsoid ayarları (MediaWiki 1.39'da artık dahili)
$wgVirtualRestConfig['modules']['parsoid'] = [
    'url' => $wgServer . $wgScriptPath . '/rest.php',
];

// REST API erişimini aç
$wgVisualEditorRestbaseURL = null;
$wgVisualEditorFullRestbaseURL = $wgServer . $wgScriptPath . '/rest.php/';

// Güvenlik ayarları
$wgVisualEditorAllowExternalLinking = true;

# ------------------------------------------------------------------------------------------------------------
# VisualEditor Extension End
# ------------------------------------------------------------------------------------------------------------