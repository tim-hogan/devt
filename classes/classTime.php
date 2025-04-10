<?php
class classTimeHelpers
{

	public static $countries =
		[
			"AF" => "Afghanistan",
			"AL" => "Albania",
			"DZ" => "Algeria",
			"AS" => "American Samoa",
			"AD" => "Andorra",
			"AO" => "Angola",
			"AI" => "Anguilla",
			"AQ" => "Antarctica",
			"AG" => "Antigua and Barbuda",
			"AR" => "Argentina",
			"AM" => "Armenia",
			"AW" => "Aruba",
			"AU" => "Australia",
			"AT" => "Austria",
			"AZ" => "Azerbaijan",
			"BS" => "Bahamas",
			"BH" => "Bahrain",
			"BD" => "Bangladesh",
			"BB" => "Barbados",
			"BY" => "Belarus",
			"BE" => "Belgium",
			"BZ" => "Belize",
			"BJ" => "Benin",
			"BM" => "Bermuda",
			"BT" => "Bhutan",
			"BO" => "Bolivia",
			"BA" => "Bosnia and Herzegovina",
			"BW" => "Botswana",
			"BV" => "Bouvet Island",
			"BR" => "Brazil",
			"IO" => "British Indian Ocean Territory",
			"BN" => "Brunei Darussalam",
			"BG" => "Bulgaria",
			"BF" => "Burkina Faso",
			"BI" => "Burundi",
			"KH" => "Cambodia",
			"CM" => "Cameroon",
			"CA" => "Canada",
			"CV" => "Cape Verde",
			"KY" => "Cayman Islands",
			"CF" => "Central African Republic",
			"TD" => "Chad",
			"CL" => "Chile",
			"CN" => "China",
			"CX" => "Christmas Island",
			"CC" => "Cocos (Keeling) Islands",
			"CO" => "Colombia",
			"KM" => "Comoros",
			"CG" => "Congo",
			"CD" => "Congo, the Democratic Republic of the",
			"CK" => "Cook Islands",
			"CR" => "Costa Rica",
			"CI" => "Cote D'Ivoire",
			"HR" => "Croatia",
			"CU" => "Cuba",
			"CY" => "Cyprus",
			"CZ" => "Czech Republic",
			"DK" => "Denmark",
			"DJ" => "Djibouti",
			"DM" => "Dominica",
			"DO" => "Dominican Republic",
			"EC" => "Ecuador",
			"EG" => "Egypt",
			"SV" => "El Salvador",
			"GQ" => "Equatorial Guinea",
			"ER" => "Eritrea",
			"EE" => "Estonia",
			"ET" => "Ethiopia",
			"FK" => "Falkland Islands (Malvinas)",
			"FO" => "Faroe Islands",
			"FJ" => "Fiji",
			"FI" => "Finland",
			"FR" => "France",
			"GF" => "French Guiana",
			"PF" => "French Polynesia",
			"TF" => "French Southern Territories",
			"GA" => "Gabon",
			"GM" => "Gambia",
			"GE" => "Georgia",
			"DE" => "Germany",
			"GH" => "Ghana",
			"GI" => "Gibraltar",
			"GR" => "Greece",
			"GL" => "Greenland",
			"GD" => "Grenada",
			"GP" => "Guadeloupe",
			"GU" => "Guam",
			"GT" => "Guatemala",
			"GN" => "Guinea",
			"GW" => "Guinea-Bissau",
			"GY" => "Guyana",
			"HT" => "Haiti",
			"HM" => "Heard Island and Mcdonald Islands",
			"VA" => "Holy See (Vatican City State)",
			"HN" => "Honduras",
			"HK" => "Hong Kong",
			"HU" => "Hungary",
			"IS" => "Iceland",
			"IN" => "India",
			"ID" => "Indonesia",
			"IR" => "Iran, Islamic Republic of",
			"IQ" => "Iraq",
			"IE" => "Ireland",
			"IL" => "Israel",
			"IT" => "Italy",
			"JM" => "Jamaica",
			"JP" => "Japan",
			"JO" => "Jordan",
			"KZ" => "Kazakhstan",
			"KE" => "Kenya",
			"KI" => "Kiribati",
			"KP" => "Korea, Democratic People's Republic of",
			"KR" => "Korea, Republic of",
			"KW" => "Kuwait",
			"KG" => "Kyrgyzstan",
			"LA" => "Lao People's Democratic Republic",
			"LV" => "Latvia",
			"LB" => "Lebanon",
			"LS" => "Lesotho",
			"LR" => "Liberia",
			"LY" => "Libyan Arab Jamahiriya",
			"LI" => "Liechtenstein",
			"LT" => "Lithuania",
			"LU" => "Luxembourg",
			"MO" => "Macao",
			"MK" => "Macedonia, the Former Yugoslav Republic of",
			"MG" => "Madagascar",
			"MW" => "Malawi",
			"MY" => "Malaysia",
			"MV" => "Maldives",
			"ML" => "Mali",
			"MT" => "Malta",
			"MH" => "Marshall Islands",
			"MQ" => "Martinique",
			"MR" => "Mauritania",
			"MU" => "Mauritius",
			"YT" => "Mayotte",
			"MX" => "Mexico",
			"FM" => "Micronesia, Federated States of",
			"MD" => "Moldova, Republic of",
			"MC" => "Monaco",
			"MN" => "Mongolia",
			"MS" => "Montserrat",
			"MA" => "Morocco",
			"MZ" => "Mozambique",
			"MM" => "Myanmar",
			"NA" => "Namibia",
			"NR" => "Nauru",
			"NP" => "Nepal",
			"NL" => "Netherlands",
			"AN" => "Netherlands Antilles",
			"NC" => "New Caledonia",
			"NZ" => "New Zealand",
			"NI" => "Nicaragua",
			"NE" => "Niger",
			"NG" => "Nigeria",
			"NU" => "Niue",
			"NF" => "Norfolk Island",
			"MP" => "Northern Mariana Islands",
			"NO" => "Norway",
			"OM" => "Oman",
			"PK" => "Pakistan",
			"PW" => "Palau",
			"PS" => "Palestinian Territory, Occupied",
			"PA" => "Panama",
			"PG" => "Papua New Guinea",
			"PY" => "Paraguay",
			"PE" => "Peru",
			"PH" => "Philippines",
			"PN" => "Pitcairn",
			"PL" => "Poland",
			"PT" => "Portugal",
			"PR" => "Puerto Rico",
			"QA" => "Qatar",
			"RE" => "Reunion",
			"RO" => "Romania",
			"RU" => "Russian Federation",
			"RW" => "Rwanda",
			"SH" => "Saint Helena",
			"KN" => "Saint Kitts and Nevis",
			"LC" => "Saint Lucia",
			"PM" => "Saint Pierre and Miquelon",
			"VC" => "Saint Vincent and the Grenadines",
			"WS" => "Samoa",
			"SM" => "San Marino",
			"ST" => "Sao Tome and Principe",
			"SA" => "Saudi Arabia",
			"SN" => "Senegal",
			"CS" => "Serbia and Montenegro",
			"SC" => "Seychelles",
			"SL" => "Sierra Leone",
			"SG" => "Singapore",
			"SK" => "Slovakia",
			"SI" => "Slovenia",
			"SB" => "Solomon Islands",
			"SO" => "Somalia",
			"ZA" => "South Africa",
			"GS" => "South Georgia and the South Sandwich Islands",
			"ES" => "Spain",
			"LK" => "Sri Lanka",
			"SD" => "Sudan",
			"SR" => "Suriname",
			"SJ" => "Svalbard and Jan Mayen",
			"SZ" => "Swaziland",
			"SE" => "Sweden",
			"CH" => "Switzerland",
			"SY" => "Syrian Arab Republic",
			"TW" => "Taiwan, Province of China",
			"TJ" => "Tajikistan",
			"TZ" => "Tanzania, United Republic of",
			"TH" => "Thailand",
			"TL" => "Timor-Leste",
			"TG" => "Togo",
			"TK" => "Tokelau",
			"TO" => "Tonga",
			"TT" => "Trinidad and Tobago",
			"TN" => "Tunisia",
			"TR" => "Turkey",
			"TM" => "Turkmenistan",
			"TC" => "Turks and Caicos Islands",
			"TV" => "Tuvalu",
			"UG" => "Uganda",
			"UA" => "Ukraine",
			"AE" => "United Arab Emirates",
			"GB" => "United Kingdom",
			"US" => "United States",
			"UM" => "United States Minor Outlying Islands",
			"UY" => "Uruguay",
			"UZ" => "Uzbekistan",
			"VU" => "Vanuatu",
			"VE" => "Venezuela",
			"VN" => "Viet Nam",
			"VG" => "Virgin Islands, British",
			"VI" => "Virgin Islands, U.s.",
			"WF" => "Wallis and Futuna",
			"EH" => "Western Sahara",
			"YE" => "Yemen",
			"ZM" => "Zambia",
			"ZW" => "Zimbabwe"
		];

