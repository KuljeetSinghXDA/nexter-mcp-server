
"use strict";
document.addEventListener("DOMContentLoaded", (event) => {

    //Woocommerce Custom Color Picker
    var colorPickers = document.querySelectorAll('.tpgb-color-picker');
    if (colorPickers.length > 0) {
        colorPickers.forEach(function(colorPicker) {
            var input = colorPicker.querySelector('input.tpgb-color-picker');
            if (input) {
                // Assuming wpColorPicker is a global function
                wpColorPicker(input);
            }
        });
    }


    //Woocommerce Custom Image Uploader
    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('button.tpgb_upload_image_button')) {
            e.preventDefault();
            e.stopPropagation();

            var file_frame;

            if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                // If the media frame already exists, reopen it.
                if (file_frame) {
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.select_image = wp.media({
                    title: 'Choose an Image',
                    button: {
                        text: 'Use Image'
                    },
                    multiple: false
                });

                // When an image is selected, run a callback.
                file_frame.on('select', function() {
                    var attachment = file_frame.state().get('selection').first().toJSON();

                    if (attachment.id.trim() !== '') {
                        var url = typeof attachment.sizes.thumbnail === 'undefined' ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;

                        e.target.previousElementSibling.value = attachment.id;
                        e.target.closest('.tpgb-meta-image-field-wrapper').querySelector('img').src = url;
                        e.target.nextElementSibling.style.display = 'block';
                    }
                    //file_frame.close();
                });

                // When open select selected
                file_frame.on('open', function() {
                    // Grab our attachment selection and construct a JSON representation of the model.
                    var selection = file_frame.state().get('selection');
                    var current = e.target.previousElementSibling.value;
                    var attachment = wp.media.attachment(current);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });

                // Finally, open the modal.
                file_frame.open();
            }
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('button.tpgb_remove_image_button')) {
            e.preventDefault();
            e.stopPropagation();
            
            var wrapper = e.target.closest('.tpgb-meta-image-field-wrapper');
            var img = wrapper.querySelector('img');
            var placeholder = img.dataset.placeholder;
            img.src = placeholder;
            
            var input = wrapper.querySelector('input[type="hidden"]');
            if (input) {
                input.value = '';
            }
            
            e.target.style.display = 'none';
            
            return false;
        }
    });


})