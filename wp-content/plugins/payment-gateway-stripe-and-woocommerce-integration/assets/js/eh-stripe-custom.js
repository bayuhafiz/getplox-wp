
jQuery(function() { 
    jQuery('#doaction').prop('disabled', 'disabled');
    jQuery('#the-list').on('click', '.complete_button', function() {
       var id=jQuery( this ).attr('id');
       var data = {
                    paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                };
       jQuery("#order_section .loader").css("display", "block");
       jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: jQuery.extend({
                _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                action: 'eh_order_status_update',
                order_id: id,
                order_action : 'completed'
            },data),
            success: function(response) {
                get_all_orders_js();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    jQuery('#the-list').on('click', '.processing_button', function() {
       var id=jQuery( this ).attr('id');
       var data = {
                    paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                };
       jQuery("#order_section .loader").css("display", "block");
       jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: jQuery.extend({
                _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                action: 'eh_order_status_update',
                order_id: id,
                order_action : 'processing'
            },data),
            success: function(response) {
                get_all_orders_js();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    jQuery('#wrap_table').on('click', '#doaction', function() {
        var ids=get_bulk_ids();
        var action=jQuery('#bulk-action-selector-top').val();
        var data = {
                    paged: parseInt(jQuery('input[name=paged]').val()) || '1'
                };
        jQuery("#order_section .loader").css("display", "block");
        jQuery.ajax({
             type: 'post',
             url: ajaxurl,
             data: jQuery.extend({
                 _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                 action: 'eh_order_status_update',
                 order_id: ids,
                 order_action : action
             },data),
             success: function(response) {
                 get_all_orders_js();
             },
             error: function(jqXHR, textStatus, errorThrown) {
                 console.log(textStatus, errorThrown);
             }
         });
    });
    jQuery( '.tablenav' ).on( 'change','#bulk-action-selector-top', function() 
    {
        var value=jQuery('#bulk-action-selector-top').val();
        if(value==='-1')
        {
            jQuery('#doaction').prop('disabled', 'disabled');
        }
        else
        {
            jQuery('#doaction').removeAttr('disabled');
        }
    }).change();
    jQuery('#wrap_table').on('click', '#save_dislay_count_order', function() {
        jQuery('#save_dislay_count_order').prop('disabled', 'disabled');
        var row_count=jQuery('#display_count_order').val();
        jQuery.ajax({
             type: 'post',
             url: ajaxurl,
             data: {
                 _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                 action: 'eh_order_display_count',
                 row_count:row_count
             },
             success: function(response) {
                 get_all_orders_js();
                 jQuery('#save_dislay_count_order').removeAttr('disabled');
             },
             error: function(jqXHR, textStatus, errorThrown) {
                 console.log(textStatus, errorThrown);
             }
         });
    });
    jQuery('#wrap_table').on('click', '#save_dislay_count_stripe', function() {
        jQuery('#save_dislay_count_stripe').prop('disabled', 'disabled');
        var row_count=jQuery('#display_count_stripe').val();
        jQuery.ajax({
             type: 'post',
             url: ajaxurl,
             data: {
                 _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                 action: 'eh_stripe_display_count',
                 row_count:row_count
             },
             success: function(response) {
                 get_all_stripe_js();
                 jQuery('#save_dislay_count_stripe').removeAttr('disabled');
             },
             error: function(jqXHR, textStatus, errorThrown) {
                 console.log(textStatus, errorThrown);
             }
         });
    });
    function get_bulk_ids() {
        var chkArray = [];
        jQuery('input[name="orders[]"]:checked').each(function() {
            chkArray.push(jQuery(this).val());
        });
        var selected;
        selected = chkArray.join(',') + ",";
        if (selected.length > 1) {
            return (selected.slice(0, -1));
        } else {
            return ('');
        }
    }
    function get_all_orders_js() {
        jQuery("#order_section .loader").css("display", "block");
        var data = {
                    paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                };
        jQuery.ajax({
            url: ajaxurl,
            data: jQuery.extend({
                _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                action: 'eh_spg_get_all_order',
            }, data),
            success: function(response) {
                jQuery("#order_section .loader").css("display", "none");
                var response = jQuery.parseJSON(response);
                 if (response.rows.length)
                    jQuery('#the-list').html(response.rows);
                if (response.column_headers.length)
                    jQuery('thead tr, tfoot tr').html(response.column_headers);
                if (response.pagination.bottom.length)
                    jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
                if (response.pagination.top.length)
                    jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());
                list.init();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
    function get_all_stripe_js() {
        jQuery("#stripe_section .loader").css("display", "block");
        var data = {
                    paged: parseInt(jQuery('input[name=paged]').val()) || '1',
                };
        jQuery.ajax({
            url: ajaxurl,
            data: jQuery.extend({
                _ajax_eh_spg_nonce: jQuery('#_ajax_eh_spg_nonce').val(),
                action: 'eh_spg_get_all_stripe',
            }, data),
            success: function(response) {
                jQuery("#stripe_section .loader").css("display", "none");
                var response = jQuery.parseJSON(response);
                 if (response.rows.length)
                    jQuery('#the-list').html(response.rows);
                if (response.column_headers.length)
                    jQuery('thead tr, tfoot tr').html(response.column_headers);
                if (response.pagination.bottom.length)
                    jQuery('.tablenav.top .tablenav-pages').html(jQuery(response.pagination.top).html());
                if (response.pagination.top.length)
                    jQuery('.tablenav.bottom .tablenav-pages').html(jQuery(response.pagination.bottom).html());
                list.init();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
});
jQuery(document).ready(function() {
    jQuery('table.wp-list-table').tableSearch();
});
(function(jQuery) {
    jQuery.fn.tableSearch = function(options) {
        if (!jQuery(this).is('table')) {
            return;
        }
        var tableObj = jQuery(this),
            inputObj = jQuery('#search_id-search-input');
        inputObj.off('keyup').on('keyup', function() {
            var searchFieldVal = jQuery(this).val();
            tableObj.find('tbody tr').hide().each(function() {
                var currentRow = jQuery(this);
                currentRow.find('td').each(function() {
                    if (jQuery(this).html().indexOf(searchFieldVal) > -1) {
                        currentRow.show();
                        return false;
                    }
                });
            });
        });
    }
}(jQuery));
