<?php
/*
SECURE PASSWORD FACTORY - 0.2.1b
By: iadnah :: iadnah@iadnah.net

Get the latest release at iadnah.net!

============== LICENSE AND COPYRIGHT =================================
(c) 2009 iadnah

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
=======================================================================


=============== SECURE PASSWORD FACTORY: ABSTRACT =====================
This class is indended for generating secure, random passwords
for end users. It is meant to produce passwords which are easier
to remember than completely random strings of characters, but
which cannot be easily guessed by software which use the standard
methods of guessing "easy" passwords first.

Most people create passwords based on a semi-pronouncable "root"
and then append either a prefix or suffix to the root (or both).
However, most people are not good at doing this and human psychology
tends to lead people to picking from the same pool of about 100,000
prefixes, suffixes, and roots. This software follows that general
method of generating passwords, but instead of pulling from words
and phrases the user "knows" it attempts to generate semi-pronouncable
"chunks" of passwords and appending or prepending random "bridges" of
variable length.

I hope this code is useful to someone. If the license does not allow
you to use it for something you want to use it for please contact me
and I will discuss licensing it to you under a more 
proprietary-friendly license for a reasonable price.

================= DISCLAIMER ==========================================
This software is not guarnteed to be suitable for any purpose and
is not under any sort of warranty. Use it at your own discretion. Do
not assume that any password generated by this is automatically secure
or safe to use for any purpose.
=======================================================================

================ BASIC USE ============================================
All the main methods for this class are public functions you can use
by just initializing the class. The main ones you'll use are genchunk()
and gen_bridge(), as well as subs_scramble() and caps_scramble(). There
are three shortcut functions provided for generating passwords right off
the bat, or you can use your own mix of the aforementioned functions to
do something more complex/custom.

@generate a simple password
	generates a 8 to 12 character password composed of lowercase 
	alphanetical characters, which should be semi-pronouncable

	$gen = new spassfactory();
	$pass = $gen->gen_pass_basic(8, 12);

@generate a mildly complex password
	generates a 8 to 12 character password composed of both upper
	and lowercase characters, which should be semi-pronouncable

	$gen = new spassfactory();
	$pass = $gen->gen_pass_mix(8, 12);


@generate a more complex password
	generaytes a 8 to 12 character password composed of both
	upper and lowercase characters, and replace some of the
	characters with "leet speak" numbers and symbols

	$gen = new spassfactory();
	$pass = $gen->gen_pass_moderate(8, 12)

@generate a fairly complex password, with bridges
public function gen_pass_hard($ccount, $cmin, $cmax, $bset = 1) {
	generates a password composed of 3 pronouncable chunks (as if
	made with gen_pass_moderate) each 3 to 5 characters long. Each
	chunk will have a "bridge" stuck before or after it. Passwords
	made with this should be (fairly) easy to remember and very
	resistant to guessing and brute force attacks.

	$gen = new spassfactory();
	$pass = $gen->gen_pass_hard(3, 4, 5);

=======================================================================


*/

class spassfactory {
	private $vowels = "aeiouy";
	private $cons = "bcdfghjklmnpqrstvwxyz";
	private $num = "1234567890";


	public $subs = array(
		'a' => '4@',
		'e' => '3',
		'l' => '1',
		'i' => '!',
		'g' => '6',
		't' => '7',
		's' => '$',
		'o' => '0'
	);

	private $syms = array(
		'`', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')',
		'-', '_', '=', '+', '[', '{', ']', '}', '|', '\\',
		';', ':', '"', '\'', ',', '<', '.', '>', '/', '?'
	);


	private $vl = 0;
	private $cl = 0;

	public function __contruct() {
		mt_srand();

	}

