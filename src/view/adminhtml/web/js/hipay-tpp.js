
/*
 * ! https://github.com/davidchambers/Base64.js
 */
;(function () {

  var object = typeof exports != 'undefined' ? exports : this; // #8: web
																// workers
  var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

  function InvalidCharacterError(message) {
    this.message = message;
  }
  InvalidCharacterError.prototype = new Error;
  InvalidCharacterError.prototype.name = 'InvalidCharacterError';

  // encoder
  // [https://gist.github.com/999166] by [https://github.com/nignag]
  object.btoa || (
  object.btoa = function (input) {
    for (
      // initialize result and counter
      var block, charCode, idx = 0, map = chars, output = '';
      // if the next input index does not exist:
      // change the mapping table to "="
      // check if d has no fractional digits
      input.charAt(idx | 0) || (map = '=', idx % 1);
      // "8 - idx % 1 * 8" generates the sequence 2, 4, 6, 8
      output += map.charAt(63 & block >> 8 - idx % 1 * 8)
    ) {
      charCode = input.charCodeAt(idx += 3/4);
      if (charCode > 0xFF) {
        throw new InvalidCharacterError("'btoa' failed: The string to be encoded contains characters outside of the Latin1 range.");
      }
      block = block << 8 | charCode;
    }
    return output;
  });

  // decoder
  // [https://gist.github.com/1020396] by [https://github.com/atk]
  object.atob || (
  object.atob = function (input) {
    input = input.replace(/=+$/, '')
    if (input.length % 4 == 1) {
      throw new InvalidCharacterError("'atob' failed: The string to be decoded is not correctly encoded.");
    }
    for (
      // initialize result and counters
      var bc = 0, bs, buffer, idx = 0, output = '';
      // get next character
      buffer = input.charAt(idx++);
      // character found in table? initialize bit storage and add its ascii
		// value;
      ~buffer && (bs = bc % 4 ? bs * 64 + buffer : buffer,
        // and if not first of each 4 characters,
        // convert the first 8 bits to one ascii character
        bc++ % 4) ? output += String.fromCharCode(255 & bs >> (-2 * bc & 6)) : 0
    ) {
      // try to find character in table (0-63, not found => -1)
      buffer = chars.indexOf(buffer);
    }
    return output;
  });

}());

define(["reqwest"], function(reqwest) {
	'use strict';

/**
 * TPP library to tokenizer credit cards
 */
var TPP = {
	allowedParameters: {
		'card_number':true, 
		'card_holder':true, 
		'card_expiry_month':true, 
		'card_expiry_year':true,
		'cvc':true, 
		'multi_use':true,
		'generate_request_id':true
	},
	target: 'production',
	username: '',
	publicKey: '',

	isCardNumberValid: function (value) {
		  // accept only digits, dashes or spaces
		if (/[^0-9-\s]+/.test(value)) return false;

		// The Luhn Algorithm. It's so pretty.
		var nCheck = 0, nDigit = 0, bEven = false;
		value = value.replace(/\D/g, "");

		for (var n = value.length - 1; n >= 0; n--) {
			var cDigit = value.charAt(n),
				  nDigit = parseInt(cDigit, 10);

			if (bEven) {
				if ((nDigit *= 2) > 9) nDigit -= 9;
			}

			nCheck += nDigit;
			bEven = !bEven;
		}

		return (nCheck % 10) == 0;
	},
	
	isValid: function (params) {
		var errors = {'code':0, 'message':''};
		var unallowedParams = [];
		for (var key in params) {
			if (this.allowedParameters[key] != true) {
				unallowedParams.push(key);
			}
		}
		
		if (unallowedParams.length > 0) {
			
			errors.code = 408;
			var message = 'unallowed parameters: {'
			for (var key in unallowedParams) {
				message += unallowedParams[key] + ' ';
			}
			message += '}';
			message += ' allowed parameters are: {';
				
			for (var key in this.allowedParameters) {
				message += key;
				message += ' ';
			}
			message += '}';
			
			errors.message = message;
		}
		
		if ( ! this.isCardNumberValid(params['card_number']) ) {
			errors.code = 409;
			errors.message = 'cardNumber is invalid : luhn check failed';
		}
		
		return errors;
	},
	
	setTarget: function(target) { 
	    this.target = target; 
    },

    getTarget: function() { 
        return this.target; 
    },
    
    setCredentials: function(username, publicKey) {
        this.username = username;
        this.publicKey = publicKey;
    },
    
    create: function(params, fn_success, fn_failure) {
    	if(params['card_expiry_month'].length < 2) {
	    	params['card_expiry_month'] = '0' + params['card_expiry_month'];
    	}
    	if(params['card_expiry_year'].length == 2) {
	    	params['card_expiry_year'] = '20' + params['card_expiry_year'];
    	}
    	var errors = this.isValid(params);
		if ( errors.code != 0 ) {
    		fn_failure(errors);
    	} else {
    	
	        var endpoint = 'https://secure2-vault.hipay-tpp.com/rest/v2/token/create.json';
	        if (this.getTarget() == 'test' || this.getTarget() == 'stage' ) {
	            endpoint = 'https://stage-secure2-vault.hipay-tpp.com/rest/v2/token/create.json';
	        } else if (this.getTarget() == 'dev') {
	            endpoint = 'http://dev-secure2-vault.hipay-tpp.com/rest/v2/token/create.json';
	        }
	        
	        if (!("generate_request_id" in params)) {
        		params['generate_request_id'] = 0;
	        }
	        
	        reqwest({
	            url: endpoint,
			    crossOrigin: true,
			    method: 'post',
			    headers: {
	                'Authorization': 'Basic ' + window.btoa(this.username + ':' + this.publicKey)
	            },
			    data: params,
			    success: function(resp) {
			    	
			    	if( typeof resp['code'] != 'undefined' )  {
			    		fn_failure({ code: resp['code'], message: resp['message'] });
			    	}  else {
			    		fn_success(resp);
			    	}
		        },
			    error: function (err) {
			    	obj = JSON.parse(err['response']);
		            fn_failure({ code: obj['code'], message: obj['message'] });
		        }
	        });
    	}
    }
};


	return TPP;
});

