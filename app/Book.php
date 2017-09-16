<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model {
	public function getRouteKeyName() {
		return 'url_slug';
	}

	public function chapters() {
		return $this->hasMany('App\Chapter');
	}

	public function firstChapter() {
		return $this->chapters()->orderBy('order', 'asc')->first()->order;
	}

	public function allChapters() {
		return $this->chapters()->orderby('order', 'asc')->pluck('order');
	}

	public function firstParagraph() {
		try {
			$content = $this->chapters()->orderBy('order', 'asc')->first()->content;
			$dom = new \DOMDocument();
			$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
			$xp = new \DOMXPath($dom);
			$res = $xp->query('//p');
			return $res[0]->nodeValue;
		} catch (\ErrorException $e) {
			return '';
		}
	}

	public function affiliates() {
		return $this->hasMany('App\Affiliate');
	}

	public function countryCode() {
		switch($this->country) {
			case "United Kingdom":
				return "gb";
				break;
			case "United States":
				return "us";
				break;
			default:
				return "";
				break;
		}
	}

	public function getGoodreadsAvgRating($value) {
		return round($value, 2);
	}

	use \web_helpers;

	public function web_page_title($site_name = FALSE) {
		return $this->page_title($site_name, [$this->title . ' by ' . $this->author]);
	}

	public function web_url() {
		return route('book', [$this->getRouteKey()]);
	}

	public function web_image() {
		return $this->web_background_image();
	}

	public function web_description() {
		return strip_tags($this->description);
	}

	public function web_star_rating() {
		$fraction = $this->goodreads_avg_rating - floor($this->goodreads_avg_rating);

		$full_stars = array_fill(0, floor($this->goodreads_avg_rating), 'gfc-p10');
		if ($fraction > 0 && $fraction < 0.5) {
			$half_stars = ['gfc-p3'];
		} elseif ($fraction >= 0.5) {
			$half_stars = ['gfc-p6'];
		} else {
			$half_stars = ['gfc-p0'];
		}
		$empty_stars = array_fill(0, 4 - floor($this->goodreads_avg_rating), 'gfc-p0');

		return array_merge($full_stars, $half_stars, $empty_stars);
	}

	public function web_cover_image() {
		return 'images/' . $this->url_slug . '/cover.jpg';
	}

	public function web_background_image() {
		$jpg_path = 'images/' . $this->url_slug . '/background.jpg';
		$png_path = 'images/' . $this->url_slug . '/background.png';
		if (file_exists(public_path($jpg_path))) {
			return $jpg_path;
		} elseif (file_exists(public_path($png_path))) {
			return $png_path;
		} else {
			throw new \ErrorException('Background image not found for book ' . $this->url_slug);
		}
	}

	public function web_portrait_image() {
		return 'images/' . $this->url_slug . '/portrait.jpg';
	}

	public function web_signature_image() {
		return 'images/' . $this->url_slug . '/signature.png';
	}
}
