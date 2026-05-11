jQuery(document).ready(function($){

	window.xooEl = window.xooEl || {};

	function objectifyForm($form) {
	    var form = $form instanceof jQuery ? $form[0] : $form;

	    // Use FormData to handle all form inputs
	    var formData = new FormData(form);
	    var result = {};

	    formData.forEach(function(value, name) {
	        // Skip File objects (they go into FormData directly for AJAX)
	        if (value instanceof File) return;

	        // Handle repeated fields by converting to arrays
	        if (result.hasOwnProperty(name)) {
	            if (!Array.isArray(result[name])) {
	                result[name] = [result[name]];
	            }
	            result[name].push(value);
	        } else {
	            result[name] = value;
	        }
	    });

    return result;
}




	function parse_notice( message, type ){
		type = (typeof type !== 'undefined') ? type : 'error';
		return xoo_el_localize.html.notice[ type ].replace( '%s', message );
	}

	var classReferral = {
		'xoo-el-login-tgr': 'login',
		'xoo-el-reg-tgr': 'register',
	}

	function getReferral(className){
		return classReferral[className] ? classReferral[className] : '';
	}

	function getFormsTrigger( $container ){

		$container = $container || '';

		var isSingle = false;

		if( $container.length && $container.find('.xoo-el-section[data-section="single"]').length ){
			isSingle = true;
		}

		var formsTrigger = {
			'xoo-el-sing-tgr': 'single',
			'xoo-el-login-tgr': isSingle ? 'single' : 'login',
			'xoo-el-reg-tgr': isSingle ? 'single' : 'register',
			'xoo-el-lostpw-tgr': 'lostpw',
			'xoo-el-resetpw-tgr': 'resetpw',
			'xoo-el-forcereg-tgr': 'register',
			'xoo-el-forcelogin-tgr': 'login',
		}

		return formsTrigger;
	}


	class Container{

		constructor( $container ){
			this.$container 	= $container;
			this.$tabs 			= $container.find('ul.xoo-el-tabs').length ? $container.find( 'ul.xoo-el-tabs' ) : null;
			this.display 		= $container.hasClass('xoo-el-form-inline') ? 'inline' : 'popup';
			this.formRetries 	= 0;

			if( this.$container.attr('data-active') ){
				this.toggleForm( this.$container.attr('data-active') );
			}

			this.createFormsTriggerHTML();

			this.eventHandlers();
		}

		createFormsTriggerHTML(){
			var HTML = '<div style="display: none!important;">';
			$.each( getFormsTrigger(this.$container), function( triggerClass, type ){
				HTML += '<span class="'+triggerClass+'"></span>';
			} )
			HTML += '</div>';
			this.$container.append(HTML);
		}

		eventHandlers(){
			
			this.$container.on( 'submit', '.xoo-el-action-form', this.submitForm.bind(this) ) ;
			this.$container.on( 'click', '.xoo-el-edit-em', this.emailFieldEditClick.bind(this) );
			$( document.body ).on( 'xoo_el_form_submitted', this.singleFormProcess.bind(this) );
			this.formTriggerEvent();
		}


		emailFieldEditClick(e){
			this.toggleForm('single');
			this.$container.find('input[name="xoo-el-sing-user"]').val( $(e.currentTarget).siblings('input').val() ).focus().trigger('keyup');
		}


		formTriggerEvent(){

			var container 	= this,
				formsTrigger = getFormsTrigger(container.$container);

			$.each( formsTrigger, function( triggerClass, formType ){
				$( container.$container ).on( 'click', '.' + triggerClass, function(e){
					e.preventDefault();
					e.stopImmediatePropagation();
					container.toggleForm(formType, getReferral(triggerClass) );
				} )
			} );

		}


		toggleForm( formType, referral ){

			 referral = referral || '';

			this.$container.attr( 'data-active', formType );

			var $section 	= this.$container.find('.xoo-el-section[data-section="'+formType+'"]'),
				activeClass = 'xoo-el-active';

			//Setting section
			if( $section.length ){

				var $sectionForm = $section.find('form');

				this.$container.find('.xoo-el-section').removeClass( activeClass );
				$section.addClass( activeClass );
				$section.find('.xoo-el-notice').html('').hide();
				$section.find('.xoo-el-action-form').show();

				if( $sectionForm.length && referral && $sectionForm.find('input[name="_xoo_el_referral"]').length ){
					$sectionForm.find('input[name="_xoo_el_referral"]').val(referral);
				}

			}

			//Setting Tab
			if( this.$tabs ){	
				this.$tabs.find('li').removeClass( activeClass );
				if( this.$tabs.find('li[data-tab="'+formType+'"]').length ){
					this.$tabs.find('li[data-tab="'+formType+'"]').addClass( activeClass );
				}
			}

			$(document.body).trigger( 'xoo_el_form_toggled', [ formType, this, referral ] ); //backward compat
			this.$container.triggerHandler( 'xoo_el_form_toggled', [ formType, this, referral ] ); //backward compat

		}


		submitForm(e){

			e.preventDefault();

			var $form 			= $(e.currentTarget),
				$button 		= $form.find('button[type="submit"]'),
				$section 		= $form.parents('.xoo-el-section'),
				buttonTxt 		= $button.text(),
				$notice			= $section.find('.xoo-el-notice'),
				formType 		= $section.attr('data-section'),
				container 		= this;

			$notice.html('');

			$button.html( xoo_el_localize.html.spinner ).addClass('xoo-el-processing');

			var form_data = new FormData($form[0]);

			form_data.append( 'action', 'xoo_el_form_action' );
			form_data.append( 'display', container.display );


			$.ajax({
				url: xoo_el_localize.adminurl,
				type: 'POST',
				processData: false,
    			contentType: false,
    			cache: false,
    			enctype: 'multipart/form-data',
				data: form_data,
				complete: function( xhr, status ){

					$button.removeClass('xoo-el-processing').html(buttonTxt); //cleanup

					if( ( status !== 'success' || !xhr.responseJSON || xhr.responseJSON.error === undefined  ) ){

						if( container.formRetries < 2 ){
							$form.submit();
							container.formRetries++;
							return;
						}


						if( xoo_el_localize.errorLog === 'yes' ){
							$notice.html( parse_notice( "We could not process your request, please check console or try again later.", 'error' ) ).show();
						}
						else if( status !== 'error' ){	
							location.reload();
						}
						
					}
				},
				success: function(response){

					container.formRetries = 0;

					//Unexpected response
					if( response.error === undefined ){
						console.log(response);
						//location.reload();
						return;
					}

					if( response.notice ){

						$notice.html(response.notice).show();

						//scrollbar position
						if( container.display === 'inline' ){
							$('html, body').animate({ scrollTop: $notice.offset().top - 100}, 500);
						}

					}

					if ( response.error === 0 ){
						
						if( response.redirect ){
							//Redirect
							setTimeout(function(){
								window.location = response.redirect;
							}, xoo_el_localize.redirectDelay );
						}
						else{
							$form.hide();
						}

						$form.trigger('reset');

						if( formType === 'resetpw' && xoo_el_localize.resetPwPattern === 'link' ){
							$form.add( '.xoo-el-resetpw-hnotice' ).remove();
						}

					}

					$( document.body ).trigger( 'xoo_el_form_submitted', [ response, $form, container ] ); //for backward compatibility
					$form.triggerHandler( 'xoo_el_form_submitted', [ response, $form, container ] );
					
				}
			}).
			fail(function(jqXHR, textStatus, errorThrown){
				if( container.formRetries < 2 ) return;
				$('body, .xoo-el-popup-notice').addClass('xoo-el-popup-notice-active');
				var iframe = $('.xoo-el-notice-wrap iframe').get(0);
				iframe.contentWindow.document.open(); // Open the iframe's document
			    iframe.contentWindow.document.write(jqXHR.responseText); // Write the string
			    iframe.contentWindow.document.close(); // Close and render the iframe's document

			     var iframeDocument = iframe.contentWindow.document;
			     
			     var style = iframeDocument.createElement("style");
					style.textContent = $('.xoo-el-notice-iframestyle').text();

				// Append the style element to the iframe's head
				iframeDocument.head.appendChild(style);
			})
		}


		singleFormProcess( e, response, $form, container ){

			if( this !== container ) return;

			if( response.field ){

				var $field = this.$container.find( response.field );

				if( $field.length ){

					this.toggleForm( $field.closest('.xoo-el-section').attr('data-section') );

					$field.closest('form').show();

					$field.val(response.fieldValue);

					$field.closest('.xoo-el-section').find('.xoo-el-notice').html(response.notice).show();

					var $fieldCont = $field.closest('.xoo-aff-group');

					if( !$fieldCont.find('.xoo-el-edit-em').length ){
						$fieldCont.addClass('xoo-el-block-edit');
						$(xoo_el_localize.html.editField).insertAfter($field);
					}

				}
			}

		}

	}


	class Popup{

		constructor( $popup ){
			this.$popup = $popup;
			this.eventHandlers();
		}

		eventHandlers(){

			if( !xoo_el_localize.preventClosing ){
				this.$popup.on( 'click', '.xoo-el-close, .xoo-el-modal, .xoo-el-opac', this.closeOnClick.bind(this) );
				$(document.body).on( 'xoo_el_popup_toggled.xooEscEvent', this.onPopupToggled.bind(this) );
			}

			$( document.body ).on( 'xoo_el_form_submitted', this.onFormSubmitSuccess.bind(this) );
			this.$popup.on( 'click', '.xoo-el-action-btn', this.setScrollBarOnSubmit.bind(this) );
			$(window).on('hashchange load', this.openViaHash.bind(this) );
			this.triggerPopupOnClick(); //Open popup using link
			if( xoo_el_localize.checkout && xoo_el_localize.checkout.loginEnabled === 'yes' ){
				$('body').on( 'click', '.wc-block-checkout__login-prompt, .wc-block-must-login-prompt', this.checkoutPageLinkClick.bind(this) );
			}
			
		}

		onPopupToggled(e, type){
			if( $('body').hasClass('xoo-el-popup-active') ){
				$(document).on('keydown.xooEscClose', this.closeOnEscPress.bind(this) );
			}
			else{
				$(document).off('keydown.xooEscClose' );
			}
		}


		closeOnEscPress(e){
			if(event.key === "Escape" || event.keyCode === 27 ){
				popup.toggle('hide');
			}
		}

		checkoutPageLinkClick(e){
			e.preventDefault();
			$(e.currentTarget).attr('data-redirect', xoo_el_localize.checkout.loginRedirect).addClass('xoo-el-login-tgr').trigger('click');
		}

		triggerPopupOnClick(){

			$.each( getFormsTrigger(this.$popup), function( triggerClass, formType ){

				$( document.body ).on( 'click', '.' + triggerClass, function(e){

					if( $(this).parents( '.xoo-el-form-container' ).length ) return true; //Let container class handle

					e.preventDefault();
					e.stopImmediatePropagation();

					popup.toggle('show');

					if( $(this).attr( 'data-redirect' ) ){
						popup.$popup.find('input[name="xoo_el_redirect"]').val( $(this).attr('data-redirect') );
					}

					popup.$popup.find( '.'+triggerClass ).trigger('click');

					return false;

				})

			})

		}

		toggle( type ){
			var $els 		= this.$popup.add( 'body' ),
				activeClass = 'xoo-el-popup-active'; 

			if( type === 'show' ){
				$els.addClass(activeClass);
			}
			else if( type === 'hide' ){
				$els.removeClass(activeClass);
			}
			else{
				$els.toggleClass(activeClass);
			}

			$(document.body).trigger( 'xoo_el_popup_toggled', [ type ] );
		}

		closeOnClick(e){
			var elClassList = e.target.classList;
			if( elClassList.contains( 'xoo-el-close' ) || elClassList.contains('xoo-el-modal') || elClassList.contains('xoo-el-opac') ){
				this.toggle('hide');
			}
		}

		setScrollbarPosition( position ){
			this.$popup.find('.xoo-el-srcont').scrollTop = position || 0;
		}

		onFormSubmitSuccess( e, response, $form, container ){
			this.setScrollbarPosition();
		}

		setScrollBarOnSubmit(e){
			var invalid_els = $(e.currentTarget).closest('form').find('input:invalid');
			if( invalid_els.length === 0 ) return;
			this.setScrollbarPosition( invalid_els.filter(":first").closest('.xoo-aff-group').position().top );
		}

		openViaHash(){

	  		var hash = $(location).attr('hash');

	  		if( hash === '#login' || hash === '#register' ){

	  			this.toggle('show');

	  			//Clear hash
	  			var uri = window.location.toString(),
	  		 		clean_uri = uri.substring( 0, uri.indexOf("#") );
	 
	            window.history.replaceState(
	            	{},
	            	document.title, clean_uri
	            );
	  		}

	  		if( hash === '#login' ){
	  			this.$popup.find('.xoo-el-login-tgr').trigger('click');
	  		}
	  		else if( hash === '#register' ){
	  			this.$popup.find('.xoo-el-reg-tgr').trigger('click');
	  		}
    
		}

		
	}

	class Form{

		constructor( $form ){
			this.$form 	= $form;
		}

		eventHandlers(){

		}

	}

	var popup = null;

	//Popup
	if( $('.xoo-el-container').length ){
		popup = new Popup( $('.xoo-el-container') );
	}

	
	//Auto open popup
	if( xoo_el_localize.isLoggedIn === "no" && xoo_el_localize.autoOpenPopup === 'yes' && localStorage.getItem( "xoo_el_popup_opened"  ) !== "yes" ){
		
		if( xoo_el_localize.autoOpenPopupOnce === "yes" ){
			localStorage.setItem( "xoo_el_popup_opened", "yes"  );
		}
		
		setTimeout(function(){
			popup.toggle('show');
		}, xoo_el_localize.aoDelay);
	}
	

	$('.xoo-el-form-container').each(function( key, el ){
		 new Container( $(el) );
	})

	//Trigger popup if reset field link is active
	if( $('form.xoo-el-form-resetpw').length && xoo_el_localize.resetPwPattern === "link" ){
		if( $('.xoo-el-form-inline').length ){
			$([document.documentElement, document.body]).animate({
				scrollTop: $(".xoo-el-form-inline").offset().top
			}, 500);
		}
		else{
			if( popup ){
				popup.toggle('show');
			}
		}
	}


	if( $( 'body.woocommerce-checkout' ).length && $('.xoo-el-form-inline').length && $( 'a.showlogin' ).length ){
  		var $inlineForm = $('.xoo-el-form-inline');
  		$inlineForm.hide();
  		$( document.body ).on( 'click', 'a.showlogin', function(){
  			$inlineForm.slideToggle();
  			$inlineForm.find('.xoo-el-login-tgr').trigger('click');
  		} );	
  	}


  	if( popup && xoo_el_localize.loginClass && $( '.'+xoo_el_localize.loginClass ).length ){
  		$( 'body:not(.logged-in) .'+xoo_el_localize.loginClass ).on( 'click', function(e){
  			e.preventDefault();
  			e.stopImmediatePropagation();
  			popup.toggle('show');
  			popup.$popup.find( '.xoo-el-login-tgr' ).trigger('click');
  		} );
  	}

  	if( popup && xoo_el_localize.registerClass && $( '.'+xoo_el_localize.registerClass ).length ){
  		$( 'body:not(.logged-in) .'+xoo_el_localize.registerClass ).on( 'click', function(e){
  			e.preventDefault();
  			e.stopImmediatePropagation();
  			popup.toggle('show');
  			popup.$popup.find( '.xoo-el-reg-tgr' ).trigger('click');
  		} );
  	}


  	$('.xoo-el-notice-close').on('click', function(){
  		$('body, .xoo-el-popup-notice').removeClass('xoo-el-popup-notice-active');
  	})



  	class CodeFormHandler{

  		constructor( $codeForm ){

  			const existing = CodeFormHandler.instances.get($codeForm[0]);

    		if (existing) return existing;

  			this.$codeForm 		= $codeForm;
  			this.codeFormID 	= this.$codeForm.data('code');
  			this.$parentForm 	= this.$codeForm.data('parentform') ? $(this.$codeForm.siblings(this.$codeForm.data('parentform')).get(0)) : $();

			this.CodePasted 	= false;

			this.resendData 	= {};

			this.$noticeCont 	= this.$codeForm.find('.xoo-el-code-notice');
			
			this.$submitBtn 	= this.$codeForm.find('.xoo-el-code-submit-btn');
			this.submitBtnTxt 	= this.$submitBtn.html();
			this.$inputs 		= this.$codeForm.find('.xoo-el-code-input');
			this.$resendLink 	= this.$codeForm.find('.xoo-el-code-resend-link');
			this.noticeTimout 	= this.resendTimer = false;
			this.parentFormValues = {}
			this.codesent 		= false;

			this.$container 	= this.$codeForm.closest('.xoo-el-form-container');

			this.events();

			CodeFormHandler.instances.set($codeForm[0], this);
  		}


  		events(){

			this.$resendLink.on( 'click', { _thisObj: this }, this.resendCode );
			this.$codeForm.find('.xoo-el-code-no-change').on( 'click', { _thisObj: this }, this.changeParentInput );
			this.$codeForm.on( 'submit', { _thisObj: this }, this.onSubmit );
			this.$inputs.on( 'paste', { _thisObj: this }, this.onCodeInputPaste );
			this.$inputs.on( 'input', { _thisObj: this }, this.onCodeInputChange );
			this.$inputs.on( 'keydown ', { _thisObj: this }, this.beforeCodeInputChange );

			this.$container.on( 'xoo_el_form_toggled', this.onParentFormToggled.bind(this) )


		}


		onParentFormToggled( e, formType, containerObj, referral ){

			let _thisObj = this;

			if( _thisObj.codesent ){
  				_thisObj.$parentForm.hide();
  				_thisObj.$codeForm.show();
  			}
		}

  		onCodeInputPaste(event){

			var _thisObj 		= event.data._thisObj,
				_this 			= $(this);

			_thisObj.CodePasted 	= true;

			setTimeout(function(){

				var inputVal 		= _this.val().trim(),
				inputValLength 		= inputVal.length;

				_thisObj.$inputs.val('');

				for (var i = 0; i < inputValLength; ++i) {

					var chr 		= inputVal.charAt(i),
						$Codeinput 	= $(_thisObj.$inputs.get(i));
					
				    if( $Codeinput.length ){
				    	$Codeinput.val(chr);
				    }

				    if( i === (inputValLength - 1) ){
				    	$Codeinput.focus();
				    }
				}

				if( inputValLength === _thisObj.$inputs.length ){
					_thisObj.$codeForm.trigger('submit');
				}

				_thisObj.CodePasted = false;

			}, 10 )

		}

		onCodeInputChange(event){

			var _thisObj 		= event.data._thisObj,
				inputVal 		= $(this).val(),
				inputValLength 	= inputVal.length;

			if( inputValLength > 1 && !_thisObj.CodePasted  ){
				$(this).trigger('paste');
				return;
			}

			if( _thisObj.CodePasted || _thisObj.processing ){
				return;
			}

			_thisObj.processing = true;

			var $nextInput = $(this).next('input.xoo-el-code-input'),
				$prevInput = $(this).prev('input.xoo-el-code-input');

			
			//Switch Input
			if( inputValLength && $nextInput.length !== 0 ){
				$nextInput.focus();
			}

			
			_thisObj.processing = false;
				
		}

		beforeCodeInputChange(event){

			var _thisObj 		= event.data._thisObj,
				inputVal 		= $(this).val(),
				inputValLength 	= inputVal.length;


			var $nextInput = $(this).next('input.xoo-el-code-input'),
				$prevInput = $(this).prev('input.xoo-el-code-input');


			if( inputVal.length && event.keyCode != 8 && event.keyCode !== 13 ){

				if( $nextInput.length && !$nextInput.val() ){
					$nextInput.focus();
				}
				else{
					$(this).val('');
				}
				
			}

			//Backspace is pressed
			if( !inputValLength && event.keyCode == 8 && $prevInput.length !== 0 ){
				$prevInput.focus();
			}

		}

		onSubmit(event){

			event.preventDefault();

			var _thisObj = event.data._thisObj;

			if( !_thisObj.validateInputs() || !_thisObj.getCodeValue().length ) return false;

			_thisObj.$submitBtn.html( xoo_el_localize.html.spinner ).addClass('xoo-el-processing');

			_thisObj.verifyCode();
		}


		changeParentInput(event){
			var _thisObj = event.data._thisObj;
			_thisObj.codesent = false;
			_thisObj.$codeForm.hide();
			_thisObj.$parentForm.show();
			_thisObj.$inputs.val('');
		}


		resendCode(event){

			event.preventDefault();

			var _thisObj = event.data._thisObj;

			_thisObj.startResendTimer();

			var form_data = {
				action: 'xoo_el_resend_code',
				'parentFormData': objectifyForm( _thisObj.$parentForm),
			}

			form_data = Object.assign({}, form_data, _thisObj.resendData);

			_thisObj.$resendLink.addClass('xoo-el-processing');

			$.ajax({
				url: xoo_el_localize.adminurl,
				type: 'POST',
				data: form_data,
				success: function(response){

					_thisObj.$resendLink.trigger( 'xoo_el_code_resent', [ response, _thisObj ] );

					_thisObj.$resendLink.removeClass('xoo-el-processing');

					if( response.notice ){
						_thisObj.showNotice( response.notice );
					}
					
				},
				complete: function(){

				}
			});
		}


		validateInputs(){

			var passedValidation = true;
			
			this.$inputs.each( function( index, input ){
				var $input = $(input);
				if( $input.val().trim() === '' ){
					$input.focus();
					passedValidation = false;
					return false;
				}
			} );
				
			
			return passedValidation;

		}

		onSuccess(response){
			this.$codeForm.removeAttr('data-processing');
			this.codesent = false;
			this.$codeForm.hide();
			this.$inputs.val('');

			if( this.$parentForm.length ){
				if( response.notice ){
					this.$parentForm.closest('.xoo-el-section').find('.xoo-el-notice').html(response.notice).show();
				}
				this.$parentForm.show();
			}
			
		}

		startResendTimer(){
			var _thisObj 		= this,
				$cont 			= this.$codeForm.find('.xoo-el-code-resend'),
				$resendLink 	= $cont.find('.xoo-el-code-resend-link'),
				$timer 			= $cont.find('.xoo-el-code-resend-timer'),
				resendTime 		= parseInt( xoo_el_localize.resend_wait );

			if( resendTime === 0 ) return;

			$resendLink.addClass('xoo-el-disabled');

			clearInterval( this.resendTimer );

			this.resendTimer = setInterval(function(){
				$timer.html('('+resendTime+')');
				if( resendTime <= 0 ){
					clearInterval( _thisObj.resendTimer );
					$resendLink.removeClass('xoo-el-disabled');
					$timer.html('');
				}
				resendTime--;
			},1000) 
		}

		showNotice(notice){
			var _thisObj = this;
			clearTimeout(this.noticeTimout);
			this.$noticeCont.html( notice ).show();
			this.noticeTimout = setTimeout(function(){
				_thisObj.$noticeCont.hide();
			},4000)
		}

		onCodeSent(response){

			var _thisObj = this;

			if( response.error ) return;

			_thisObj.codesent = true;

			_thisObj.$codeForm.show();

			if( response.code_txt ){
				_thisObj.$codeForm.find('.xoo-el-code-no-txt').html( response.code_txt );
			}
			
			setTimeout(function(){
				_thisObj.$inputs.first().trigger('click');
				_thisObj.$inputs.first().focus();
				_thisObj.$inputs.first().attr('autofocus', true);
			}, 500)
			
			_thisObj.startResendTimer();


			_thisObj.parentFormValues = _thisObj.$parentForm.serialize();

			_thisObj.$codeForm.attr('data-processing','yes');
			
			_thisObj.$parentForm.hide();

		}

		verifyCode(data){

			var _thisObj = this;

			var form_data = $.extend( {
				'code': _thisObj.getCodeValue(),
				'action': 'xoo_el_code_form_submit',
				'xoo_el_code_ajax': _thisObj.codeFormID,
				'parentFormData': objectifyForm( _thisObj.$parentForm )
			}, data );


			$.ajax({
				url: xoo_el_localize.adminurl,
				type: 'POST',
				data: form_data,
				success: function(response){
					_thisObj.$submitBtn.removeClass('xoo-el-processing').html(_thisObj.submitBtnTxt);

					if( response.notice ){
						_thisObj.showNotice( response.notice );
					}

					if( response.error === 0 ){
						_thisObj.onSuccess(response);
						_thisObj.$codeForm.trigger( 'xoo_el_on_code_success', [response] );
					}
				}
			});
		}

		validateFormSubmit(){

			if( this.validateInputs() && this.getCodeValue().length ){
				this.$submitBtn.html( xoo_el_localize.html.spinner ).addClass('xoo-el-processing');
				return true;
			}
		}

		getCodeValue(){
			var code = '';
			this.$inputs.each( function( index, input ){
				code += $(input).val();
			});
			return code;
		}

  	}

  	CodeFormHandler.instances = new WeakMap();


  	$('.xoo-el-code-form').each(function( index, codeForm ){

  		new CodeFormHandler( $(codeForm) );

  	});


	window.xooEl.CodeFormHandler = CodeFormHandler; //allow other plugins to acccess this



  	if( xoo_el_localize.resetPwPattern === "code" ){

	  	class lostPasswordFormHandler{

	  		constructor( $form ){
	  			this.$form 				= $form;
	  			this.$section 			= this.$form.closest('.xoo-el-section');
	  			this.$resetPwSection 	= this.$section.siblings('[data-section="resetpw"]');
	  			this.$resetPwForm  		= this.$resetPwSection.find('.xoo-el-form-resetpw');
	  			this.$container 		= this.$section.closest('.xoo-el-form-container');  
	  			this.codeFormHandler 	= new CodeFormHandler( $form.siblings('.xoo-el-code-form') );
	  			this.events();
	  		}

	  		events(){
	  			this.$form.on( 'xoo_el_form_submitted', this.onFormSubmit.bind(this) );
	  			this.$resetPwForm.on( 'xoo_el_form_submitted', this.onResetPWFormSubmit.bind(this) );
	  			this.codeFormHandler.$codeForm.on( 'xoo_el_on_code_success', this.onCodeVerificationSuccess.bind(this) );
	  			this.$container.on( 'xoo_el_form_toggled', this.onLostPasswordFormToggle.bind(this) );
	  		}
	  	

	  		onFormSubmit( e, response, $form, container ){
	  			let _thisObj = this;
	  			_thisObj.codeFormHandler.onCodeSent(response);
	  			_thisObj.$resetPwSection.removeAttr('data-verified','yes');
	  		}

	  		onResetPWFormSubmit( e, response, $form, containerObj ){
	  			let _thisObj = this;
	  			_thisObj.$resetPwSection.removeAttr('data-verified','yes');
	  			if( response.error ){
	  				_thisObj.$section.find('.xoo-el-notice').html(response.notice).show();
	  				if( response.error_code === 'code_reset' ){
	  					containerObj.toggleForm('lostpw');
	  				}	
	  			}
	  			else{
	  				containerObj.toggleForm('login');
	  				this.$container.find('.xoo-el-section[data-section="login"]').find('.xoo-el-notice').html(response.notice).show();
	  			}
	  		}

	  		onCodeVerificationSuccess( e, response ){
	  			let _thisObj = this;
	  			_thisObj.$container.find('.xoo-el-resetpw-tgr').trigger('click');
	  			_thisObj.$resetPwSection.attr('data-verified','yes');
	  		}

	  		onLostPasswordFormToggle( e, formType, containerObj, referral  ){

	  			let _thisObj = this;

	  			if( formType !== 'lostpw'  ) return;

	  			if( _thisObj.$resetPwSection.attr('data-verified') === "yes" ){
	  				containerObj.toggleForm('resetpw');
	  			}


	  			
	  		}

	  	}

	  	$('form.xoo-el-form-lostpw').each(function(index, el){
	  		new lostPasswordFormHandler($(el));
	  	})

	}


})
