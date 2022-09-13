// JavaScript Document

$.prototype.showDialog = function(strTitle, intWidth, intHeight){
	var strTitle = strTitle || document.title;
	var intWidth = intWidth || 'auto';
	var intHeight = intHeight || 'auto';
	var title = '<img src="'+$('link[rel="icon"]').attr('href')+'" />'+strTitle;
	$(this).dialog({
		dialogClass: "ui-dialog-logo",
		height: intHeight,
		width: intWidth,
		title: title,
		modal: true
	});
};

$.prototype.showForm = function(strTitle, oButtons, intWidth, intHeight){
	var strTitle = strTitle || document.title;
	var intWidth = intWidth || 'auto';
	var intHeight = intHeight || 'auto';
	if(typeof(strTitle) === 'undefined'){
		strTitle = '<img src="'+$('link[rel="icon"]').attr('href')+'" />'+document.title;
	} else {
		strTitle = '<img src="'+$('link[rel="icon"]').attr('href')+'" />'+strTitle;
	}
	$(this).dialog({
		dialogClass: "ui-dialog-logo ui-dialog-form",
		height: intHeight,
		width: intWidth,
		title: strTitle,
		buttons : oButtons,
		modal: true
	});
	return this;
};

function jDialog(strMessage, strTitle, intWidth, intHeight){
	var strTitle = strTitle || document.title;
	var intWidth = intWidth || 'auto';
	var intHeight = intHeight || 'auto';
	if($('#dialog').length == 0){
		$('body').append('<div id="dialog"></div>');
	}
	if(typeof(strMessage) == 'undefined'){
		strMessage = '';
	} else {
		//format content for output
		strMessage = strMessage.replace(/\n/g, '<br />');
	}
	$('#dialog').html(strMessage).showDialog(strTitle, intWidth, intHeight);
}

function closeDialog(){
	$(this).dialog( "close" );
}

function jAlert(strMessage, callback){
	if(typeof(strMessage) == 'undefined'){
		strMessage = '';
	} else {
		//format content for output
		strMessage = $('<div/>').text(strMessage).html();
		strMessage = strMessage.replace(/\n/g, '<br />');
	}
	if(typeof(callback) === 'undefined'){
		callback = false;
	}
	var title = '<img src="'+$('link[rel="icon"]').attr('href')+'" />'+document.title;
	$('<div>').html(strMessage).dialog({
		dialogClass: "ui-dialog-logo",
		width: '400',
		title: title,
		buttons: { "OK" : function(){
			$(this).dialog( "close" );
			$(this).remove();
			if(callback !== false){
				callback();
			}
		}},
		modal: true
	});
	return false;
};

function jConfirm(strMessage, fnOnYes, fnOnNo){
	if(typeof(strMessage) == 'undefined'){
		strMessage = '';
	} else {
		//format content for output
		strMessage = $('<div/>').text(strMessage).html();
		strMessage = strMessage.replace(/\n/g, '<br />');
	}
	if(typeof(fnOnYes) == 'undefined'){
		fnOnYes = false;
	}
	if(typeof(fnOnNo) == 'undefined'){
		fnOnNo = false;
	}
	if($('#confirm').length == 0){
		$('body').append('<div id="confirm"></div>');
	}
	var title = '<img src="'+$('link[rel="icon"]').attr('href')+'" />'+document.title;
	$('#confirm').html(strMessage).dialog({
		dialogClass: "ui-dialog-logo",
		width: '400',
		title: title,
		buttons: { "Yes" : function(){
						if(fnOnYes !== false){
							fnOnYes(this);
						}
						$(this).dialog( "close" );
						return true;
					},
					'No' : function(){
						if(fnOnNo !== false){
							fnOnNo(this);
						}
						$(this).dialog( "close" );
						return false;
					}},
		modal: true
	});

	return false;
};

