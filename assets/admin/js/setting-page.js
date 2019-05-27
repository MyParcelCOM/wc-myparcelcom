jQuery(function($){

    $("form#api-setting-form").validate({
        rules: {

            api_url: {
                required: true
            },
            api_auth_url: {
                required: true
            },
            client_key: {
                required: true
            },
            client_secret_key: {
                required: true
            },
            street1: {
                required: true
            },
            street_number: {
                required: true
            },
            city: {
                required: true
            },
            postal_code: {
                required: true
            },
            country_code: {
                required: true
            },
            phone_number: {
                required: true
            },
            company_name: {
                required: true
            }
             
       },
       messages: {
        
            api_url: {
                required: "Required"
            },
            api_auth_url: {
                required: "Required"
            },
            client_key: {
                required: "Required"
            },
            client_secret_key: {
                required: "Required"
            },
            street1: {
                required: "Required"
            },
            street_number: {
                required: "Required"
            },
            city: {
                required: "Required"
            },
            postal_code: {
                required: "Required"
            },
            country_code: {
                required: "Required"
            },
            phone_number: {
                required: "Required"
            },
            company_name: {
                required: "Required"
            }
            
       },
           
       // the errorPlacement has to take the table layout into account
        errorPlacement: function(error, element) {
            error.css('color','red');
            if ( element.is(":radio") )
                error.appendTo( element.parent().next().next() );
            else if ( element.is(":checkbox") )
                error.appendTo ( element.next() );
            else
                error.appendTo( element.parent() );
        },
    
    });

});