	public static $iso_639_1 = [
		'ab' => 'Abkhazian',
		'aa' => 'Afar',
		'af' => 'Afrikaans',
		'ak' => 'Akan',
		'sq' => 'Albanian',
		'am' => 'Amharic',
		'ar' => 'Arabic',
		'an' => 'Aragonese',
		'hy' => 'Armenian',
		'as' => 'Assamese',
		'av' => 'Avaric',
		'ae' => 'Avestan',
		'ay' => 'Aymara',
		'az' => 'Azerbaijani',
		'bm' => 'Bambara',
		'ba' => 'Bashkir',
		'eu' => 'Basque',
		'be' => 'Belarusian',
		'bn' => 'Bengali',
		'bh' => 'Bihari languages',
		'bi' => 'Bislama',
		'bs' => 'Bosnian',
		'br' => 'Breton',
		'bg' => 'Bulgarian',
		'my' => 'Burmese',
		'ca' => 'Catalan, Valencian',
		'km' => 'Central Khmer',
		'ch' => 'Chamorro',
		'ce' => 'Chechen',
		'ny' => 'Chichewa, Chewa, Nyanja',
		'zh' => 'Chinese',
		'cu' => 'Church Slavonic, Old Bulgarian, Old Church Slavonic',
		'cv' => 'Chuvash',
		'kw' => 'Cornish',
		'co' => 'Corsican',
		'cr' => 'Cree',
		'hr' => 'Croatian',
		'cs' => 'Czech',
		'da' => 'Danish',
		'dv' => 'Divehi, Dhivehi, Maldivian',
		'nl' => 'Dutch, Flemish',
		'dz' => 'Dzongkha',
		'en' => 'English',
		'eo' => 'Esperanto',
		'et' => 'Estonian',
		'ee' => 'Ewe',
		'fo' => 'Faroese',
		'fj' => 'Fijian',
		'fi' => 'Finnish',
		'fr' => 'French',
		'ff' => 'Fulah',
		'gd' => 'Gaelic, Scottish Gaelic',
		'gl' => 'Galician',
		'lg' => 'Ganda',
		'ka' => 'Georgian',
		'de' => 'German',
		'ki' => 'Gikuyu, Kikuyu',
		'el' => 'Greek (Modern)',
		'kl' => 'Greenlandic, Kalaallisut',
		'gn' => 'Guarani',
		'gu' => 'Gujarati',
		'ht' => 'Haitian, Haitian Creole',
		'ha' => 'Hausa',
		'he' => 'Hebrew',
		'hz' => 'Herero',
		'hi' => 'Hindi',
		'ho' => 'Hiri Motu',
		'hu' => 'Hungarian',
		'is' => 'Icelandic',
		'io' => 'Ido',
		'ig' => 'Igbo',
		'id' => 'Indonesian',
		'ia' => 'Interlingua (International Auxiliary Language Association)',
		'ie' => 'Interlingue',
		'iu' => 'Inuktitut',
		'ik' => 'Inupiaq',
		'ga' => 'Irish',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'jv' => 'Javanese',
		'kn' => 'Kannada',
		'kr' => 'Kanuri',
		'ks' => 'Kashmiri',
		'kk' => 'Kazakh',
		'rw' => 'Kinyarwanda',
		'kv' => 'Komi',
		'kg' => 'Kongo',
		'ko' => 'Korean',
		'kj' => 'Kwanyama, Kuanyama',
		'ku' => 'Kurdish',
		'ky' => 'Kyrgyz',
		'lo' => 'Lao',
		'la' => 'Latin',
		'lv' => 'Latvian',
		'lb' => 'Letzeburgesch, Luxembourgish',
		'li' => 'Limburgish, Limburgan, Limburger',
		'ln' => 'Lingala',
		'lt' => 'Lithuanian',
		'lu' => 'Luba-Katanga',
		'mk' => 'Macedonian',
		'mg' => 'Malagasy',
		'ms' => 'Malay',
		'ml' => 'Malayalam',
		'mt' => 'Maltese',
		'gv' => 'Manx',
		'mi' => 'Maori',
		'mr' => 'Marathi',
		'mh' => 'Marshallese',
		'ro' => 'Moldovan, Moldavian, Romanian',
		'mn' => 'Mongolian',
		'na' => 'Nauru',
		'nv' => 'Navajo, Navaho',
		'nd' => 'Northern Ndebele',
		'ng' => 'Ndonga',
		'ne' => 'Nepali',
		'se' => 'Northern Sami',
		'no' => 'Norwegian',
		'nb' => 'Norwegian Bokm�l',
		'nn' => 'Norwegian Nynorsk',
		'ii' => 'Nuosu, Sichuan Yi',
		'oc' => 'Occitan (post 1500)',
		'oj' => 'Ojibwa',
		'or' => 'Oriya',
		'om' => 'Oromo',
		'os' => 'Ossetian, Ossetic',
		'pi' => 'Pali',
		'pa' => 'Panjabi, Punjabi',
		'ps' => 'Pashto, Pushto',
		'fa' => 'Persian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'qu' => 'Quechua',
		'rm' => 'Romansh',
		'rn' => 'Rundi',
		'ru' => 'Russian',
		'sm' => 'Samoan',
		'sg' => 'Sango',
		'sa' => 'Sanskrit',
		'sc' => 'Sardinian',
		'sr' => 'Serbian',
		'sn' => 'Shona',
		'sd' => 'Sindhi',
		'si' => 'Sinhala, Sinhalese',
		'sk' => 'Slovak',
		'sl' => 'Slovenian',
		'so' => 'Somali',
		'st' => 'Sotho, Southern',
		'nr' => 'South Ndebele',
		'es' => 'Spanish, Castilian',
		'su' => 'Sundanese',
		'sw' => 'Swahili',
		'ss' => 'Swati',
		'sv' => 'Swedish',
		'tl' => 'Tagalog',
		'ty' => 'Tahitian',
		'tg' => 'Tajik',
		'ta' => 'Tamil',
		'tt' => 'Tatar',
		'te' => 'Telugu',
		'th' => 'Thai',
		'bo' => 'Tibetan',
		'ti' => 'Tigrinya',
		'to' => 'Tonga (Tonga Islands)',
		'ts' => 'Tsonga',
		'tn' => 'Tswana',
		'tr' => 'Turkish',
		'tk' => 'Turkmen',
		'tw' => 'Twi',
		'ug' => 'Uighur, Uyghur',
		'uk' => 'Ukrainian',
		'ur' => 'Urdu',
		'uz' => 'Uzbek',
		've' => 'Venda',
		'vi' => 'Vietnamese',
		'vo' => 'Volap_k',
		'wa' => 'Walloon',
		'cy' => 'Welsh',
		'fy' => 'Western Frisian',
		'wo' => 'Wolof',
		'xh' => 'Xhosa',
		'yi' => 'Yiddish',
		'yo' => 'Yoruba',
		'za' => 'Zhuang, Chuang',
		'zu' => 'Zulu'
	];
	public static $iso_639_2 = [
		'abk' => 'Abkhazian',
		'aar' => 'Afar',
		'ace' => 'Achinese',
		'ach' => 'Acoli',
		'ada' => 'Adangme',
		'ady' => 'Adyghe',
		'afh' => 'Afrihili',
		'afr' => 'Afrikaans',
		'ain' => 'Ainu',
		'aka' => 'Akan',
		'akk' => 'Akkadian',
		'ale' => 'Aleut',
		'alg' => 'Algonquian languages',
		'alt' => 'Southern Altai',
		'sqi' => 'Albanian',
		'amh' => 'Amharic',
		'ang' => 'English, Old',
		'anp' => 'Angika',
		'apa' => 'Apache languages',
		'ara' => 'Arabic',
		'arc' => 'Aramaic',
		'arg' => 'Aragonese',
		'arn' => 'Mapuche',
		'arp' => 'Arapaho',
		'art' => 'Artificial languages',
		'arw' => 'Arawak',
		'hye' => 'Armenian',
		'asm' => 'Assamese',
		'ast' => 'Asturian; Bable; Leonese; Asturleonese',
		'ath' => 'Athapascan',
		'aus' => 'Australian',
		'ava' => 'Avaric',
		'ave' => 'Avestan',
		'awa' => 'Awadhi',
		'aym' => 'Aymara',
		'aze' => 'Azerbaijani',
		'bm' => 'Bambara',
		'ba' => 'Bashkir',
		'eu' => 'Basque',
		'be' => 'Belarusian',
		'bn' => 'Bengali',
		'bh' => 'Bihari languages',
		'bi' => 'Bislama',
		'bs' => 'Bosnian',
		'br' => 'Breton',
		'bg' => 'Bulgarian',
		'my' => 'Burmese',
		'ca' => 'Catalan, Valencian',
		'km' => 'Central Khmer',
		'ch' => 'Chamorro',
		'ce' => 'Chechen',
		'ny' => 'Chichewa, Chewa, Nyanja',
		'zh' => 'Chinese',
		'cu' => 'Church Slavonic, Old Bulgarian, Old Church Slavonic',
		'cv' => 'Chuvash',
		'kw' => 'Cornish',
		'co' => 'Corsican',
		'cr' => 'Cree',
		'hr' => 'Croatian',
		'cs' => 'Czech',
		'da' => 'Danish',
		'dv' => 'Divehi, Dhivehi, Maldivian',
		'nl' => 'Dutch, Flemish',
		'dz' => 'Dzongkha',
		'en' => 'English',
		'eo' => 'Esperanto',
		'et' => 'Estonian',
		'ee' => 'Ewe',
		'fo' => 'Faroese',
		'fj' => 'Fijian',
		'fi' => 'Finnish',
		'fr' => 'French',
		'ff' => 'Fulah',
		'gd' => 'Gaelic, Scottish Gaelic',
		'gl' => 'Galician',
		'lg' => 'Ganda',
		'ka' => 'Georgian',
		'de' => 'German',
		'ki' => 'Gikuyu, Kikuyu',
		'el' => 'Greek (Modern)',
		'kl' => 'Greenlandic, Kalaallisut',
		'gn' => 'Guarani',
		'gu' => 'Gujarati',
		'ht' => 'Haitian, Haitian Creole',
		'ha' => 'Hausa',
		'he' => 'Hebrew',
		'hz' => 'Herero',
		'hi' => 'Hindi',
		'ho' => 'Hiri Motu',
		'hu' => 'Hungarian',
		'is' => 'Icelandic',
		'io' => 'Ido',
		'ig' => 'Igbo',
		'id' => 'Indonesian',
		'ia' => 'Interlingua (International Auxiliary Language Association)',
		'ie' => 'Interlingue',
		'iu' => 'Inuktitut',
		'ik' => 'Inupiaq',
		'ga' => 'Irish',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'jv' => 'Javanese',
		'kn' => 'Kannada',
		'kr' => 'Kanuri',
		'ks' => 'Kashmiri',
		'kk' => 'Kazakh',
		'rw' => 'Kinyarwanda',
		'kv' => 'Komi',
		'kg' => 'Kongo',
		'ko' => 'Korean',
		'kj' => 'Kwanyama, Kuanyama',
		'ku' => 'Kurdish',
		'ky' => 'Kyrgyz',
		'lo' => 'Lao',
		'la' => 'Latin',
		'lv' => 'Latvian',
		'lb' => 'Letzeburgesch, Luxembourgish',
		'li' => 'Limburgish, Limburgan, Limburger',
		'ln' => 'Lingala',
		'lt' => 'Lithuanian',
		'lu' => 'Luba-Katanga',
		'mk' => 'Macedonian',
		'mg' => 'Malagasy',
		'ms' => 'Malay',
		'ml' => 'Malayalam',
		'mt' => 'Maltese',
		'gv' => 'Manx',
		'mi' => 'Maori',
		'mr' => 'Marathi',
		'mh' => 'Marshallese',
		'ro' => 'Moldovan, Moldavian, Romanian',
		'mn' => 'Mongolian',
		'na' => 'Nauru',
		'nv' => 'Navajo, Navaho',
		'nd' => 'Northern Ndebele',
		'ng' => 'Ndonga',
		'ne' => 'Nepali',
		'se' => 'Northern Sami',
		'no' => 'Norwegian',
		'nb' => 'Norwegian Bokm�l',
		'nn' => 'Norwegian Nynorsk',
		'ii' => 'Nuosu, Sichuan Yi',
		'oc' => 'Occitan (post 1500)',
		'oj' => 'Ojibwa',
		'or' => 'Oriya',
		'om' => 'Oromo',
		'os' => 'Ossetian, Ossetic',
		'pi' => 'Pali',
		'pa' => 'Panjabi, Punjabi',
		'ps' => 'Pashto, Pushto',
		'fa' => 'Persian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'qu' => 'Quechua',
		'rm' => 'Romansh',
		'rn' => 'Rundi',
		'ru' => 'Russian',
		'sm' => 'Samoan',
		'sg' => 'Sango',
		'sa' => 'Sanskrit',
		'sc' => 'Sardinian',
		'sr' => 'Serbian',
		'sn' => 'Shona',
		'sd' => 'Sindhi',
		'si' => 'Sinhala, Sinhalese',
		'sk' => 'Slovak',
		'sl' => 'Slovenian',
		'so' => 'Somali',
		'st' => 'Sotho, Southern',
		'nr' => 'South Ndebele',
		'es' => 'Spanish, Castilian',
		'su' => 'Sundanese',
		'sw' => 'Swahili',
		'ss' => 'Swati',
		'sv' => 'Swedish',
		'tl' => 'Tagalog',
		'ty' => 'Tahitian',
		'tg' => 'Tajik',
		'ta' => 'Tamil',
		'tt' => 'Tatar',
		'te' => 'Telugu',
		'th' => 'Thai',
		'bo' => 'Tibetan',
		'ti' => 'Tigrinya',
		'to' => 'Tonga (Tonga Islands)',
		'ts' => 'Tsonga',
		'tn' => 'Tswana',
		'tr' => 'Turkish',
		'tk' => 'Turkmen',
		'tw' => 'Twi',
		'ug' => 'Uighur, Uyghur',
		'uk' => 'Ukrainian',
		'ur' => 'Urdu',
		'uz' => 'Uzbek',
		've' => 'Venda',
		'vi' => 'Vietnamese',
		'vo' => 'Volap_k',
		'wa' => 'Walloon',
		'cy' => 'Welsh',
		'fy' => 'Western Frisian',
		'wo' => 'Wolof',
		'xh' => 'Xhosa',
		'yi' => 'Yiddish',
		'yo' => 'Yoruba',
		'za' => 'Zhuang, Chuang',
		'zu' => 'Zulu'
		];

