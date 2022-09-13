<?php

class SBSECURE{
	private $base64_chars = "ABCDEFGHIJKLMNOPQ";

	public function encode($data) {
		$base64_chars = $this->base64_chars;
		$in_len = strlen($data);
		$i = 0;
		$j = 0;
		$ret = "";
		$char_array_3 = "00";
		$char_array_4[0] = 0;  	$char_array_4[1] = 0;
		$bytes_to_encode = 0;
		while ($in_len--) {
			$char_array_3[$i++] = $data[$bytes_to_encode++];
			if ($i == 1) {
				$char_array_4[0] = ord($char_array_3[0]) & 15;
				$char_array_4[1] = ord($char_array_3[0]) >> 4;

				for($i = 0; ($i <2 ) ; $i++) {
					$ret .= $base64_chars[$char_array_4[$i]];
				}
				$i = 0;
			}
		}

		return $ret;
	}

	public function decode($data) {
		$base64_chars = $this->base64_chars;
		$in_len = strlen($data);
		$i = 0;
		$j = 0;
		$ret = "";
		$char_array_3 = "00";
		$char_array_4[0] = 0;  	$char_array_4[1] = 0;
		$bytes_to_encode = 0;
		while ($i < strlen($data)) {
			$pos1 = strpos($base64_chars, $data[$i++]);
			if ($pos1 === FALSE) {
				return "";
			}
			$pos2 = strpos($base64_chars, $data[$i++]);
			if ($pos2 === FALSE) {
				return "";
			}
			$ret .= chr($pos1 + ($pos2 << 4));
		}
		return $ret;
	}
}
?>