<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 1.2.3
 * @author Jessica Gonz�lez <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2015 Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function OYTE_bbc_add_code(&$codes)
{
	global $modSettings, $txt;

	if (empty($modSettings['OYTE_master']))
		return;

	loadLanguage('OharaYTEmbed');

	array_push($codes,
		array(
			'tag' => 'youtube',
			'type' => 'unparsed_content',
			'content' => '$1',
			'validate' => function (&$tag, &$data, $disabled) use ($txt)
			{
				// This tag was disabled.
				if (!empty($disabled['youtube']))
					return;

				if (empty($data))
					$data = $txt['OYTE_unvalid_link'];

				else
					$data = OYTE_Main(trim(strtr($data, array('<br />' => ''))));
			},
			'disabled_content' => '$1',
			'block_level' => true,
		),
		array(
			'tag' => 'yt',
			'type' => 'unparsed_content',
			'content' => '$1',
			'validate' => function (&$tag, &$data, $disabled) use ($txt)
			{
				// This tag was disabled.
				if (!empty($disabled['yt']))
					return;

				if (empty($data))
					$data = $txt['OYTE_unvalid_link'];

				else
					$data = OYTE_Main(trim(strtr($data, array('<br />' => ''))));
			},
			'disabled_content' => '$1',
			'block_level' => true,
		),
		array(
			'tag' => 'vimeo',
			'type' => 'unparsed_content',
			'content' => '$1',
			'validate' => function (&$tag, &$data, $disabled) use ($txt)
			{
				// This tag was disabled.
				if (!empty($disabled['vimeo']))
					return;

				if (empty($data))
					$data = $txt['OYTE_unvalid_link'];

				else
					$data = OYTE_Vimeo(trim(strtr($data, array('<br />' => ''))));
			},
			'disabled_content' => '$1',
			'block_level' => true,
		)
	);
}

 /* The bbc button */
function OYTE_bbc_add_button(&$buttons)
{
	global $txt, $modSettings;

	loadLanguage('OharaYTEmbed');

	if (empty($modSettings['OYTE_master']))
		return;

	$buttons[count($buttons) - 1][] = array(
		'image' => 'youtube',
		'code' => 'youtube',
		'before' => '[youtube]',
		'after' => '[/youtube]',
		'description' => $txt['OYTE_desc'],
	);

	$buttons[count($buttons) - 1][] =array(
		'image' => 'vimeo',
		'code' => 'vimeo',
		'before' => '[vimeo]',
		'after' => '[/vimeo]',
		'description' => $txt['OYTE_vimeo_desc'],
	);

}

/* Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^ */
function OYTE_settings(&$config_vars)
{
	global $txt;

	loadLanguage('OharaYTEmbed');

	$config_vars[] = $txt['OYTE_title'];
	$config_vars[] = array('check', 'OYTE_master', 'subtext' => $txt['OYTE_master_sub']);
	$config_vars[] = array('check', 'OYTE_autoEmbed', 'subtext' => $txt['OYTE_autoEmbed_sub']);
	$config_vars[] = array('int', 'OYTE_video_width', 'subtext' => $txt['OYTE_video_width_sub'], 'size' => 3);
	$config_vars[] = array('int', 'OYTE_video_height', 'subtext' => $txt['OYTE_video_height_sub'], 'size' => 3);
	$config_vars[] = '';
}

/* Take the url, take the video ID and return the embed code */
function OYTE_Main($data)
{
	global $modSettings, $txt;

	loadLanguage('OharaYTEmbed');

	// Gotta respect the master setting...
	if (empty($data) || empty($modSettings['OYTE_master']))
		return sprintf($txt['OYTE_unvalid_link'], 'youtube');

	/* Set a local var for laziness */
	$videoID = '';
	$result = '';

	// Check if the user provided the youtube ID
	if (preg_match('/^[a-zA-z0-9_-]{11}$/', $data) > 0)
		$videoID = $data;

	 /* We all love Regex */
	$pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';

	/* First attempt, pure regex */
	if (empty($videoID) && preg_match($pattern, $data, $matches))
		$videoID = isset($matches[1]) ? $matches[1] : false;

	/* Give another regex a chance */
	elseif (empty($videoID) && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $data, $match))
		$videoID = isset($match[1]) ? $match[1] : false;

	/* No?, then one last chance, let PHPs native parse_url() function do the dirty work */
	elseif (empty($videoID))
	{
		/* This relies on the url having ? and =, this is only an emergency check */
		parse_str(parse_url($data, PHP_URL_QUERY), $videoID);
		$videoID = isset($videoID['v']) ? $videoID['v'] : false;
	}

	/* At this point, all tests had miserably failed */
	if (empty($videoID))
		return sprintf($txt['OYTE_unvalid_link'], 'youtube');

	// Got something!
	else
		$result = '<div class="youtube" id="oh_'. $videoID .'" style="width: '. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'px; height: '. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']) .'px;"></div>';

	return $result;
}

function OYTE_Vimeo($data)
{
	global $modSettings, $txt, $sourcedir;

	if (empty($data) || empty($modSettings['OYTE_master']))
		return sprintf($txt['OYTE_unvalid_link'], 'vimeo');

	loadLanguage('OharaYTEmbed');

	// Need a function in a far far away file...
	require_once($sourcedir .'/Subs-Package.php');

	// Construct the URL
	$oembed = 'https://vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'&height='. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']);

	//Attempts to fetch data from a URL, regardless of PHP's allow_url_fopen setting
	$jsonArray = json_decode(fetch_web_data($oembed), true);

	if (!empty($jsonArray) && is_array($jsonArray) && !empty($jsonArray['html']))
		return $jsonArray['html'];

	else
		return sprintf($txt['OYTE_unvalid_link'], 'vimeo');
}

function OYTE_Preparse($message)
{
	global $context, $modSettings;

	// Gotta respect the master and the autoembed setting.
	if (empty($modSettings['OYTE_master']) || empty($modSettings['OYTE_autoEmbed']))
		return $message;

	// Someone else might not like this!
	if (empty($message) || !empty($context['ohara_disable']))
		return $message;

	// The extremely long regex...
	$vimeo = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)?(?:www\.)?vimeo.com\/(?:album\/|groups\/(.*?)\/|channels\/(.*?)\/)?[0-9]+\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$youtube = '~(?<=[\s>\.(;\'"]|^)(?:http|https):\/\/[\w\-_%@:|]?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | </a>  ))[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix';

	// Is this a YouTube video url?
	$message = preg_replace_callback(
		$youtube,
		function ($matches) {
			return '[youtube]'. $matches[0] .'[/youtube]';
		},
		$message
	);

	// A Vimeo url perhaps?
	$message = preg_replace_callback(
		$vimeo,
		function ($matches) {
			return '[vimeo]'. $matches[0] .'[/vimeo]';
		},
		$message
	);

	return $message;
}

/* DUH! WINNING! */
function OYTE_care(&$dummy)
{
	global $context, $settings;

	if (!empty($context['current_action']) && $context['current_action'] == 'credits')
		$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" target="_blank" title="Free SMF mods">Ohara YouTube Embed mod &copy Suki</a>';

	// Add our css and js files. Dear and lovely mod authors, if you're going to use $context['html_headers'] MAKE SURE you append your data .= instead of re-declaring the var! and don't forget to add a new line and proper indentation too!
	$context['html_headers'] .= '
	<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="//code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/ohyoutube.min.js"></script>
	<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/oharaEmbed.css" />';
}

	/* Slowly repeating
	...Sunday morning */