	public static $phoneprefix =
		[
		"AU" => "61",
		"CA" => "1",
		"DE" => "49",
		"FR" => "33",
		"IT" => "39",
		"NL" => "31",
		"NZ" => "64",
		"UK" => "44",
		"US" => "1",
		];

	private $tdllist =
	[
	  //2D
		//Australia
		"com.au",
		"net.au",
		"org.au",
		"gov.au",
		"asn.au",
		"id.au",
		"csiro.au",

		//New Zealand
		"co.nz",
		"govt.nz",
		"net.nz",
		"org.nz",
		"ac.nz",
		"school.nz",
		"geek.nz",
		"gen.nz",
		"kiwi.nz",
		"cri.nz",
		"health.nz",
		"iwi.nz",
		"mil.nz",
		"parliament.nz",

	//1D

		//Australia
		"au",

		//New Zealand
		"nz",
		"kiwi",

		//World
		"com",
	];

	const shortMonths =
	[
		"","JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC",
	];
	public function getTLD($strName)
	{
		foreach ($this->tdllist as $d)
		{
			if (strtoupper(substr($strName,-(strlen($d)))) == strtoupper($d) )
				return $d;
		}
		return $strName;
	}

	public static function getNameServers($domain)
	{
		if (exec("dig +short {$domain} NS",$out,$rslt) !== false)
			return $out;
		return null;
	}

