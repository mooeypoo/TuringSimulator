<?php print header('Content-type: application/json'); ?>
{
	"info": {
		"name": "Sample Turing Machine (#2)",
		"author": "Moriel Schottlender",
		"description": "Sample Turing Machine that accepts a string with format a*c*b* and outputs the same number of a's as b's.",
		"language": "a,b,c",
		"rule": "a<sup>j</sup>c<sup>j</sup>b<sup>k</sup> ==> a<sup>k</sup>c<sup>j</sup>b<sup>k</sup>"
	},
    "states": [
        {
            "name": 0,
            "role": ">",
            "transitions": [
                {
                    "to_state": 0,
					"read": "#",
                    "replace": "#",
                    "direction": "R"
                },
                {
                    "to_state": 0,
					"read": "$",
                    "replace": "$",
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
					"read": "c",
                    "replace": "c",
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
                    "to_state": 1,
					"read": "c",
                    "replace": "c",
                    "direction": "L"
                },
                {
                    "to_state": 0,
					"read": "B",
                    "replace": "#",
                    "direction": "R"
                }
            ]
        },
        {
            "name": 2,
            "role": " ",
            "transitions": [
                {
                    "to_state": 2,
					"read": "$",
                    "replace": "a",
                    "direction": "L"
                },
                {
                    "to_state": 2,
					"read": "#",
                    "replace": "b",
                    "direction": "L"
                },
                {
                    "to_state": 2,
					"read": "c",
                    "replace": "c",
                    "direction": "L"
                },
                {
                    "to_state": 3,
					"read": "B",
                    "replace": "B",
                    "direction": "L"
                }
            ]
        },
        {
            "name": 3,
            "role": "=",
            "transitions": []
        }
    ]
}