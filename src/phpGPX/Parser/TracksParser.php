<?php
/**
 * Created            30/08/16 13:31
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parser;


use phpGPX\Model\Extension;
use phpGPX\Model\Segment;
use phpGPX\Model\Collection;
use phpGPX\Model\Point;

abstract class TracksParser
{

	/**
	 * @param \SimpleXMLElement $nodes
	 * @return \phpGPX\Model\Collection[]
	 */
	public static function parse(\SimpleXMLElement $nodes)
	{
		$tracks = [];

		foreach ($nodes as $trk)
		{
			$tracks[] = self::parseNode($trk);
		}

		return $tracks;
	}

	/**
	 * @param \SimpleXMLElement $node
	 * @return Collection
	 */
	private static function parseNode(\SimpleXMLElement $node)
	{
		$track = new Collection();

		if (isset($node->src))
		{
			$track->source = (string) $node->src;
		}

		if (isset($node->link))
		{
			$track->url = (string) $node->link['href'];
		}

		if (isset($node->type))
		{
			$track->type = (string) $node->type;
		}

		if (isset($node->trkseg))
		{
			foreach ($node->trkseg as $seg)
			{
				$track->segments[] = self::parseSegment($seg);
			}
		}

		return $track;
	}

	private static function parseSegment(\SimpleXMLElement $seg)
	{
		$segment = new Segment();

		foreach ($seg as $pt)
		{
			$point = new Point();

			$point->latitude = isset($pt['lat']) ? ((double) $pt['lat']) : null;
			$point->longitude = isset($pt['lon']) ? ((double) $pt['lon']) : null;
			$point->altitude = isset($pt->ele) ? ((double) $pt->ele) : null;

			if (isset($pt->time))
			{
				$utc = new \DateTimeZone('UTC');
				$point->timestamp = new \DateTime($pt->time, $utc);
			}

			if (isset($pt->extensions))
			{
				$point->extension = self::parseExtensions($pt->extensions);
			}

			$segment->points[] = $point;
		}

		return $segment;
	}

	/**
	 * @param \SimpleXMLElement $ext
	 * @return Extension
	 */
	private static function parseExtensions(\SimpleXMLElement $ext)
	{
		$extension = new Extension();
		$ns = $ext->getNamespaces(true);

		$trackPointExtension = $ext->children($ns['gpxtpx'])->TrackPointExtension;

		if (!empty($trackPointExtension))
		{
			$extension->heartRate = isset($trackPointExtension->hr) ? ((double) $trackPointExtension->hr) : null; //check
			$extension->avgTemperature = isset($trackPointExtension->atemp) ? ((double) $trackPointExtension->atemp) : null; //check
			$extension->cadence = isset($trackPointExtension->cad) ? ((double) $trackPointExtension->cad) : null; //check
//			$extension->course = isset($trackPointExtension->hr) ? ((double) $trackPointExtension->hr) : null;
//			$extension->distance = isset($trackPointExtension->hr) ? ((double) $trackPointExtension->hr) : null;
//			$extension->speed = isset($trackPointExtension->hr) ? ((double) $trackPointExtension->hr) : null;
		}

		return $extension;
	}

}