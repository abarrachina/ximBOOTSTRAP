X.actionLoaded(function (event, fn, params){
  fn("input.input_colorpicker").ColorPicker(
  {
    onSubmit: function(hsb, hex, rgb, el) {
                $(el).val(hex);
                $(this).ColorPicker("hide");
              },

    onChange : function(hsb, hex, rgb){
                var el = $(this).data("colorpicker").el;
                var actionContainer = $(el).parentsUntil(".action_container");

                $(el).val("#"+hex);
                switch ($(el).attr("name")){
                  case "principal_color":
                          $("div.bsPreviewContainer", actionContainer).css("background-color","#"+hex);
                          break;
                  case "secundary_color":
                          $("div.bsPreviewTitle", actionContainer).css("background-color","#"+hex);
                          break;
                  case "font_color":
                          $("div.bsPreviewContent", actionContainer).css("color","#"+hex);
                          break;

                }
              }

  }).bind('keyup', function(){
        $(this).ColorPickerSetColor(this.value);
  });

  fn("input#title").bind("keyup", function(){

    var texto = $(this).val();
    var actionContainer = $(this).parentsUntil(".action_container");
    $("div.bsPreviewTitle h4", actionContainer).text(texto);


  });
  });