	public static function getDNSNameIP($domain)
	{
		if (exec("dig +short {$domain} A",$out,$rslt) !== false)
		if ($out && strlen($out[0]) > 0)
		   return trim($out[0],".");
		return null;
	}

	public static function getLanguagesISO639_1($order='name')
	{
		$list = self::$iso_639_1;
		if ($order == 'name')
			asort($list);
		else
			ksort($list);
		return $list;
	}

	public static function getLanguagesISO639_2($order='name')
	{
		$list = self::$iso_639_2;
		if ($order == 'name')
			asort($list);
		else
			ksort($list);
		return $list;
	}

	public static function timeFormat($Time,$strFormat,$strTimeZone = null)
	{
		$date = null;
		if (gettype($Time) !== "string")
		{
			$date = $Time;
		}
		else
		{
			if (null != $Time && strlen($Time) > 0)
			{
				if ($strTimeZone== NULL || strlen($strTimeZone)==0)
					$strTimeZone = 'UTC';
				$date = new DateTime($Time);
			}
		}

		if ($date)
		{
			if ($strTimeZone== NULL || strlen($strTimeZone)==0)
				$strTimeZone = 'UTC';
			$date->setTimezone(new DateTimeZone($strTimeZone));
			return $date->format($strFormat);
		}

		return null;
	}