	/*
		@param min = min length of chunk
		@param max = max length of chunk
	
		if max isn't given it will be $min + 3
	*/
	public function genchunk($min = 1, $max = NULL, $syms = FALSE) {
		if ($max == NULL) {
			$max = ($min + 3);
		}

		$chunk_length = mt_rand($min, $max);
	
		$chunk = '';
		$vowel_count = strlen($this->vowels) - 1;
		$cons_count = strlen($this->cons) - 1;
		$syms_count = count($this->syms) - 1;

		if (!$syms) {
			$i = mt_rand(0,1);
			for ($x=0; $x < $chunk_length; $x++) {
				if ($i > 1) {
					$chunk .= $this->cons[ (mt_rand(0, $cons_count)) ];
				} else {
					$chunk .= $this->vowels[ (mt_rand(0, $vowel_count)) ];
				}
				$i = mt_rand(0,3);
			}
		} else {
			$i = mt_rand(0,5);
			for ($x=0; $x < $chunk_length; $x++) {
				if ($i < 2) {
					$chunk .= $this->cons[ (mt_rand(0, $cons_count)) ];
				} elseif ($i < 5) {
					$chunk .= $this->vowels[ (mt_rand(0, $vowel_count)) ];
				} else {
					$chunk .= $this->syms[ (mt_rand(0, $syms_count)) ];
				}
				$i = mt_rand(0,5);
			}
		}

		return $chunk;
	}

	public function caps_scramble($chunk) {
		$chunk_length = strlen($chunk);
		for ($x = 0; $x < $chunk_length; $x++) {
			$char = $chunk[$x];
			$char_ascii = ord($char);
			if ( $char_ascii >= 65 && $char_ascii <= 90 ) { //character is caps alpha
				$char_new = strtolower($char);
			} elseif ( $char_ascii >= 97 && $char_ascii <= 122) { //character is lower alpha
				$char_new = strtoupper($char);
			} else {
				$char_new = $char;
			}

			if ($char != $char_new) {
				$d = mt_rand(0,3);	//decide whether to flip case
				if ($d > 0) {
					$chunk[$x] = $char_new;
				}
			}
		}
		return $chunk;
	}

	public function subs_scramble($chunk) {
		$chunk_length = strlen($chunk);
		$new_chunk = '';
		for ($x = 0; $x < $chunk_length; $x++) {
			$char_lower = strtolower($chunk[$x]);
			if (isset($this->subs["$char_lower"])) { //check if a substitute exists
				$d = mt_rand(0,1);
				if ($d > 0) {
					$sub_count = strlen($this->subs["$char_lower"]);
					$chunk[$x] = $this->subs["$char_lower"][ mt_rand(0, $sub_count - 1) ];
				}
			}
		}
		return $chunk;
	}

	/*
		generates a "bridge" between chunks

		a bridge is meant to be a short, random string which is likely not pronouncable

		@param $set : choose between different possible character sets

			0 : all printable ASCII characters, including SPACE
			1 : all printable ASCII characters, excluding SPACE
			2 : all lowercase alphabetical characters
			3 : all uppercase alphabetical characters
			4 : all ASCII numerals

	*/
	public function gen_bridge($set = 0) {
		$bridge = '';
		$len = mt_rand(1,4);

		switch ($set) {
			case 0:	$set_mod = 32; $set_add = 94; break;
			case 1:	$set_mod = 33; $set_add = 94; break;
			case 2: $set_mod = 26; $set_add = 97; break;
			case 3: $set_mod = 26; $set_add = 65; break;
			case 4; $set_mod = 10; $set_add = 48; break;
		}


		for ($x = 0; $x < $len; $x++) {
			$bridge .= chr(mt_rand()%($set_mod) + ($set_add));
			//$bridge .= chr(mt_rand()%95 + 26);
		}
		return $bridge;
	}

	public function gen_pass_basic($min, $max) {
		//format: chunk bridge chunk
		return $this->genchunk($min, $max);
	}

	public function gen_pass_mix($min, $max) {
		return $this->caps_scramble($this->genchunk($min, $max));
	}

	public function gen_pass_moderate($min, $max) {
		return $this->subs_scramble($this->caps_scramble($this->genchunk($min, $max)));
	}

	/*
		@param ccount : number of chunks in password
		@param cmin : min length of chunks
		@param cmax : length of chunks
		@param bset : which character set to generate bridge(s) from
			default: 1 (all printable ascii characters except space)
	*/
	public function gen_pass_hard($ccount, $cmin, $cmax, $bset = 1) {

		$chunks = array(); $pass = '';
		for ($x = 0; $x < $ccount; $x++) {
			$chunks[] = $this->gen_pass_moderate($cmin, $cmax);
		}

		foreach ($chunks as $chunk) {
			$bridge = $this->gen_bridge($bset);
			//decide if bridge goes before or after
			$d = mt_rand(0, 2);
			if ($d > 0) {
				$pass .= $chunk. $bridge;
			} else {
				$pass .= $bridge. $chunk;
			}
		}
		return $pass;
	}
}
?>
