function initTinymce(selector) {
    tinymce.init({
        element_format : "html",
        selector: selector,
        fix_list_elements : true,
        height : 600,
        menubar: false,
        entity_encoding : 'raw',
        plugins: 'paste,table,nonbreaking,link,code,image,contextmenu',
        contextmenu: "link image inserttable | cell row column deletetable",
        paste_as_text: true,
        relative_urls: false,
        skin_url: '/components/tinymce/skins/typicms',
        file_browser_callback: function(field_name, url, type, win) {
        	// Help : http://www.tinymce.com/forum/viewtopic.php?id=30861&p=2
            tinymce.activeEditor.windowManager.open({
                title: 'Choose image',
                url: '/admin/files?type=i&view=filepicker',
                width: 835,
                height: 550
            }, {
                oninsert: function(url) {
                	fieldElm = win.document.getElementById(field_name);
                    fieldElm.value = url;
                    if ("createEvent" in document) {
                        var evt = document.createEvent("HTMLEvents");
                        evt.initEvent("change", false, true);
                        fieldElm.dispatchEvent(evt);
                    } else {
                        fieldElm.fireEvent("onchange");
                    }
                }
            });
        },
        // statusbar: false,
        block_formats : "Paragraph=p;Code=pre;Blockquote=blockquote;Header 2=h2;Header 3=h3;Header 4=h4;Header 5=h5;Header 6=h6",
        style_formats : [
            { title : 'Small text', inline: 'small' },
            { title : 'Image Left', selector : 'img', styles : { 'float': 'left', 'margin': '0 20px 20px 0' } },
            { title : 'Image Right', selector : 'img', styles : { 'float': 'right', 'margin': '0 0 20px 20px' } },
            { title : 'File (link)', selector : 'a', classes : 'file' },
            { title : 'Button (link)', selector : 'a', classes : 'btn btn-default' },
        ],
        content_css : '/css/public.css,/components/tinymce/css/tiny_mce.css',
        toolbar: 'formatselect | styleselect | bold italic | subscript superscript | bullist numlist outdent indent | link unlink | alignleft aligncenter alignright alignjustify | table | nonbreaking | image | code | removeformat',
        language_url: '/components/tinymce/langs/fr.js'
    });
}

!function( $ ){

    "use strict";

    $(function () {

        /**
         * Slug fields
         */
        for (var i = 0; i < langues.length; i++) {
            var titleField = $('#' + langues[i] + '\\[title\\]');
            titleField.slug({
                slugField: '#' + langues[i] + '\\[slug\\]'
            });
        };
        var titleField = $('#title');
        titleField.slug({
            slugField: '#slug'
        });

        /**
         * TinyMCE on .editor items
         */
        if ($('.editor').length) {
            initTinymce('.editor');
        }

        /**
         * Selectize for tags
         */
        if ($('#tags').length) {
            $.getJSON("/admin/tags", function(data){
                var items = data.map(function(x) { return { item: x }; });
                $('#tags').selectize({
                    persist: false,
                    create: true,
                    delimiter: ', ',
                    options: items,
                    labelField: 'item',
                    valueField: 'item',
                    createOnBlur: true
                });
            })
        }

        /**
         * Selectize for galleries
         */
        $('select#galleries').selectize({
            plugins: ['drag_drop'],
            createOnBlur: true
        });

        /**
         * Set button in red on validation errors
         */
        var firstErrorTabActive = false;
        $('.tab-pane').each(function(index, el) {
            if ($(this).find('.has-error').length) {
                var tabButton = $('a[data-target="#' + $(this).attr('id') + '"]');
                if ( ! firstErrorTabActive) {
                    tabButton.tab('show');
                    firstErrorTabActive = true;
                }
                var dangerClass = 'text-danger';
                if (tabButton.hasClass('btn')) {
                    dangerClass = 'btn-danger';
                }
                tabButton.addClass(dangerClass);
            };
        });

        /**
         * Locale switcher : set active button
         */
        $('#btn-group-form-locales .btn').click(function(){
            $(this).parent().children('.active').removeClass('active');
            $(this).addClass('active');
        });

        /**
         * Date picker
         */
        if ($('.datepicker').length) {
            $('.datepicker').pickadate({
                editable: true,
                // formatSubmit: 'yyyy-mm-dd',
                format: 'dd.mm.yyyy'
            });
        }

        /**
         * Time picker
         */
        if ($('.timepicker').length) {
            $('.timepicker').pickatime({
                editable: true,
                format: 'HH:i'
            });
        }

    });

}( window.jQuery || window.ender );