function jPrompt(strMessage, strDefaultVal, callback){
	if(typeof(strMessage) == 'undefined'){
		strMessage = '';
	} else {
		//format content for output
		strMessage = $('<div/>').text(strMessage).html();
		strMessage = strMessage.replace(/\n/g, '<br />');
	}
	if(typeof(strDefaultVal) == 'undefined' || strDefaultVal === false){
		strDefaultVal = '';
	}
	if(typeof(callback) == 'undefined'){
		callback = false;
	}
	if($('#prompt').length == 0){
		$('body').append('<div id="prompt"></div>');
	}
	var htmlForm = '<label for="prompt_input" style="display:block; margin-bottom: 10px;">'+strMessage+'</label><input type="text" name="prompt_input" id="prompt_input" value="'+strDefaultVal+'" class="text ui-widget-content ui-corner-all" style="width:100%;">';
	var title = '<img src="'+$('link[rel="icon"]').attr('href')+'" />'+document.title;
	$('#prompt').html(htmlForm).dialog({
		dialogClass: "ui-dialog-logo",
		width: '400',
		title: title,
		buttons: { "OK" : function(){

						var val = $('#prompt_input').val();
						if(callback !== false){
							callback(val, this);
						}
						$(this).dialog( "close" );
						return true;
					},
					Cancel : function(){
						$(this).dialog( "close" );
						return false;
					}},
		modal: true
	});

	return strDefaultVal;
};


var Base64 = {

		// private property
		_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

		encode_blocks : function(input1) {
			var output = "";
			var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
			var i = 0;

			var input = new Array();
			for (i = 0; i < input1.length; i++) {
				input[i * 4] = input1[i] & 0xFF;
				input[i * 4 + 1] = (input1[i] >>> 8) & 0xFF;
				input[i * 4 + 2] = (input1[i] >>> 16) & 0xFF;
				input[i * 4 + 3] = (input1[i] >>> 24) & 0xFF;
			}
			i = 0;
			while (i < input.length) {

				chr1 = input[i++];// input.charCodeAt(i++);
				chr2 = input[i++];// input.charCodeAt(i++);
				chr3 = input[i++];// input.charCodeAt(i++);

				enc1 = chr1 >> 2;
				enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
				enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
				enc4 = chr3 & 63;

				if (isNaN(chr2)) {

					enc3 = enc4 = 64;
				} else if (isNaN(chr3)) {
					enc4 = 64;
				}

				output = output + this._keyStr.charAt(enc1)
						+ this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3)
						+ this._keyStr.charAt(enc4);

			}
			delete input;
			return output;
		},

		// public method for decoding
		decode_blocks : function(input) {
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
			for (i = 0; i < j; i++) {
				output1[i] = output[4 * i] | (output[4 * i + 1] << 8)
						| (output[4 * i + 2] << 16) | (output[4 * i + 3] << 24);
			}

			delete output;

			return output1;

		},

		// public method for encoding
		encode : function(input) {
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

				output = output + this._keyStr.charAt(enc1)
						+ this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3)
						+ this._keyStr.charAt(enc4);

			}

			return output;
		},

		// public method for decoding
		decode : function(input) {
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
		_utf8_encode : function(string) {
			string = string.replace(/\r\n/g, "\n");
			var utftext = "";

			for ( var n = 0; n < string.length; n++) {

				var c = string.charCodeAt(n);

				if (c < 128) {
					utftext += String.fromCharCode(c);
				} else if ((c > 127) && (c < 2048)) {
					utftext += String.fromCharCode((c >> 6) | 192);
					utftext += String.fromCharCode((c & 63) | 128);
				} else {
					utftext += String.fromCharCode((c >> 12) | 224);
					utftext += String.fromCharCode(((c >> 6) & 63) | 128);
					utftext += String.fromCharCode((c & 63) | 128);
				}

			}

			return utftext;
		},

		// private method for UTF-8 decoding
		_utf8_decode : function(utftext) {
			var string = "";
			var i = 0;
			var c = c1 = c2 = 0;

			while (i < utftext.length) {

				c = utftext.charCodeAt(i);

				if (c < 128) {
					string += String.fromCharCode(c);
					i++;
				} else if ((c > 191) && (c < 224)) {
					c2 = utftext.charCodeAt(i + 1);
					string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
					i += 2;
				} else {
					c2 = utftext.charCodeAt(i + 1);
					c3 = utftext.charCodeAt(i + 2);
					string += String.fromCharCode(((c & 15) << 12)
							| ((c2 & 63) << 6) | (c3 & 63));
					i += 3;
				}

			}

			return string;
		}

	};
