// Easyship

var $j = jQuery.noConflict();

var Easyship = Class.create();
Easyship.prototype.postRegistration =  function (url, storeid) {

        new Ajax.Request(url, {
            parameters: {isAjax:1, method: "POST", store_id: storeid},
            onSuccess: function(transport) {
                console.log(transport);
                if (transport.status == 200) {
                    var redirectUrl = transport['responseJSON'].redirect_url;
                    window.open(redirectUrl);
                }



            },
            onFailure: function(transport) {
                alert("Error: Fail to initialize Easyship extension for store.  Please try again.");
            }
        });

    };

var easyship = new Easyship;


