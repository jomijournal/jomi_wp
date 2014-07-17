/**
	Here's a slightly changed code of:
	Secure Hash Algorithm (SHA1) - http://www.webtoolkit.info/
**/
function SHA1(msg) {
	function rotate_left(n,s) {
		var t4 = ( n<<s ) | (n>>>(32-s));
		return t4;
	};

	function lsb_hex(val) {
		var str="";
		var i;
		var vh;
		var vl;

		for( i=0; i<=6; i+=2 ) {
			vh = (val>>>(i*4+4))&0x0f;
			vl = (val>>>(i*4))&0x0f;
			str += vh.toString(16) + vl.toString(16);
		}
		return str;
	};

	function cvt_hex(val) {
		var str="";
		var i;
		var v;

		for( i=7; i>=0; i-- ) {
			v = (val>>>(i*4))&0x0f;
			str += v.toString(16);
		}
		return str;
	};

	var blockstart;
	var i, j;
	var W = new Array(80);
	var H0 = 0x67452301;
	var H1 = 0xEFCDAB89;
	var H2 = 0x98BADCFE;
	var H3 = 0x10325476;
	var H4 = 0xC3D2E1F0;
	var A, B, C, D, E;
	var temp;

	var msg = utf2bytes(msg);
	var msg_len = msg.length;

	var word_array = new Array();
	for( i=0; i<msg_len-3; i+=4 ) {
		j = msg[i]<<24 | msg[i+1]<<16 |
		msg[i+2]<<8 | msg[i+3];
		word_array.push( j );
	}

	switch( msg_len % 4 ) {
		case 0:
			i = 0x080000000;
			break;
		case 1:
			i = msg[msg_len-1]<<24 | 0x0800000;
			break;
		case 2:
			i = msg[msg_len-2]<<24 | msg[msg_len-1]<<16 | 0x08000;
			break;
		case 3:
			i = msg[msg_len-3]<<24 | msg[msg_len-2]<<16 | msg[msg_len-1]<<8	| 0x80;
			break;
	}

	word_array.push( i );

	while( (word_array.length % 16) != 14 ) word_array.push( 0 );

	word_array.push( msg_len>>>29 );
	word_array.push( (msg_len<<3)&0x0ffffffff );

	for ( blockstart=0; blockstart<word_array.length; blockstart+=16 ) {

		for( i=0; i<16; i++ ) W[i] = word_array[blockstart+i];
		for( i=16; i<=79; i++ ) W[i] = rotate_left(W[i-3] ^ W[i-8] ^ W[i-14] ^ W[i-16], 1);

		A = H0;
		B = H1;
		C = H2;
		D = H3;
		E = H4;

		for( i= 0; i<=19; i++ ) {
			temp = (rotate_left(A,5) + ((B&C) | (~B&D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		for( i=20; i<=39; i++ ) {
			temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		for( i=40; i<=59; i++ ) {
			temp = (rotate_left(A,5) + ((B&C) | (B&D) | (C&D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		for( i=60; i<=79; i++ ) {
			temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}

		H0 = (H0 + A) & 0x0ffffffff;
		H1 = (H1 + B) & 0x0ffffffff;
		H2 = (H2 + C) & 0x0ffffffff;
		H3 = (H3 + D) & 0x0ffffffff;
		H4 = (H4 + E) & 0x0ffffffff;
	}
	return (cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4)).toLowerCase();
}

function utf2bytes(s) {
	var s = encodeURIComponent(s);
	var l = s.length;
	var q = [];
	for (var i=0; i<l;) {
		var p = s.substr(i, 1);
		if (p=='%') {
			q.push(parseInt(s.substr(i+1,2), 16));
			i+= 3;
		} else {
			q.push(p.charCodeAt(0));
			i+= 1;
		}
	}
	return q;
}

function bytes2hex(arr) {
	var l = arr.length;
	var q = [];
	for (var i=0; i<l; i++) {
		var t = arr[i].toString(16)
		q.push(t.length==1 ? '0'+t : t);
	}
	return q.join('');
}

function hex2bytes(s) {
	var l = s.length;
	var q = [];
	for (var i=0; i<l; i+=2)
		q.push(parseInt(s.substr(i, 2),16));
	return q;
}

function bytes2utf(arr) {
	for (var i=0, l=arr.length; i<l; i++)
		arr[i] = String.fromCharCode(arr[i]);
	return arr.join('');
}

function rc4_arr(key, arr) {
	var s = [];
	for (var i=0; i<256; i++)
		s[i] = i;
	var j = 0, x;
	for (i=0; i<256; i++) {
		j = (j + s[i] + key.charCodeAt(i % key.length)) % 256;
		x = s[i];
		s[i] = s[j];
		s[j] = x;
	}
	i = 0; j = 0;
	var ct = [];
	var al = arr.length;
	for (var y=0; y<al; y++) {
		i = (i + 1) % 256;
		j = (j + s[i]) % 256;
		x = s[i];
		s[i] = s[j];
		s[j] = x;
		ct[y] = arr[y] ^ s[(s[i] + s[j]) % 256];
	}
	return ct;
}
