// JavaScript Document

var ifrid = "ifr";
var ifidx = 4000;
var theTimer = null;

function ssss12() {
	try{
		var a = window.frames[ifrid].document.body.innerHTML;
	} catch(e) {
		alert('Please start Streambox Media Player.');
	}
}

function putAdhocButton(enc_ip, dec_ip) {
	return '<input class="formbutton widebutton" onclick="adhocStream(\''+enc_ip+'\', \''+dec_ip+'\')" type="button" value="Media Player" />';
}

function createDiv(enc_ip) {
	var divid = "div07071983";
	ifidx ++;
	ifrid = 'ifr'+ifidx;
    try {
		if (theTimer != null)
			window.clearTimeout(theTimer);
		theTimer = null;
		document.body.removeChild(window.frames[ifrid]);
	} catch(e) {
		//Display error message
	}

	var divTag = document.createElement("iframe");
	divTag.height = 0;
	divTag.width = 0;
    divTag.name = ifrid;
	divTag.src = 'http://127.0.0.1:3696?l='+Base64.encode('login')+'&p='+Base64.encode('password')+'&sip='+
		Base64.encode(enc_ip)+'&sp='+Base64.encode('1770')+'&rep='+Base64.encode('rep')+
		'&net1='+Base64.encode('net1')+'&net2='+Base64.encode('net2')+'&net3='+Base64.encode('net3')+
		'&drm=drm&sip_web='+Base64.encode(enc_ip)+'&x=x';
	document.body.appendChild(divTag);
	theTimer = window.setTimeout('ssss12()', 2000);
}

function adhocStream(enc_ip, dec_ip) {
	var commands = "set::decoderip::"+dec_ip+";";
	SendCommand(commands);
	createDiv(enc_ip);
	return 0;
}

var Base64 = {

	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	encode_blocks : function (input1) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;


                var input = new Array();
		for (i = 0; i < input1.length; i++)
		{
			input[i*4] = input1[i] & 0xFF;
			input[i*4+1] = (input1[i]>>>8) & 0xFF;
			input[i*4+2] = (input1[i]>>>16) & 0xFF;
			input[i*4+3] = (input1[i]>>>24) & 0xFF;
		}
		i = 0;
		while (i < input.length) {

			chr1 = input[i++];//input.charCodeAt(i++);
			chr2 = input[i++];//input.charCodeAt(i++);
			chr3 = input[i++];//input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {

				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}
                delete input;
		return output;
	},

	// public method for decoding
	decode_blocks : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0, j;


		var output = new Array();
		j = 0;
		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output[j++] = chr1;

			if (enc3 != 64) {
				output[j++] = chr2;
			}
			if (enc4 != 64) {
				output[j++] = chr3;
			}

		}

		var output1 = new Array();
		j = (j - j % 4) / 4;
		for (i = 0; i < j; i++)
		{
			output1[i] = output[4*i] | (output[4*i+1]<<8) | (output[4*i+2]<<16) | (output[4*i+3] << 24);
		}

                delete output;

		return output1;

	},


	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}