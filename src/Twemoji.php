<?php

namespace ADT\Twemoji;

use Exception;

class Twemoji
{
	/**
	 * Base for twemoji url.
	 *
	 * @var string
	 */
	const TWEMOJI_URL = '//twemoji.maxcdn.com/%1$sx%1$s/%2$s.png';

	/**
	 * Regular expression for finding twemoji names (surrounded by double colon)
	 */
	const TWEMOJI_REGEX = '/(:[a-zA-Z0-9_]*:)/';

	/**
	 * Skeleton for image html tag
	 *
	 * @var string
	 */
	const IMAGE_TAG = '<img src="%s" alt="%s" class="%s">';

	/**
	 * Icon size of twemoji image.
	 *
	 * @var int
	 */
	protected $iconSize;

	/**
	 * Supported icon sizes for twemoji.
	 *
	 * @var array
	 */
	protected $supportedIconSizes = [16, 36, 72];

	/**
	 * Array of mappings twemoji name to unicode representation and description of twemoji
	 *
	 * @var
	 */
	protected $twemojiIndex;

	protected $unicodeIndex;

	/**
	 * @param int $iconSize
	 */
	public function __construct($iconSize = 16)
	{
		$this->iconSize = $iconSize;

		$this->validateIconSize();

		$this->obtainTwemojiIndex();
	}

	/**
	 * Returns generated url of given twemoji name (surrounded by double colon).
	 *
	 * @param $emoji
	 * @param bool $useTweEmoji
	 *
	 * @return string
	 */
	public function getUrl($emoji, $useTweEmoji = true)
	{
		return sprintf(
			self::TWEMOJI_URL,
			$this->iconSize,
			$useTweEmoji ? $this->getUnicode($emoji) : $this->getUnicodeFromUtf8($emoji)
		);
	}

	/**
	 * Returns unicode representation of twemoji name (surrounded by double colon).
	 *
	 * @param $twemojiName
	 * @return string
	 */
	public function getUnicode($twemojiName)
	{
		return $this->twemojiIndex[$twemojiName]['unicode'];
	}

	/**
	 * Returns unicode representation of utf-8.
	 *
	 * @param $emoji
	 * @return string
	 */
	private function getUnicodeFromUtf8($emoji)
	{
		return preg_replace("/^[0]+/","", bin2hex(mb_convert_encoding($emoji, 'UTF-32', 'UTF-8')));
	}

	/**
	 * Returns description of given twemoji name (surrounded by double colon).
	 *
	 * @param $emoji
	 * @param bool $useTweEmoji
	 * @return string
	 */
	public function getDescription($emoji, $useTweEmoji = true)
	{

		return $useTweEmoji ? $this->twemojiIndex[$emoji]['description'] : $this->unicodeIndex[$this->getUnicodeFromUtf8($emoji)]['description'];
	}

	/**
	 * Returns image of twemoji name (surrounded by double colon).
	 *
	 * @param string $emoji
	 * @param string|array $classNames
	 * @param bool $useTweEmoji
	 *
	 * @return string
	 */
	public function getImage($emoji, $useTweEmoji = true, $classNames = '')
	{
		return $this->makeImage($emoji, $useTweEmoji, $classNames);
	}

	/**
	 * Prints image of twemoji name (surrounded by double colon).
	 *
	 * @param string $emoji
	 * @param string|array $classNames
	 * @param bool $useTweEmoji
	 */
	public function image($emoji, $classNames = '', $useTweEmoji = true)
	{
		echo $this->makeImage($emoji, $useTweEmoji, $classNames);
	}

	/**
	 * Replaces twemoji names (surrounded by double colon) in text with corresponding images.
	 *
	 * @param string $text
	 * @param bool $useTweEmoji
	 * @param string $classNames
	 * @return string
	 */
	public function parseText($text, $useTweEmoji = true, $classNames = '')
	{
		return preg_replace_callback(self::TWEMOJI_REGEX, function($matches) use ($classNames, $useTweEmoji) {
			return $this->getImage($matches[1], $useTweEmoji, $classNames);
		}, $text);
	}

	/**
	 * Loads twemoji index json file into array.
	 *
	 * @see https://github.com/heyupdate/Emoji/tree/master/config
	 */
	private function obtainTwemojiIndex()
	{
		$twemojiIndex = file_get_contents(__DIR__ . '/twemoji-index.json');
		$twemojiIndex = json_decode($twemojiIndex, true);

		$this->twemojiIndex = [];

		foreach ($twemojiIndex as $twemoji) {
			$this->twemojiIndex[':' . $twemoji['name'] . ':'] = [
				'unicode' => $twemoji['unicode'],
				'description' => $twemoji['description'],
			];

			$this->unicodeIndex[$twemoji['unicode']] = [
				'twemoji' => ':' . $twemoji['name'] . ':',
				'description' => $twemoji['description'],
			];
		}
	}

	/**
	 * Returns formatted text of image html tag for given twemoji name (surrounded by double colon)
	 * with optional classes applied to it.
	 *
	 * @param string $emoji
	 * @param string|array $classNames
	 * @param bool $useTweEmoji
	 * @return string
	 */
	private function makeImage($emoji, $useTweEmoji, $classNames = '')
	{
		return sprintf(
			self::IMAGE_TAG,
			$this->getUrl($emoji, $useTweEmoji),
			$this->getDescription($emoji, $useTweEmoji),
			is_array($classNames) ? implode(' ', $classNames) : $classNames
		);
	}

	/**
	 * Throws an exception if icon size is not valid/supported.
	 *
	 * @throws Exception
	 */
	private function validateIconSize()
	{
		if (! in_array($this->iconSize, $this->supportedIconSizes)) {
			throw new Exception('Icon must be of size 16, 36 or 72');
		}
	}

}
