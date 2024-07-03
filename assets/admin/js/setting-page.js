jQuery(function ($) {
  $('#myparcelcom-settings-form').validate({
    rules: {
      myparcelcom_client_id: {
        required: true
      },
      myparcelcom_client_secret: {
        required: true
      },
      myparcelcom_shop_id: {
        required: true
      }
    },
    messages: {
      myparcelcom_client_id: {
        required: 'Required'
      },
      myparcelcom_client_secret: {
        required: 'Required'
      },
      myparcelcom_shop_id: {
        required: 'Required'
      }
    },

    // the errorPlacement has to take the table layout into account
    errorPlacement: function (error, element) {
      error.css('color', 'red')
      if (element.is(':radio'))
        error.appendTo(element.parent().next().next())
      else if (element.is(':checkbox'))
        error.appendTo(element.next())
      else
        error.appendTo(element.parent())
    },
  })

  const shopSelect = $('#myparcelcom_shop_id')

  function resetShopList () {
    const id = $('#myparcelcom_client_id').val()
    const secret = $('#myparcelcom_client_secret').val()
    const testmode = $('#myparcelcom_test_mode').prop('checked') ? '1' : '0'

    if (id && secret) {
      shopSelect.empty().append($('<option>', {
        value: '',
        text: 'Loading...'
      }))

      const data = {
        action: 'myparcelcom_get_shops_for_client',
        myparcelcom_client_id: id,
        myparcelcom_client_secret: secret,
        myparcelcom_test_mode: testmode
      }
      jQuery.post(ajaxurl, data, function (response) {
        const shops = JSON.parse(response)

        if (shops.length === 0) {
          shopSelect.empty().append($('<option>', {
            value: '',
            text: 'No shops available for this ' + (testmode === '1' ? 'sandbox' : 'production') + ' client'
          }))
        } else {
          shopSelect.empty().append($('<option>', {
            value: '',
            text: 'Please select a shop...'
          }))
          $.each(shops, function (index, shop) {
            shopSelect.append($('<option>', {
              value: shop.id,
              text: shop.name
            }))
          })
          shopSelect.val(initialShop)
        }
      })
    }
  }

  $('#myparcelcom_test_mode, #myparcelcom_client_id, #myparcelcom_client_secret').change(function () {
    resetShopList()
  })
  resetShopList()
})
