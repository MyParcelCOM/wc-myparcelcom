jQuery(function ($) {
  // Make mass action open modal and cancel form submit.
  $('#doaction').click(function (e) {
    if (jQuery('#bulk-action-selector-top').val() === 'print_label_shipment') {
      jQuery('#labelModal').dialog('open')
      e.preventDefault()
    }
  })

  jQuery('#labelModal').dialog({
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
  $('#download-pdf').click(function (e) {
    var selected = []
    e.preventDefault()
    $('#loadingmessage').show()
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
    var selectOrientation = $('input[name=\'radio\']:checked').val()
    if (selectOrientation) {
      var data = {
        'action': 'myparcelcom_download_pdf',
        'selectOrientation': selectOrientation,
        'orderIds': selected,
        'labelPrinter': selectVal
      }
      jQuery.post(myparcelAdminAjaxUrl, data, function (response) {
        if (response === 'Failed') {
          $('#loadingmessage').hide()
          $('.modal-footer .alert').show().delay(5000).slideUp(500)
        } else {
          const linkSource = 'data:application/pdf;base64,' + response
          const downloadLink = document.createElement('a')
          const date = (new Date()).toISOString().replace(/\D/g, '').substr(0, 14)
          const fileName = 'myparcelcom-label-' + date + '.pdf'
          downloadLink.href = linkSource
          downloadLink.download = fileName
          downloadLink.click()
          $('#loadingmessage').hide()
          $('#labelModal').dialog('close')
        }
      })
    }
    return false
  })
})
