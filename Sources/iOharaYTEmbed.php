<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.1
 * @author Jessica Gonz�lez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2016 Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access...');

interface iOharaYTEmbed
{
	public function content($data);

	public function auto(&$message);

	public function create($params);

	public function invalid();
}