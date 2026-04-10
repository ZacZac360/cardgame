<?php

function profanity_word_list(): array {
  return [
    // -------------------------
    // Tagalog / Filipino
    // -------------------------
    'putangina',
    'pukinangina',
    'kingina',
    'inamo',
    'puta',
    'piste',
    'yawa',
    'gago',
    'gagi',
    'bobo',
    'obob',
    'tanga',
    'ulol',
    'ulul',
    'inutil',
    'tarantado',
    'ngongo',
    'hindot',
    'kantot',
    'jakol',
    'salsal',
    'chupa',
    'burat',
    'oten',
    'kepyas',
    'kupal',
    'punyeta',
    'bilat',
    'pekpek',
    'betlog',
    'bayag',
    'ratbu',
    'timang',

    // -------------------------
    // Regional / PH slang / insults
    // -------------------------
    'buang',
    'dugyot',
    'pisot',
    'palautog',
    'bungi',
    'kigwahon',
    'geatay',
    'loslos',
    'monggi',

    // multi-word / phrase forms
    'anak sa chanak',
    'bahog bugan',
    'anak matoy',
    'opaw',
    'kalbo',

    // -------------------------
    // English profanity
    // -------------------------
    'fuck',
    'fucking',
    'shit',
    'bitch',
    'dumbass',
    'asshole',
    'bastard',
    'cunt',
    'whore',
    'slut',
    'dickhead',
    'cock',
    'cocksucker',
    'motherfucker',
    'pussy',

    // -------------------------
    // Slurs / strongly offensive
    // -------------------------
    'retard',
    'retarded',
    'faggot',
    'nigger',
    'nigga',
    'mongoloid',
    'intsik',

    // -------------------------
    // Context-sensitive PH queer slang
    // Note: these can be reclaimed/used casually in some contexts,
    // but included because you asked for stricter filtering.
    // -------------------------
    'bakla',
    'bading',
    'badeng',
    'bayot',
    'akla',
    'accla',
  ];
}

/**
 * Extra aliases / bypass forms that should map to a canonical bad word.
 * This helps catch gamer spelling without bloating the main list too much.
 */
function profanity_alias_map(): array {
  return [
    // puta family
    'pota'         => 'puta',
    'pota'         => 'puta',
    'puta mo'      => 'puta',
    'putam0'       => 'puta',
    'pu7a'         => 'puta',
    'pvt4'         => 'puta',
    'phuta'        => 'puta',

    // putangina family
    'ptngina'      => 'putangina',
    'putang ina'   => 'putangina',
    'p u t a n g i n a' => 'putangina',

    // gago family
    'gagu'         => 'gago',
    'g4g0'         => 'gago',

    // bobo family
    'b0b0'         => 'bobo',

    // ulol family
    'ulul'         => 'ulol',

    // kantot / sexual
    'k4ntot'       => 'kantot',
    'j4kol'        => 'jakol',
    's4lsal'       => 'salsal',
    'chupa'        => 'chupa',
    'tsupa'        => 'chupa',

    // english
    'phuck'        => 'fuck',
    'fck'          => 'fuck',
    'fucc'         => 'fuck',
    'fuk'          => 'fuck',
    'fuq'          => 'fuck',
    'sh1t'         => 'shit',
    'b1tch'        => 'bitch',
    'biatch'       => 'bitch',
    'd1ckhead'     => 'dickhead',
    'azzhole'      => 'asshole',
    'mf'           => 'motherfucker',

    // slurs
    'niqqa'        => 'nigga',
    'n1gga'        => 'nigga',
    'n1gger'       => 'nigger',
    'faqqot'       => 'faggot',
  ];
}

/**
 * Normalize text so common bypasses are easier to catch.
 * Examples:
 *   f.u.c.k    -> fuck
 *   sh1t       -> shit
 *   p u t a    -> puta
 *   puuutaaa   -> puta
 *   phuck      -> phuck (alias map can catch it)
 */
function profanity_normalize(string $text): string {
  $text = mb_strtolower($text, 'UTF-8');

  $map = [
    '0' => 'o',
    '1' => 'i',
    '3' => 'e',
    '4' => 'a',
    '5' => 's',
    '6' => 'g',
    '7' => 't',
    '8' => 'b',
    '@' => 'a',
    '$' => 's',
    '!' => 'i',
    '+' => 't',
  ];

  $text = strtr($text, $map);

  // common separator/punctuation bypasses
  $text = preg_replace('/[\s\-_\.]+/u', '', $text) ?? $text;

  // strip everything except letters/numbers after mapping
  $text = preg_replace('/[^a-z0-9]+/u', '', $text) ?? $text;

  // collapse long repeated chars:
  // putaaaa -> puta
  // biiiitch -> bitch
  // fuuuuuck -> fuck
  $text = preg_replace('/(.)\1{2,}/u', '$1', $text) ?? $text;

  return $text;
}

