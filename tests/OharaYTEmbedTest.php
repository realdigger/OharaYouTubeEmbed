<?php

use Suki\Ohara;

// Extend the main class to avoid calling SMF's functions.
class OharaYTEmbedMock extends OharaYTEmbed
{
	public function __construct()
	{
		// Get yourself noted.
		$this->setRegistry();

		// Get the default settings.
		$this->defaultSettings();
	}
}

class OharaYTEmbedTest extends \PHPUnit_Framework_TestCase
{
	public function testGetSites()
	{
		$o = new OharaYTEmbedMock;

		// Get all available sites.
		$sites = $o->getSites();

		$this->assertInternalType('array', $sites);

		// $sites must only contain objects.
		$this->assertContainsOnly('object', $sites);
	}

	public function testSiteSettingsProperty()
	{
		$o = new OharaYTEmbedMock;

		// Get all available sites.
		$sites = $o->getSites();

		// Every site class needs to have a siteSettings property.
		foreach ($sites as $site)
			$this->assertObjectHasAttribute('siteSettings', $site);
	}

	public function testSiteTestsProperty()
	{
		$o = new OharaYTEmbedMock;

		// Get all available sites.
		$sites = $o->getSites();

		// Every site class needs to have a siteSettings property.
		foreach ($sites as $site)
			$this->assertObjectHasAttribute('siteTests', $site);
	}

	public function TestOharaYTEmbed()
	{
		$o = new OharaYTEmbedMock;

		// Get all available sites.
		$sites = $o->getSites();

		foreach ($sites as $site)
		{
			foreach ($site->siteTests['original'] as $originalString)
			{
				$result = $site->content($originalString);

				$this->assertEquals($site->siteTests['expected'], $result);
			}
		}
	}
}
