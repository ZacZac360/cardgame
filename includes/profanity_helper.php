<?php

function profanity_word_list(): array {
  return [
    'putangina',
    'puta',
    'gago',
    'bobo',
    'ulol',
    'tanga',
    'inutil',
    'tarantado',
    'inamo',
    'kingina',
    'piste',
    'yawa',
    'pukinangina',
    'puke',
    'tite',
    'obob',
    'ngongo',
    'fucking',
    'fuck',
    'shit',
    'bitch',
    'asshole',
    'bastard',
    'cunt',
    'whore',
    'slut',
    'motherfucker',
    'nigger',
    'nigga',
  ];
}

/**
 * Normalize text so common bypasses are easier to catch.
 * Example:
 *   f.u.c.k   -> fuck
 *   sh1t      -> shit
 *   p u t a   -> puta
 */
function profanity_normalize(string $text): string {
  $text = mb_strtolower($text, 'UTF-8');

  $map = [
    '0' => 'o',
    '1' => 'i',
    '3' => 'e',
    '4' => 'a',
    '5' => 's',
    '7' => 't',
    '@' => 'a',
    '$' => 's',
    '!' => 'i',
  ];

  $text = strtr($text, $map);

  // remove separators/spaces users use to dodge filters
  $text = preg_replace('/[\s\-_\.]+/u', '', $text) ?? $text;

  // strip everything except letters/numbers after mapping
  $text = preg_replace('/[^a-z0-9]+/u', '', $text) ?? $text;

  return $text;
}

function profanity_matches(string $text): array {
  $hits = [];

  if (trim($text) === '') {
    return $hits;
  }

  $normalized = profanity_normalize($text);

  foreach (profanity_word_list() as $bad) {
    $badNorm = profanity_normalize($bad);

    if ($badNorm !== '' && str_contains($normalized, $badNorm)) {
      $hits[] = $bad;
    }
  }

  return array_values(array_unique($hits));
}

function contains_profanity(string $text): bool {
  return !empty(profanity_matches($text));
}

/**
 * Returns null when valid, or an error string when invalid.
 */
function validate_clean_text(string $fieldLabel, string $value, int $maxLen): ?string {
  $value = trim($value);

  if (mb_strlen($value, 'UTF-8') > $maxLen) {
    return "{$fieldLabel} is too long.";
  }

  if (contains_profanity($value)) {
    return "{$fieldLabel} contains blocked language.";
  }

  return null;
}

function censor_profanity(string $text, string $mask = '*'): string {
  $badWords = profanity_word_list();

  foreach ($badWords as $bad) {
    $pattern = '/' . preg_quote($bad, '/') . '/i';

    $text = preg_replace_callback($pattern, function ($m) use ($mask) {
      return str_repeat($mask, strlen($m[0]));
    }, $text);
  }

  return $text;
}

/**
 * Funny / game-style censor, Town of Salem-ish.
 * Replaces profanity with goofy safer words instead of ****
 */
function tos_censor(string $text): string {
  $map = [
    // Tagalog
    'putangina'    => 'susmaryosep',
    'pukinangina'  => 'susmaryosep',
    'kingina'      => 'hay naku',
    'puta'         => 'bruha',
    'gago'         => 'siraulo',
    'bobo'         => 'unggoy',
    'obob'         => 'unggoy',
    'ulol'         => 'lokoloko',
    'tanga'        => 'hunghang',
    'inutil'       => 'walang silbi',
    'tarantado'    => 'pasaway',
    'inamo'        => 'hay naku',
    'piste'        => 'bwisit',
    'yawa'         => 'lintik',
    'puke'         => 'halaman',
    'tite'         => 'talong',
    'ngongo'       => 'utal-utal',

    // English
    'motherfucker' => 'blackguard',
    'fucking'      => 'fudging',
    'fuck'         => 'fudge',
    'shit'         => 'dung',
    'bitch'        => 'wench',
    'asshole'      => 'buffoon',
    'bastard'      => 'scoundrel',
    'cunt'         => 'wretch',
    'whore'        => 'harlot',
    'slut'         => 'rascal',
    'nigger'       => 'fool',
    'nigga'        => 'fool',
  ];

  // Sort longest first so bigger phrases get replaced before shorter ones
  uksort($map, function ($a, $b) {
    return mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8');
  });

  foreach ($map as $bad => $replacement) {
    $pattern = '/' . preg_quote($bad, '/') . '/iu';

    $text = preg_replace_callback($pattern, function ($m) use ($replacement) {
      $original = $m[0];

      // ALL CAPS
      if (mb_strtoupper($original, 'UTF-8') === $original) {
        return mb_strtoupper($replacement, 'UTF-8');
      }

      // First letter capitalized
      $first = mb_substr($original, 0, 1, 'UTF-8');
      $rest  = mb_substr($original, 1, null, 'UTF-8');

      if (
        mb_strtoupper($first, 'UTF-8') === $first &&
        mb_strtolower($rest, 'UTF-8') === $rest
      ) {
        $repFirst = mb_strtoupper(mb_substr($replacement, 0, 1, 'UTF-8'), 'UTF-8');
        $repRest  = mb_substr($replacement, 1, null, 'UTF-8');
        return $repFirst . $repRest;
      }

      return $replacement;
    }, $text);
  }

  return $text;
}