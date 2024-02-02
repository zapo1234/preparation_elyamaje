$(document).ready(function() {

    const inputElement = document.getElementById('new_password');
	const inputElement2 = document.getElementById('new_password2');
	const error = document.getElementsByClassName('same_password');

    inputElement.addEventListener('input', (event) => {
        checkPassword()
	})

    inputElement2.addEventListener('input', (event) => {
        checkPassword()
	})

    $(".avatar").on('click', function(){
        updateProfilImage($(this).attr('data-value'), $(this).attr('src'), true)
    })


    $('#editProfilImage').on('shown.bs.modal', function () {
        updateProfilImage($("#display_image_data").attr('data-value'), $("#display_image_data").attr('src'))
    });

   
    $("#upload_file").on('click', function(){
        $("#browse_image").click()
    })


    $("body").on("change", "#browse_image", function(e) {
        var files = e.target.files;
        var done = function(url) {
            $('#display_image_div').html('');
            $("#display_image_div").html(`<img name="display_image_data" id="display_image_data" src="`+url+`" alt="Uploaded Picture">`)
        };

        if (files && files.length > 0) {
            var file = files[0];
            if (URL) {
                done(URL.createObjectURL(file));
            
            } else if (FileReader) {
                reader = new FileReader();
                reader.onload = function(e) {
                    done(reader.result);
                };
                reader.readAsDataURL(file);
            }
        }
        var image = document.getElementById('display_image_data');
        var button = document.getElementById('crop_button');
        var result = document.getElementById('cropped_image_data');
        var croppable = false;
        var cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            ready: function() {
                croppable = true;
            },
        });
        button.onclick = function() {
            var croppedCanvas;
            var roundedCanvas;
            var roundedImage;
            if (!croppable) {
                return;
            }
            // Crop
            croppedCanvas = cropper.getCroppedCanvas();
            // Round
            roundedCanvas = getRoundedCanvas(croppedCanvas);
            result.value = roundedCanvas.toDataURL()
            $("#crop_button").attr('disabled', true)
            $(".updateImageProfil").submit()
        };
    });

    function getRoundedCanvas(sourceCanvas) {
        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d');
        var width = sourceCanvas.width;
        var height = sourceCanvas.height;
        canvas.width = width;
        canvas.height = height;
        context.imageSmoothingEnabled = true;
        context.drawImage(sourceCanvas, 0, 0, width, height);
        context.globalCompositeOperation = 'destination-in';
        context.beginPath();
        context.arc(width / 2, height / 2, Math.min(width, height) / 2, 0, 2 * Math.PI, true);
        context.fill();
        return canvas;
    }

    function checkPassword(){
        error.innerText=""

        if(inputElement.value.length == 0 && inputElement2.value.length == 0){
            $(".pass").removeClass('text-success')
            $(".pass").removeClass('text-danger')
        } else {
            if(inputElement.value != inputElement2.value){
                $(".pass").addClass('text-danger')
                $(".pass").removeClass('text-success')
            } else {
                $(".pass").removeClass('text-danger')
                $(".pass").addClass('text-success')
            }
        }	
    }


    function updateProfilImage(value, src, load = false){

        var files = new File([""], value, {lastModified: Date(), size: 0, type: "image/jpeg"})
        var done = function(url) {
            $('#display_image_div').html('');
            $("#display_image_div").html('<img name="display_image_data" id="display_image_data" src="'+src+'" alt="Uploaded Picture">')
        }
        if (files && files.length > 0 || (files && load)) {
            var file = files;
            if (URL) {
                done(URL.createObjectURL(file));
            } else if (FileReader) {
                reader = new FileReader();
                reader.onload = function(e) {
                    done(reader.result);
                };
                reader.readAsDataURL(file);
            }
        }
        var image = document.getElementById('display_image_data');
        var button = document.getElementById('crop_button');
        var result = document.getElementById('cropped_image_data');
        var croppable = false;
        var cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            ready: function() {
                croppable = true;
            },
        });

        button.onclick = function() {

            var croppedCanvas;
            var roundedCanvas;
            var roundedImage;
            if (!croppable) {
                return;
            }
            // Crop
            croppedCanvas = cropper.getCroppedCanvas();
            // Round
            roundedCanvas = getRoundedCanvas(croppedCanvas);
            result.value = roundedCanvas.toDataURL()
            $("#crop_button").attr('disabled', true)
            $(".updateImageProfil").submit()
        };
    }
})
