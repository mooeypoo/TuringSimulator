<?php print header('Content-type: application/json'); ?>
{
	"info": {
		"name": "Sample Turing Machine (#1)",
		"author": "Moriel Schottlender",
		"description": "Sample Turing Machine that transposes a's from the beginning of a string to the end.",
		"language": "a,b",
		"rule": "a<sup>j</sup>b<sup>i</sup> ==> b<sup>i</sup>a<sup>j</sup>"
	},
    "states": [
        {
            "name": 0,
            "role": ">",
			"is_start": 1,
			"is_end": 0,
            "transitions": [
                {
                    "to_state": 0,
					"read": "#",
                    "replace": "#",
                    "direction": "R"
                },
                {
                    "to_state": 0,
					"read": "a",
                    "replace": "a",
                    "direction": "R"
                },
                {
                    "to_state": 0,
					"read": "$",
                    "replace": "$",
                    "direction": "R"
                },
                {
                    "to_state": 1,
					"read": "b",
                    "replace": "#",
                    "direction": "L"
                },
                {
                    "to_state": 2,
					"read": "B",
                    "replace": "B",
                    "direction": "L"
                }
            ]
        },
        {
            "name": 1,
            "role": " ",
			"is_start": 0,
			"is_end": 0,
            "transitions": [
                {
                    "to_state": 1,
					"read": "$",
                    "replace": "$",
                    "direction": "L"
                },
                {
                    "to_state": 1,
					"read": "#",
                    "replace": "#",
                    "direction": "L"
                },

                {
                    "to_state": 1,
					"read": "a",
                    "replace": "a",
                    "direction": "L"
                },
                {
                    "to_state": 0,
					"read": "B",
                    "replace": "$",
                    "direction": "R"
                }
            ]
        },
        {
            "name": 2,
            "role": " ",
			"is_start": 0,
			"is_end": 0,
            "transitions": [
                {
                    "to_state": 2,
					"read": "$",
                    "replace": "$",
                    "direction": "L"
                },
                {
                    "to_state": 2,
					"read": "#",
                    "replace": "#",
                    "direction": "L"
                },
                {
                    "to_state": 2,
					"read": "a",
                    "replace": "a",
                    "direction": "L"
                },
                {
                    "to_state": 3,
					"read": "B",
                    "replace": "B",
                    "direction": "R"
                }
            ]
        },
        {
            "name": 3,
            "role": " ",
			"is_start": 0,
			"is_end": 0,
            "transitions": [
                {
                    "to_state": 3,
					"read": "$",
                    "replace": "b",
                    "direction": "R"
                },
                {
                    "to_state": 3,
					"read": "#",
                    "replace": "B",
                    "direction": "R"
                },
                {
                    "to_state": 3,
					"read": "a",
                    "replace": "a",
                    "direction": "R"
                },
                {
                    "to_state": 4,
					"read": "B",
                    "replace": "B",
                    "direction": "R"
                }
            ]
        },
        {
            "name": 4,
            "role": "=",
			"is_start": 0,
			"is_end": 1,
            "transitions": []
        }
    ]
}