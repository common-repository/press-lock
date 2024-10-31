(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


    $(function(){
        $( "#tabs" ).tabs({
        });


		// <!--Start of Tawk.to Script-->
		// var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
		// var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
		// s1.async=true;
		// s1.src='https://embed.tawk.to/5d30c1a89b94cd38bbe81717/default';
		// s1.charset='UTF-8';
		// s1.setAttribute('crossorigin','*');
		// s0.parentNode.insertBefore(s1,s0);
		// <!--End of Tawk.to Script-->



		/**
		 * Calculate progress bar
		 */
		var barProgress = {
			/**
			 * Calculate rate
			 *
			 * @param section
			 * @returns {number}
			 */
			'calculate': function (section) {
				var countCheck = $(section + ' .fa-check').length;
				var countDouble = $(section + ' .fa-check-double').length;
				var countExclamation = $(section + ' .fa-exclamation').length;
				var total = countCheck + countDouble + countExclamation;

				return (countCheck + countDouble) * (100 / total);
			},

			/**
			 * Apply progress rate to bar
			 *
			 * @param progressBar
			 * @param sectionBody
			 */
			'start': function (progressBar, sectionBody) {
				var rate = this.calculate(sectionBody);

				$(progressBar).css('width', rate + '%');
			}
		}

		// Set value progress bar
		barProgress.start('#statusReportProgressBar', '#statusReportBody');
		barProgress.start('#statusBasicScanProgressBar', '#statusBasicScanBody');
    });

})( jQuery );
