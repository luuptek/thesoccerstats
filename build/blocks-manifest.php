<?php
// This file is generated. Do not modify it manually.
return array(
	'match-summary' => array(
		'apiVersion' => 3,
		'name' => 'tss/match-summary',
		'title' => 'Match Summary',
		'category' => 'widgets',
		'icon' => 'chart-bar',
		'description' => 'Render a match summary card anywhere.',
		'textdomain' => 'tss',
		'attributes' => array(
			'useCurrent' => array(
				'type' => 'boolean',
				'default' => true
			),
			'matchId' => array(
				'type' => 'number',
				'default' => 0
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'supports' => array(
			'html' => false,
			'reusable' => true,
			'align' => array(
				'full',
				'wide'
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'blockGap' => false
			)
		)
	),
	'player-profile' => array(
		'apiVersion' => 3,
		'name' => 'tss/player-profile',
		'title' => 'Player Profile',
		'category' => 'widgets',
		'icon' => 'id',
		'description' => 'Render a player profile anywhere.',
		'textdomain' => 'tss',
		'attributes' => array(
			'playerId' => array(
				'type' => 'number',
				'default' => 0
			)
		),
		'editorScript' => 'tss-editor',
		'render' => 'file:./render.php',
		'supports' => array(
			'html' => false
		)
	),
	'player-stats-table' => array(
		'apiVersion' => 3,
		'name' => 'tss/player-stats-table',
		'title' => 'Player Stats Table',
		'category' => 'widgets',
		'icon' => 'table-col-after',
		'description' => 'Render player statistics for a season.',
		'textdomain' => 'tss',
		'attributes' => array(
			'seasonId' => array(
				'type' => 'number',
				'default' => 0
			),
			'matchTypeId' => array(
				'type' => 'number',
				'default' => 0
			)
		),
		'editorScript' => 'tss-editor',
		'render' => 'file:./render.php',
		'supports' => array(
			'html' => false
		)
	),
	'season-match-list' => array(
		'apiVersion' => 3,
		'name' => 'tss/season-match-list',
		'title' => 'Season Match List',
		'category' => 'widgets',
		'icon' => 'list-view',
		'description' => 'Render a season match list anywhere.',
		'textdomain' => 'tss',
		'attributes' => array(
			'seasonId' => array(
				'type' => 'number',
				'default' => 0
			),
			'matchTypeId' => array(
				'type' => 'number',
				'default' => 0
			)
		),
		'editorScript' => 'tss-editor',
		'render' => 'file:./render.php',
		'supports' => array(
			'html' => false
		)
	)
);
