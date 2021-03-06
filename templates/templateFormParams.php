<?php
$formparams = [
    "global" => [
        "table" => "<edit db table name>",
        "primary_key" => "<edit db table primary key>",
        "single_record" => true | false, //Set if there is no primary key and only a single record exists in this table.
        "selector_text" => "GLOBAL",
    ],
    "form" => [
         "heading" => "<heading text>",
         "introduction" => "",
         "classes" => [
            "div" => [
                "inputtext" => "d_inputtext",
                "emailtext" => "d_inputtext",
                "passwordtext" => "d_inputtext",
                "textarea" => "d_textarea",
                "checkbox" => "d_checkbox",
                "choice" => "d_choice",
                "dropdown" => "d_dropdown",
                "fk" => "d_dropdown",
            ]
        ],
        "groups" => [
            "details1" => [
                "heading" => "YOUR DETAILS",
                "introduction1" => "Introduction paragarph",
                "introduction2" => "",
                "introduction3" => "",
            ]
        ]
    ],
    "list" => [
        "type" => "plain | checkbox",
        "record_selector" => "true | false",
        "heading" => "<heading text>",
        "introduction" => "",
        "default_order" => "",
        "default_where" => "",
        "actions" => [
            "disable" => ["display" => "DISABLE", "action" => "disableSubscription"],
    ],
    "fields" => [
        "<field name>" => [
            "type" => "text | boolean | integer | decimal | currency | percent | date | datetime | button | choice | dropdown | fk | hidden",
            "fk_table" => "",
            "fk_index" => "",
            "fk_display" => "",
            "fk_where" => "",
            "fk_order" => "",
            "tag" => "input | textarea ",
            "sub-tag" => "text | checkbox ",
            "dbfield" => true | false, //Assumed to be true of not specified
            "size" => "20",
            "maxlength" => "20",
            "cols" => "50",  //The number of cols in a text area
            "rows" => "4",  //The number of rows in a text area
            "errname" => "Prefix",
            "decimalplaces" => 2,
            "currency_symbol" => "$",
            "readonly => true",
            "dropdownvalues" => [
                ["text" => "INDEFINITE", "value" => 0],
                ["text" => "1 MONTH", "value" => 1],
                ["text" => "2 MONTHS", "value" => 2, "default" => true],
                ["text" => "3 MONTHS", "value" => 3],
                ["text" => "6 MONTHS", "value" => 6],
                ["text" => "1 YEAR", "value" => 12],
                ["text" => "2 YEARS", "value" => 24],
            ],
            "form" => [
                "display" => true | false,
                "formlabel" => "<label name>",
                "title" => "",
                "required" => true | false,
                "default" => "default form field text",
                "errtext" => "",
                "posttext" => "",
                "trim" =>  true | false,
                "group" => "details1",
                "choice" => [
                    ["text" => "Pay by credit card", "value" => 1,"selected" => "javascript()"],
                    ["text" => "Pay on account", "value" => 2, "selected" => "javascript()"],
                ]
            ],
            "list" => [
                "display" => true | false,
                "heading" => "<table heading name>",
                "anchor" => true,  // This is the field that has a link to click of edit
                "displayoption" => "tick | ",
                "translation" => "none | upper | lower | firstupper"
                ],
            ],
            "secruity_view" => integer,
            "security_edit" => integer
        ]
    ]
];

$FL = new FormList($formparams);

?>