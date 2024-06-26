jQuery(function ($) {
  $('#myparcelcom-settings-form').validate({
    rules: {
      client_key: {
        required: true
      },
      client_secret_key: {
        required: true
      },
      myparcel_shopid: {
        required: true
      }
    },
    messages: {
      client_key: {
        required: 'Required'
      },
      client_secret_key: {
        required: 'Required'
      },
      myparcel_shopid: {
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

  const shopSelect = $('#myparcel_shopid')

  function resetShopList () {
    const id = $('#client_key').val()
    const secret = $('#client_secret_key').val()
    const testmode = $('#act_test_mode').prop('checked') ? '1' : '0'

    if (id && secret) {
      shopSelect.empty().append($('<option>', {
        value: '',
        text: 'Loading...'
      }))

      const data = {
        action: 'myparcelcom_get_shops_for_client',
        client_key: id,
        client_secret_key: secret,
        act_test_mode: testmode
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

  $('#act_test_mode, #client_key, #client_secret_key').change(function () {
    resetShopList()
  })
  resetShopList()
})