	public static function timeFormatDB($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"Y-m-d H:i:s",$strTimeZone);
	}

	public static function timeFormat24Hr($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"j/n/Y H:i:s",$strTimeZone);
	}

	public static function timeFormat24HrUS($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"n/j/Y H:i:s",$strTimeZone);
	}

	public static function timeFormat12Hr($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"j/n/Y h:ia",$strTimeZone);
	}

	public static function timeFormat12HrUS($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"n/j/Y h:ia",$strTimeZone);
	}

	public static function timeFormatnthDate($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"jS M Y",$strTimeZone);
	}

	public static function timeFormatnthDateTime1($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"D jS M Y h:ia",$strTimeZone);
	}

	public static function timeFormatnthDateTime2($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"D jS M Y H:i:s",$strTimeZone);
	}

	public static function  timeFormatDateTimeLocal($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat($time,"Y-m-d\TH:i:s",$strTimeZone);
	}

	public static function timeFormatMilliToHHMM($time,$strTimeZone=null)
	{
		return classTimeHelpers::timeFormat((new DateTime())->setTimestamp(round($time / 1000.0)),"H:i",$strTimeZone);
	}

	public static function timeFormatISO8601($Time,$strTimeZone=null)
	{
		$date = null;
		if (gettype($Time) !== "string")
		{
			$date = $Time;
		}
		else
		{
			if (null != $Time && strlen($Time) > 0)
			{
				if ($strTimeZone== NULL || strlen($strTimeZone)==0)
					$strTimeZone = 'UTC';
				$date = new DateTime($Time);
			}
		}

		if ($date)
		{
			if ($strTimeZone== NULL || strlen($strTimeZone)==0)
				$strTimeZone = 'UTC';
			$date->setTimezone(new DateTimeZone($strTimeZone));
			return $date->format('Y-m-d') . "T" . $date->format('H:i:s') . "Z";
		}

		return null;

	}

	public static function smartTime($strTime,$strTimeZone=null)
	{
	   if ($strTimeZone==null || strlen($strTimeZone)==0)
			$strTimeZone = 'UTC';

	   $dt = new DateTime('now');
	   $dt->setTimezone(new DateTimeZone($strTimeZone));

	   if (classTimeHelpers::timeFormat($strTime,'Y-m-d',$strTimeZone) == $dt->format('Y-m-d'))
	   {
			return "Today " . classTimeHelpers::timeFormat($strTime,'H:i:s',$strTimeZone);
	   }
	   else
	   {
			return classTimeHelpers::timeFormat($strTime,'D d M Y H:i:s',$strTimeZone);
	   }
	}

	public static function smartDay($strTime,$strTimeZone=null)
	{
	   if ($strTimeZone==null || strlen($strTimeZone)==0)
			$strTimeZone = 'UTC';

	   $dt = new DateTime('now');
	   $dt->setTimezone(new DateTimeZone($strTimeZone));

	   if (classTimeHelpers::timeFormat($strTime,'Y-m-d',$strTimeZone) == $dt->format('Y-m-d'))
	   {
			return "Today";
	   }
	   else
	   {
		   return classTimeHelpers::timeFormat($strTime,'D jS M Y',$strTimeZone);
	   }
	}

	public static function smartTimeDiff($dt)
	{
		$diff = abs((new DateTime('now'))->getTimestamp() - $dt->getTimestamp());

		if ($diff > (86400*2) )
			return floor($diff/86400) . " days";
		else
		if ($diff > 86400)
		{
		   $v =  sprintf('%3.1f',floor($diff/8640) / 10.0);
		   if (substr($v,2,1) == "0")
				return $v . " day";
		   else
				return $v . " days";
		}
		else
		if ($diff > 7200)
		{
			return floor($diff/3600) . " hours";
		}
		else
		if ($diff > 3600)
		{
		   $v =  sprintf('%3.1f',floor($diff/360) / 10.0);
		   if (substr($v,2,1) == "0")
				return $v . " hour";
		   else
				return $v . " hours";

		}
		else
		if ($diff > 120)
		{
			return floor($diff/60) . " minutes";
		}
		else
		if ($diff > 60)
		{
		$v =  sprintf('%3.1f',floor($diff/6) / 10.0);
		if (substr($v,2,1) == "0")
				return $v . " minute";
		   else
				return $v . " minutes";
		}
		else
		{
			echo $diff . " seconds";
		}
	}

	public function age($dt1,$dt2)
	{
		$i1 = abs($dt1->getTimestamp() - $dt2->getTimestamp());
		if ($i1 < 60)
			return sprintf('%ds',floor($i1));
		else
		if ($i1 < 3600)
		{
			$i1 = floor($i1 / 60);
			return sprintf('%dm',$i1);
		}
		else
		if ($i1 < 86400)
		{
			$i1 = floor($i1 / 3600);
			return sprintf('%dh',$i1);
		}
		else
		{
			$i1 = floor($i1 / 86400);
			return sprintf('%dd',$i1);
		}
	}

	public static function timeDiff($d1,$d2=null)
	{
		$d3 = $d2;
		if (!$d3)
			$d3 = new DateTime('now');
		return $d3->getTimestamp() - $d1->getTimestamp();
	}

	public function everyCountryName()
	{
		$rslt = array();
		foreach($this->countries as $country)
			array_push($rstl,$country);
		return $rslt;
	}

	public static function ipDecode($ip=null)
	{
		//Get the timezone and county of the requested ip address
		$check_ip = $ip;
		if ($ip==null && isset($_SERVER['REMOTE_ADDR']))
			$check_ip = $_SERVER['REMOTE_ADDR'];
		if ($check_ip)
		{
			try {
				$ipInfo = file_get_contents('http://ip-api.com/json/' . $check_ip);
				if ($ipInfo && strlen($ipInfo) > 0)
				{
					return $ipInfo = json_decode($ipInfo,true);
				}
			}

			catch (Exception $e) {
				error_log("Exception trying to get timezone from ip-api.com {$e->getMessage()}");
			}

		}

		return null;
	}

}

class DateMonth
{
	private $_start;
	private $_end;

	function __construct($year=0,$month=0)
	{
		$this->create($year=0,$month=0);
	}

	private function pad($v,$n)
	{
		return sprintf("%0{$n}d",$v);
	}

	private function create($year=0,$month=0)
	{
		if ($year >0 && $month > 0)
		{
			$m = $this->pad(intval($month),2);
			$this->_start = new DateTime("{$year}-{$m}-01 00:00:00");
		}
		else
		{
			$this->_start = new DateTime((new DateTime())->format("Y-m-01 00:00:00"));
		}
		$this->_end = new DateTime($this->_start->format("Y-m-d H:i:s"));
		$this->_end->add(new DateInterval("P1M"));
		$this->_end->sub(new DateInterval("P1D"));
		$this->_end = new DateTime($this->_end->format("Y-m-d 23:59:59"));

	}

	public function createLastMonth()
	{
		$dt = new DateTime();
		$dt->sub(new DateInterval("P1M"));
		$this->create($dt->format("Y"),$dt->format("m"));
	}

	public function Start()
	{
		return $this->_start->format("Y-m-d H:i:s");
	}

	public function End()
	{
		return $this->_end->format("Y-m-d H:i:s");
	}
}


?>