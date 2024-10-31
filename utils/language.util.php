<?php
function phantomwriter_client_alphabetical_sort($a, $b)
{
	return strcasecmp($a['nativeName'], $b['nativeName']);
}

function phantomwriter_client_get_languages_path()
{
	return PHANTOMWRITER_CLIENT_PLUGIN_DIR . 'assets/languages.json';
}

function phantomwriter_client_get_default_language() {
	$wp_language = get_locale();
	$languages   = phantomwriter_client_get_languages();

	$formatted_language = substr($wp_language, 0, 2);

	if (array_key_exists($formatted_language, $languages)) {
			return $formatted_language;
	}

	return 'en';
}

function phantomwriter_client_get_languages()
{
	$languages_path = phantomwriter_client_get_languages_path();

	if (!file_exists($languages_path)) return array();

	$languages_json = file_get_contents($languages_path);
	$languages_json = json_decode($languages_json, true);

	uasort($languages_json, 'phantomwriter_client_alphabetical_sort');

	$languages = array();
	foreach ($languages_json as $lang) {
		$key = $lang['code'];
		$native_name = $lang['nativeName'];
		$name = $lang['name'];

		if ($key === 'en') {
			$languages[$key] = "$native_name";
		} else {
			$languages[$key] = "$native_name ($name)";
		}
	}

	return $languages;
}

function phantomwriter_client_get_language_by_code($code)
{
	$languages_path = phantomwriter_client_get_languages_path();
	if (!file_exists($languages_path)) return 'English';

	$languages_json = file_get_contents($languages_path);
	$languages_json = json_decode($languages_json, true);

	$language = 'English';
	foreach ($languages_json as $lang) {
		$key         = $lang['code'];
		$native_name = $lang['nativeName'];
		$name        = $lang['name'];

		if ($key === $code) $language = "$native_name ($name)";
	}

	return $language;
}
