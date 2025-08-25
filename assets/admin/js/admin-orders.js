jQuery(function ($) {
  // Make mass action open modal and cancel form submit.
  $('#doaction').click(function (e) {
    if (jQuery('#bulk-action-selector-top').val() === 'print_label_shipment') {
      labelModalDialog.dialog('open')
      e.preventDefault()
    }
  })

  var labelModalDialog = jQuery('#labelModal').dialog({
    autoOpen: false,
    closeText: '',
    modal: true,
    title: 'Label position',
    width: 400,
  })

  var selectVal = $('#printer-orientation input[name=\'selectorientation\']:checked').val()
  $('#printer-orientation input[name=\'selectorientation\']').click(function () {
    selectVal = $(this).val()
    $('div.cntnr').hide()
    $('#orientation' + selectVal).show()
  })
  labelModalDialog.find('#download-pdf').click(function (e) {
    var selected = []
    e.preventDefault()
    $('#loadingmessage').show()

    // Retrieve selected orders (non-HPOS)
    $('.wp-list-table #the-list tr input[name=\'post[]\']:checked').map(function () {
      if ($('.wp-list-table #the-list tr input[name=\'post[]\']').is(':checked')) {
        var idx = $.inArray($(this).val(), selected)
        if (idx == -1) {
          selected.push($(this).val())
        }
      } else {
        selected.splice($(this).val())
      }
    })

    // Retrieve selected orders (HPOS)
    $('.wp-list-table #the-list tr input[name=\'id[]\']:checked').map(function (index, element) {
      selected.push(element.value)
    })

    var selectOrientation = $('input[name=\'radio\']:checked').val()
    if (selectOrientation) {
      var data = {
        'action': 'myparcelcom_download_pdf',
        'selectOrientation': selectOrientation,
        'orderIds': selected,
        'labelPrinter': selectVal
      }
      jQuery.post(ajaxurl, data, function (response) {
        if (response.startsWith('Error: ')) {
          $('#loadingmessage').hide()
          $('.modal-footer .alert p').text(response)
          $('.modal-footer .alert').show().delay(10000).slideUp(500)
        } else {
          const linkSource = 'data:application/pdf;base64,' + response
          const downloadLink = document.createElement('a')
          const date = (new Date()).toISOString().replace(/\D/g, '').substr(0, 14)
          const fileName = 'myparcelcom-label-' + date + '.pdf'
          downloadLink.href = linkSource
          downloadLink.download = fileName
          downloadLink.click()
          $('#loadingmessage').hide()
          labelModalDialog.dialog('close')
        }
      })
    }
    return false
  })
})
