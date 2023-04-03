(function ($, _, ts) {
  $(document).ready(function () {
    $('#CRM_Resource_DemandCondition_Attribute__operator').change(function() {
      var val = $('#CRM_Resource_DemandCondition_Attribute__operator').val();
      var isMultiple = false;
      switch (val) {
        case 'is one of':
        case 'is not one of':
        case 'contains one or more':
        case 'not contains one or more':
          $('#value_parent').addClass('hiddenElement');
          $('#multi_value_parent').removeClass('hiddenElement');
          isMultiple = true;
          break;

        case 'is empty':
        case 'is not empty':
          $('#value_parent').addClass('hiddenElement');
          $('#multi_value_parent').addClass('hiddenElement');
          isMultiple = false;
          break;

        default:
          $('#value_parent').removeClass('hiddenElement');
          $('#multi_value_parent').addClass('hiddenElement');
          isMultiple = false;
          break;
      }
    });

    function retrieveOptionsForEntityAndField(entity, field) {
      var options = new Array();
      var multiple = false;
      resource_updateOptionValues(options, multiple);
      CRM.api3(entity, 'getoptions', {'sequential': 1, 'field': field}, false)
      .done(function (data) {
        if (data.values) {
          options = data.values;
        }

        if (field.indexOf('custom_') == 0) {
          var custom_field_id = field.replace('custom_', '');
          CRM.api3('CustomField', 'getsingle', {'sequential': 1, 'id': custom_field_id}, true)
          .done(function(data) {
            switch(data.html_type) {
              case 'CheckBox':
              case 'Multi-Select':
              case 'AdvMulti-Select':
                multiple = true;
                resource_updateOptionValues(options, multiple);
                break;

              default:
                resource_updateOptionValues(options, multiple);
                break;

            }
          });
        } else {
          resource_updateOptionValues(options, multiple);
        }
      });

    }

    function resource_form_resetOptions () {
      $('#multi_value_options').html('');
      $('#value_options').html('');
      $('#multi_value_options').addClass('hiddenElement');
      $('#multi_value_parent .content.textarea').removeClass('hiddenElement');
      $('#value_options').addClass('hiddenElement');
      $('#value').removeClass('hiddenElement');
    }

    function resource_form_updateOperator (options, multiple) {
      if (!resource_form_initialOperator) {
        resource_form_initialOperator = $('#operator').val();
      }
      $('#operator option').removeClass('hiddenElement');
      if (options.length) {
          $('#operator option[value=">"').addClass('hiddenElement');
          $('#operator option[value=">="').addClass('hiddenElement');
          $('#operator option[value="<"').addClass('hiddenElement');
          $('#operator option[value="<="').addClass('hiddenElement');
          $('#operator option[value="contains string"').addClass('hiddenElement');
          $('#operator option[value="not contains string"').addClass('hiddenElement');
      }
      if (options.length && multiple) {
          $('#operator option[value="="').addClass('hiddenElement');
          $('#operator option[value="!="').addClass('hiddenElement');
          $('#operator option[value="is one of"').addClass('hiddenElement');
          $('#operator option[value="is not one of"').addClass('hiddenElement');
          $('#operator option[value="contains one or more"').addClass('hiddenElement');
          $('#operator option[value="not contains one or more"').addClass('hiddenElement');

      }
      else {

      }
      if ($('#operator option:selected').hasClass('hiddenElement')) {

          if (!$('#operator option[value="'+resource_form_initialOperator+'"]').hasClass('hiddenElement')) {
              $('#operator option[value="'+resource_form_initialOperator+'"]').prop('selected', true);
          } else {
              $('#operator option:not(.hiddenElement)').first().prop('selected', true);
          }
          $('#operator option:not(.hiddenElement)').first().prop('selected', true);
          $('#operator').trigger('change');
      }
    }

    function resource_updateOptionValues(options, multiple) {
      resource_form_resetOptions();
      resource_form_updateOperator(options, multiple);
      if (options && options.length > 0) {
        var select_options = '';
        var multi_select_options = '';

        var currentSelectedOptions = $('#CRM_Resource_DemandCondition_Attribute__multi_value').html().match(/[^\r\n]+/g);
        var currentSelectedOption = $('#CRM_Resource_DemandCondition_Attribute__value').val();
        var selectedOptions = new Array();
        var selectedOption = '';
        if (!currentSelectedOptions) {
          currentSelectedOptions = new Array();
        }

        for(var i=0; i < options.length; i++) {
          var selected = '';
          var checked = '';
          if (currentSelectedOptions.indexOf(options[i].key) >= 0 || currentSelectedOptions.indexOf(options[i].key.toString()) >= 0) {
            checked = 'checked="checked"';
            selectedOptions[selectedOptions.length] = options[i].key;
          }
          if (options[i].key == currentSelectedOption || (!currentSelectedOption && i == 0)) {
            selected='selected="selected"';
            selectedOption = options[i].key;
          }
          multi_select_options = multi_select_options + '<input type="checkbox" value="'+options[i].key+'" '+checked+'>'+options[i].value+'<br>';
          select_options = select_options + '<option value="'+options[i].key+'" '+selected+'>'+options[i].value+'</option>';
        }

        // Single value
        $('#CRM_Resource_DemandCondition_Attribute__value').val(selectedOption);
        // Debug: Comment the following line to be able to see the textfield/textarea that holds the values
        $('#CRM_Resource_DemandCondition_Attribute__value').addClass('hiddenElement');
        $('#value_options').html(select_options);
        $('#value_options').removeClass('hiddenElement');
        $('#value_options').change(function() {
          var value = $(this).val();
          $('#CRM_Resource_DemandCondition_Attribute__value').val(value);
        });

        // Multi-value
        $('#CRM_Resource_DemandCondition_Attribute__multi_value').val(selectedOptions.join('\r\n'));
        // Debug: Comment the following line to be able to see the textfield/textarea that holds the values
        $('textarea#CRM_Resource_DemandCondition_Attribute__multi_value').addClass('hiddenElement');
        $('#multi_value_options').html(multi_select_options);
        $('#multi_value_options').removeClass('hiddenElement');
        $('#multi_value_options input[type="checkbox"]').change(function() {
          var currentOptions = $('#CRM_Resource_DemandCondition_Attribute__multi_value').val().match(/[^\r\n]+/g);
          if (!currentOptions) {
            currentOptions = new Array();
          }
          var value = $(this).val();
          var index = currentOptions.indexOf(value);
          if (this.checked) {
            if (index < 0) {
              currentOptions[currentOptions.length] = value;
              $('#CRM_Resource_DemandCondition_Attribute__multi_value').val(currentOptions.join('\r\n'));
            }
          } else {
            if (index >= 0) {
              currentOptions.splice(index, 1);
              $('#CRM_Resource_DemandCondition_Attribute__multi_value').val(currentOptions.join('\r\n'));
            }
          }
        });
      } else {
        // TODO: Fill in more information here on what to hide
        $('#multi_value_parent .content.textarea').removeClass('hiddenElement');
        $('#CRM_Resource_DemandCondition_Attribute__value').removeClass('hiddenElement');
        if (!multiple) {
          $('textarea#CRM_Resource_DemandCondition_Attribute__multi_value').addClass('hiddenElement');
        }
      }
    }

    var all_fields = $('#CRM_Resource_DemandCondition_Attribute__field_name').html();
    var resource_form_initialOperator;

    $('#CRM_Resource_DemandCondition_Attribute__field_name').change(function() {
        var entity = 'Contact';
        var field = $('#CRM_Resource_DemandCondition_Attribute__field_name').val();
        var field = field.replace($('#entity').val()+'_', "");
        retrieveOptionsForEntityAndField(entity, field);
        $('#operator').trigger('change');
    });

    // Force a re-render of the changes during load/reload
    $('#entity').trigger('change');
    $('#CRM_Resource_DemandCondition_Attribute__field_name').trigger('change');
    $('#CRM_Resource_DemandCondition_Attribute__operator').trigger('change');
  });
})(CRM.$, CRM._, CRM.ts('de.systopia.resource'));
