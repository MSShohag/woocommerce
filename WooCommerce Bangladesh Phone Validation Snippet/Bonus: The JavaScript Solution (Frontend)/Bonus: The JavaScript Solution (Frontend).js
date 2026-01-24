jQuery(document).ready(function($) {
    $('#billing_phone').on('blur', function() {
        var phone = $(this).val();
        
        // Remove non-digits
        var cleanPhone = phone.replace(/\D/g, '');

        // Remove leading 88 if present
        if (cleanPhone.startsWith('88')) {
            cleanPhone = cleanPhone.substring(2);
        }

        // Update the field value
        $(this).val(cleanPhone);
    });
});
