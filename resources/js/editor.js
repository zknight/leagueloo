(function() {
  $.extend(window, {
    loadEditor: function(el, url) {
      return el.tinymce({
        script_url: url,
        theme: "advanced",
        mode: "exact",
        plugins: "advimage,inlinepopups,table",
        theme_advanced_toolbar_location: "top",
        theme_advanced_statusbar_location: "bottom",
        theme_advanced_resizing: true,
        theme_advanced_buttons3_add: "tablecontrols",
        table_styles: "Info=info-table",
        file_browser_callback: 'self.browse',
        convert_urls: false
      });
    },
    browse: function(field_name, url, type, win) {
      var formURL;
      formURL = "http://" + window.location.host + $('meta[name="url_base"]').attr("content") + "content/image/image_list";
      tinyMCE.activeEditor.windowManager.open({
        file: formURL,
        title: 'Browse',
        width: 800,
        height: 800,
        resizeable: "yes",
        inline: "yes",
        close_previous: "no"
      }, {
        window: win,
        input: field_name,
        editor_id: tinyMCE.selectedInstance.editorId
      });
      return false;
    }
  });
}).call(this);
