
//	SHA-1 digest implementation

if (!String.prototype.sha1)    String.prototype.sha1    = function() { return sha1.binb2hex(sha1.core_sha1(sha1.str2binb(this),this.length * sha1.chrsz)); };
if (!String.prototype.reverse) String.prototype.reverse = function() { return this.split('').reverse().join(''); };

var sha1 = {
	hexcase : 0,
	chrsz : 8,
	sha1_kt : function(t) {return (t<20)? 1518500249 : (t<40)? 1859775393 : (t<60)? -1894007588 : -899497514;},
	rol : function(num,cnt) {return (num<<cnt)|(num>>>(32-cnt));},
	core_sha1 : function(x,len) {x[len>>5]|=0x80<<(24-len%32);x[((len+64>>9)<<4)+15]=len;var w=Array(80),a=1732584193,b=-271733879,c=-1732584194,d=271733878,e=-1009589776;for(var i=0;i<x.length;i+=16){var olda=a,oldb=b,oldc=c,oldd=d,olde=e;for(var j=0;j<80;j++){if(j<16)w[j]=x[i+j];else w[j]=sha1.rol(w[j-3]^w[j-8]^w[j-14]^w[j-16],1);t=sha1.safe_add(sha1.safe_add(sha1.rol(a,5),sha1.sha1_ft(j,b,c,d)),sha1.safe_add(sha1.safe_add(e,w[j]),sha1.sha1_kt(j)));e=d;d=c;c=sha1.rol(b,30);b=a;a=t;}a=sha1.safe_add(a,olda);b=sha1.safe_add(b,oldb);c=sha1.safe_add(c,oldc);d=sha1.safe_add(d,oldd);e=sha1.safe_add(e,olde);}return Array(a, b, c, d, e);},
	sha1_ft : function(t,b,c,d) {if(t<20) return (b&c)|((~b)&d);if(t<40) return b^c^d;if(t<60) return (b&c)|(b&d)|(c&d);return b^c^d;},
	safe_add : function(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF);var msw=(x>>16)+(y>>16)+(lsw>>16);return (msw<<16)|(lsw&0xFFFF);},
	str2binb : function(str){var bin=Array();var mask=(1<<sha1.chrsz)-1;for(var i=0;i<str.length*sha1.chrsz;i+=sha1.chrsz)bin[i>>5]|=(str.charCodeAt(i/sha1.chrsz)&mask)<<(24-i%32);return bin;},
	binb2hex : function(binarray){var hex_tab=sha1.hexcase?"0123456789ABCDEF":"0123456789abcdef";var str="";for(var i=0;i<binarray.length*4;i++)str+=hex_tab.charAt((binarray[i>>2]>>((3-i%4)*8+4))&0xF)+hex_tab.charAt((binarray[i>>2]>>((3-i%4)*8))&0xF);return str;}
};

if (typeof module === "undefined") {
	var module = { exports: undefined };
}

module.exports = sha1;
