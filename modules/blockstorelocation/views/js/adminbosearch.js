$(function(){	
	var input =  document.getElementById('BLOCK_STORE_LOCATION_ADDRESS');
	function initAutocomplete(){
		var autocomplete =  new google.maps.places.Autocomplete(
	     	input,
	      { componentRestrictions: {country: 'fr'}
	  	});
	}
	$(input).on('focus',initAutocomplete);
});
