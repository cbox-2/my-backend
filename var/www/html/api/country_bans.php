<?php
require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// ========== إضافة/تحديث قيود الدولة ==========
if ($action === 'add') {
    header('Content-Type: text/html; charset=utf-8');
    $policy = intval($_POST['policy'] ?? 0);
    $countryCode = trim($_POST['country'] ?? '');
    
    if (!$countryCode) {
        echo '<html><body><script>ld=1;parent.setmsg("fcban","Country is required",2);parent.rcvdformresponse("t_fcban");</script></body></html>';
        exit;
    }
    
    // قائمة أسماء الدول
    $countryNames = [
        'AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa',
        'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba',
        'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas',
        'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus',
        'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda',
        'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BQ' => 'Bonaire', 'BA' => 'Bosnia',
        'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean',
        'BN' => 'Brunei', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi',
        'CV' => 'Cabo Verde', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada',
        'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile',
        'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos Islands', 'CO' => 'Colombia',
        'KM' => 'Comoros', 'CD' => 'Congo', 'CG' => 'Congo', 'CK' => 'Cook Islands',
        'CR' => 'Costa Rica', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CW' => 'Curacao',
        'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'CI' => 'Ivory Coast', 'DK' => 'Denmark',
        'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador',
        'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea',
        'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands', 'FO' => 'Faroe Islands',
        'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana',
        'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia',
        'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar',
        'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe',
        'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island',
        'VA' => 'Holy See', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary',
        'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran',
        'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle of Man', 'IL' => 'Israel',
        'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey',
        'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati',
        'KR' => 'Korea', 'KP' => 'Korea-DPR', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan',
        'LA' => 'Laos', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho',
        'LR' => 'Liberia', 'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania',
        'LU' => 'Luxembourg', 'MO' => 'Macao', 'MK' => 'Macedonia', 'MG' => 'Madagascar',
        'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali',
        'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania',
        'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia',
        'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro',
        'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar',
        'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands',
        'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger',
        'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau',
        'PS' => 'Palestine', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay',
        'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland',
        'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RO' => 'Romania',
        'RU' => 'Russia', 'RW' => 'Rwanda', 'RE' => 'Reunion', 'BL' => 'Saint Barthelemy',
        'SH' => 'Saint Helena', 'KN' => 'Saint Kitts and Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin',
        'PM' => 'Saint Pierre', 'VC' => 'Saint Vincent', 'WS' => 'Samoa', 'SM' => 'San Marino',
        'ST' => 'Sao Tome', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia',
        'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SX' => 'Sint Maarten',
        'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia',
        'ZA' => 'South Africa', 'GS' => 'South Georgia', 'SS' => 'South Sudan', 'ES' => 'Spain',
        'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard',
        'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria',
        'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand',
        'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine',
        'AE' => 'UAE', 'GB' => 'United Kingdom', 'UM' => 'US Minor Outlying', 'US' => 'United States',
        'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela',
        'VN' => 'Vietnam', 'VG' => 'Virgin Islands British', 'VI' => 'Virgin Islands US', 'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe', 'AX' => 'Aland Islands'
    ];
    
    $countryName = $countryNames[$countryCode] ?? 'Unknown';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO country_bans (country_code, country_name, policy) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE policy = VALUES(policy)");
        $stmt->execute([$countryCode, $countryName, $policy]);
        
        $policyText = $policy == 1 ? 'Block all from' : ($policy == -1 ? 'Allow only from' : 'Allow any');
        echo '<html><body><script>';
        echo 'ld=1;';
        echo 'parent.setmsg("fcban","'.$policyText.' '.$countryName.'",1);';
        echo 'parent.rcvdformresponse("t_fcban");';
        echo 'if(parent.frames && parent.frames["cboxcountry"]) {';
        echo '  try { parent.frames["cboxcountry"].location.reload(); } catch(e) {}';
        echo '}';
        echo '</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fcban","Error updating country restriction",2);parent.rcvdformresponse("t_fcban");</script></body></html>';
    }
    exit;
}

// ========== Default ==========
echo '<html><body><script>ld=1;parent.setmsg("fcban","Invalid action",2);parent.rcvdformresponse("t_fcban");</script></body></html>';
exit;