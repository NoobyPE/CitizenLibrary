<?php

namespace nooby\CitizenLibrary\attributes;

use pocketmine\entity\Skin;

final class CitizenSkin
{

	public static function fromDefaultGeometry(string $skinPath): Skin
	{
		$img = @imagecreatefrompng($skinPath);
		$skin_bytes = "";
		for ($y = 0; $y < imagesy($img); $y++) {
			for ($x = 0; $x < imagesx($img); $x++) {
				$colorant = @imagecolorat($img, $x, $y);
				$a = ((~($colorant >> 24)) << 1) & 0xff;
				$r = ($colorant >> 16) & 0xff;
				$g = ($colorant >> 8) & 0xff;
				$b = $colorant & 0xff;
				$skin_bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		@imagedestroy($img);
		return new Skin("Standard_CustomSlim", $skin_bytes, "", "geometry.humanoid.custom", "");
	}

	public static function fromCustomGeometry(string $skinPath, string $skinGeometryPath): Skin
	{
		$img = imagecreatefrompng($skinPath);
		$skin_bytes = "";
		for ($y = 0; $y < imagesy($img); $y++) {
			for ($x = 0; $x < imagesx($img); $x++) {
				$colorant = @imagecolorat($img, $x, $y);
				$a = ((~($colorant >> 24)) << 1) & 0xff;
				$r = ($colorant >> 16) & 0xff;
				$g = ($colorant >> 8) & 0xff;
				$b = $colorant & 0xff;
				$skin_bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		imagedestroy($img);
		$c = json_decode(file_get_contents($skinGeometryPath), true);
		return new Skin($c['skinId'], $skin_bytes, $c['capeData'], $c['geometryName'], $c['geometryName']);
	}

	public static function getSkinDataFromPNG(string $path): string
	{
		$bytes = "";
		if (!file_exists($path)) {
			return $bytes;
		}
		$img = imagecreatefrompng($path);
		[$width, $height] = getimagesize($path);
		for ($y = 0; $y < $height; ++$y) {
			for ($x = 0; $x < $width; ++$x) {
				$argb = imagecolorat($img, $x, $y);
				$bytes .= chr(($argb >> 16) & 0xff) . chr(($argb >> 8) & 0xff) . chr($argb & 0xff) . chr((~($argb >> 24) << 1) & 0xff);
			}
		}
		imagedestroy($img);
		return $bytes;
	}

}