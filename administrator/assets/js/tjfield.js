jQuery(document).ready(function() {

    jQuery(".btn-delete").click(function() {
        let field_id = jQuery(this).attr("id");
        let fileName = jQuery("#fileName_" + field_id).val();

        let commentContainer = jQuery(this).parent().parent().parent().parent();

        if (confirm("Are you sure to remove the uploaded document?") == false) {
            return false;
        }

        jQuery.ajax({
            url: '?option=com_tjucm&task=itemform.delete_doc',
            type: 'post',
            data: {
                field_id: field_id,
                fileName: fileName
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.error) {
                    jQuery("#message").html(resp.message);
                    jQuery("#message").addClass('alert alert-danger');
                } else {
                    jQuery("#uploadedreceipt_" + field_id).hide();
                }
            },
            error: function(resp) {}
        });
        return true;
    });
});
