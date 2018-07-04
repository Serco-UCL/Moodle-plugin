$(function() {
  $('#id_collection').hide();
  $('#id_collectiontype').hide();
  $('#id_userid').hide();
  
  if($('#id_serverid option').length == 1) {
    $('#id_serverid').parent().append('<span id="serveronly" style="padding-left:10px;"></span>');
    $('#serveronly').html($('#id_serverid').text());
    $('#id_serverid').hide();
  }  
  
  $('#id_collectiontype2').parent().append('<span id="collectiontypedesc" style="padding-left:10px;"></span>');
  $('#id_collection2').parent().append('<span id="collectiondesc" style="padding-left:10px;"></span>');
  $('#id_serverid').parent().append('<span id="serverdesc" style="padding-left:10px;"></span>');
 
  $('#id_serverid').parent().parent().css('margin-top','40px');
  
  $('<iframe src="type/serco/servicetest.php?userid=' + $('#id_userid').val() + '" width="100%" height="200" style="margin-top:15px;"></iframe>').appendTo('#id_generalheader');

  /*************************************************************************************************************/
  function serco_init_collectiontype() {
      var jqxhr = $.getJSON({url:'type/serco/service.php?action=querycollectiontype&serverid='+$('#id_serverid').val(),async: false}, function() { 
        }).done(function(result) {
          for (var prop in result) {
            if(!result.hasOwnProperty(prop)) continue;
            $('#id_collectiontype2').append($('<option>', {  
              value: result[prop].ref,
              text : result[prop].name,
              description : result[prop].description
            }));
          }
          $('#id_collectiontype2').val($('#id_collectiontype').val());
          $('#collectiontypedesc').html($('#id_collectiontype2').find(":selected").attr("description"));          
        }).fail(function() {
        }).always(function() {
        });
  }  
 /*************************************************************************************************************/
  function serco_init_collection() {
      if ($('#id_collectiontype2').val() == null) return;
      var jqxhr = $.getJSON({url:'type/serco/service.php?action=querycollection&serverid='+$('#id_serverid').val()+'&collectiontype='+$('#id_collectiontype2').val(),async: false}, function() { 
        }).done(function(result) {

          $('#id_collection2').append($('<option>', {  
            value: '',
            text : ''
          }));

          var subarrayLength = result.collections.length;
          for (var j = 0; j < subarrayLength; j++) {
            $('#id_collection2').append($('<option>', {  
              value: result.collections[j].ref,
              text : result.collections[j].name,
              description : result.collections[j].description
            }));
          }
          $('#id_collection2').val($('#id_collection').val());
          if($('#id_collection').val() == '') {
            $('#id_collection').val(result.defaultColl);
            $('#id_collection2').val($('#id_collection').val());
          }
          $('#collectiondesc').html($('#id_collection2').find(":selected").attr("description"));
        }).fail(function() {
        }).always(function() {
        });
  }
  /*************************************************************************************************************/
  serco_init_collectiontype();
  serco_init_collection();
  
  //$('#id_collection').parent().parent().hide();
  
  /*************************************************************************************************************/
  $('#id_serverid').on('change', function() {
   $('#id_collectiontype2').empty();
   $('#id_collection2').empty();
   $('#id_collectiontype').val('');
   $('#id_collection').val('');
   $('#collectiontypedesc').html('');
   $('#collectiondesc').html('');
   serco_init_collectiontype();
  });  
  /*************************************************************************************************************/
  $('#id_collectiontype2').on('change', function() {
    $('#id_collectiontype').val(this.value);
    $('#collectiontypedesc').html($(this).find(":selected").attr("description"));
    $('#collectiondesc').html('');
    $('#id_collection2').empty();
    $('#id_collection').val('');
    serco_init_collection();
  });  
  /*************************************************************************************************************/
  $('#id_collection2').on('change', function() {
    $('#id_collection').val(this.value );
    $('#collectiondesc').html($(this).find(":selected").attr("description"));
  });
  /*************************************************************************************************************/
});  