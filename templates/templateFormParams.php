<?php
$formparams = [
    "global" => [
        "table" => "<edit db table name>",
        "primary_key" => "<edit db table primary key>"
    ],
    "form" => [
         "heading" => "<heading text>",
         "introduction" => "",
         "classes" => [
            "div" => [
                "inputtext" => "d_inputtext",
                "textarea" => "d_textarea",
                "booleancheckbox" => "d_checkbox",
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
    ],
    "fields" => [
        "<field name>" => [
            "type" => "text | boolean | button | choice",
            "tag" => "input | textarea | checkbox",
            "sub-tag" => "text",
            "size" => "20",
            "maxlength" => "20",
            "errname" => "Prefix",
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
            "secuity_view" => integer,
            "security_eidt" => integer
        ]
    ]
];

$FL = new FormList($formparams);

?>