/**
 * Returns the main blocked words plus aliases, normalized into one searchable set.
 */
function profanity_search_terms(): array {
  $terms = [];

  foreach (profanity_word_list() as $word) {
    $norm = profanity_normalize($word);
    if ($norm !== '') {
      $terms[$norm] = $word;
    }
  }

  foreach (profanity_alias_map() as $alias => $canonical) {
    $aliasNorm = profanity_normalize($alias);
    if ($aliasNorm !== '') {
      $terms[$aliasNorm] = $canonical;
    }
  }

  return $terms;
}

function profanity_matches(string $text): array {
  $hits = [];

  if (trim($text) === '') {
    return $hits;
  }

  $normalized = profanity_normalize($text);
  $terms = profanity_search_terms();

  foreach ($terms as $needle => $canonical) {
    if ($needle !== '' && str_contains($normalized, $needle)) {
      $hits[] = $canonical;
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

/**
 * Straight asterisk censor.
 * Replaces direct bad words and some common alias spellings in visible text.
 */
function censor_profanity(string $text, string $mask = '*'): string {
  $patterns = [];

  foreach (profanity_word_list() as $bad) {
    $patterns[$bad] = $bad;
  }

  foreach (profanity_alias_map() as $alias => $canonical) {
    $patterns[$alias] = $canonical;
  }

  // longest first so "motherfucker" goes before "fuck"
  uksort($patterns, function ($a, $b) {
    return mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8');
  });

  foreach ($patterns as $bad => $_canonical) {
    $pattern = '/(?<!\pL)' . preg_quote($bad, '/') . '(?!\pL)/iu';

    $text = preg_replace_callback($pattern, function ($m) use ($mask) {
      return str_repeat($mask, mb_strlen($m[0], 'UTF-8'));
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
    // Tagalog / Filipino
    'putangina'    => 'susmaryosep',
    'pukinangina'  => 'susmaryosep',
    'kingina'      => 'hay naku',
    'inamo'        => 'hay naku',
    'puta'         => 'bruha',
    'piste'        => 'bwisit',
    'yawa'         => 'lintik',
    'gago'         => 'siraulo',
    'gagi'         => 'siraulo',
    'bobo'         => 'unggoy',
    'obob'         => 'unggoy',
    'tanga'        => 'hunghang',
    'ulol'         => 'lokoloko',
    'ulul'         => 'lokoloko',
    'inutil'       => 'walang silbi',
    'tarantado'    => 'pasaway',
    'ngongo'       => 'utal-utal',
    'hindot'       => 'salbahe',
    'kantot'       => 'harot',
    'jakol'        => 'kamot',
    'salsal'       => 'kamot',
    'chupa'        => 'subo',
    'burat'        => 'talong',
    'oten'         => 'talong',
    'kepyas'       => 'halaman',
    'kupal'        => 'pasaway',
    'punyeta'      => 'bwiset',
    'bilat'        => 'halaman',
    'pekpek'       => 'halaman',
    'betlog'       => 'itlog',
    'bayag'        => 'itlog',
    'ratbu'        => 'pasaway',
    'timang'       => 'loko',

    // English
    'motherfucker' => 'blackguard',
    'fucking'      => 'fudging',
    'fuck'         => 'fudge',
    'shit'         => 'dung',
    'bitch'        => 'wench',
    'dumbass'      => 'buffoon',
    'asshole'      => 'buffoon',
    'bastard'      => 'scoundrel',
    'cunt'         => 'wretch',
    'whore'        => 'harlot',
    'slut'         => 'rascal',
    'dickhead'     => 'nitwit',
    'cock'         => 'rooster',
    'cocksucker'   => 'scoundrel',
    'pussy'        => 'cat',

    // slurs / strong language
    'retarded'     => 'foolish',
    'retard'       => 'fool',
    'faggot'       => 'rascal',
    'nigger'       => 'fool',
    'nigga'        => 'fool',
    'mongoloid'    => 'foolish',
    'intsik'       => 'fellow',

    // context-sensitive words
    'bakla'        => 'friend',
    'bading'       => 'friend',
    'badeng'       => 'friend',
    'bayot'        => 'friend',
    'akla'         => 'friend',
    'accla'        => 'friend',
  ];

  // alias spellings should also map to canonical replacements
  foreach (profanity_alias_map() as $alias => $canonical) {
    if (isset($map[$canonical])) {
      $map[$alias] = $map[$canonical];
    }
  }

  // Sort longest first so bigger phrases get replaced before shorter ones
  uksort($map, function ($a, $b) {
    return mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8');
  });

  foreach ($map as $bad => $replacement) {
    $pattern = '/(?<!\pL)' . preg_quote($bad, '/') . '(?!\pL)/iu';

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