	// hArpanet.com
	// 29-Jan-2013
	// adapted from: http://web.enavu.com/tutorials/how-to-make-a-completely-reusable-jquery-modal-window/
	//
	// For this to work, you need the following HTML within the webpage...
	//
	//			<div id='modal-container' class='close_modal'><div id='modal-body'></div></div>
	//
	//		Also need to add   class="modalview"   to any element used to call the modal window
	//
	//		This particular implementation (for Dashboard) looks for an attribute named 'type' on
	//		the element containing the 'modalview' class.
	//			eg. <div class="widget_heading modalview" type="heading">

	function close_modal()
	{
		// re-enable scrolling on main webpage
		jQuery("body").css({ overflow: 'inherit' })

    	//hide the mask
        jQuery('#modal-container').fadeOut(500);

        //hide modal window(s)
        jQuery('.modal_body').fadeOut(500);
    }

    function show_modal(modal_id)
    {
    	// disable scrolling of main webpage
    	jQuery("body").css({ overflow: 'hidden' })

    	//set display to block and opacity to 0 so we can use fadeTo
        jQuery('#modal-container').css({ 'display' : 'block', opacity : 0});

        //fade in the mask to opacity 0.8
        jQuery('#modal-container').fadeTo(500,1);

         //show the modal window
        jQuery('#'+modal_id).fadeIn(500);
    }

    jQuery(document).ready(function()
    {
		// close the window when clicked
//    	jQuery('.close_modal').click(function()
	    jQuery('#modal-container').click(function()
	    {
	        //use the function to close it
	        close_modal();
	    });

        // don't process href links via jQuery
        jQuery('.modalview a').click(function(e){e.stopPropagation();});

		// process jQuery events on the modal elements
	    jQuery('.modalview').click(function(event)
	    {
			// get type of element clicked (image or heading)
	    	var type	= jQuery(this).attr('type');
	    	if (type == 'heading')
	    	{
	    		// pull the heading and content sections
	    		// (this allows us to ignore the column width settings of the parent)
	    		domobj = this.parentElement.children[0].outerHTML +
	    				 '<div class="modal_zoom">' +
	    				 	this.parentElement.children[1].outerHTML +
	    				 '</div>';
	    	} else {

	    		domobj 	= '<div class="modal_zoom">' +
	    					this.outerHTML +
	    				  '</div>';
	    	}

	    	var modal_id = 'modal-body'; // id of content block within modal window

	    	// fill the modal content
	    	jQuery('#'+modal_id).html(domobj);

   	        // show it
            show_modal(modal_id);

            event.preventDefault();
	    });
	});
