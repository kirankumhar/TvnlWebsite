const maxFiles = MAX_ALLOWED_FILES;
const allowedExtensions = ['jpg', 'jpeg', 'png'];
const maxFileSizeKB = 400;

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let originalData = {}; // Store original values
let hasChanges = false;

// Initialize the original data when page loads
$(document).ready(function () {
    // Store initial values of captions and cover photo
    $('.photo-row').each(function () {
        const photoId = $(this).attr('id');
        const caption = $(this).find('textarea[name="captionEn"]').val();
        const isCover = $(this).find('input[type="radio"]').prop('checked');

        originalData[photoId] = {
            caption: caption,
            isCover: isCover,
            position: $(this).find('input[placeholder="Position"]').val()
        };
    });

    // Check for changes in caption
    $('textarea[name="captionEn"]').on('input', checkForChanges);

    // Check for changes in cover photo
    $('input[name="coverPhoto"]').on('change', checkForChanges);

    $('input[name="replacePic"]').on('change', function () {
        const imgElement = $(this).closest('.photo-row').find('img.preview-image');
        const imgId = imgElement.attr('id');
        hasChanges = true;
        updateSaveButton();
        if (imgId) {
            previewImage(this, imgId);
        }
    });

    // Check for changes in position
    $('input[placeholder="Position"]').on('change', function () {

        const newPosition = parseInt($(this).val()) || 1;
        const currentItem = $(this).closest('.col-md-4');

        if (newPosition < 1) {
            $(this).val(1);
            return;
        }

        // Get all photo items
        const allItems = $('.photo-row').closest('.col-md-4').toArray();
        const container = currentItem.parent();

        // Remove the current item from flow temporarily
        currentItem.detach();

        // Insert at new position
        if (newPosition <= 1) {
            // Insert at beginning
            container.prepend(currentItem);
        } else if (newPosition >= allItems.length) {
            // Insert at end
            container.append(currentItem);
        } else {
            // Insert at specified position (accounting for 1-based indexing)
            $(allItems[newPosition - 1]).before(currentItem);
        }

        checkForChanges();
        updatePositions();
    });

    // Initially hide save button
    $("#saveBtn").prop('disabled', true).hide();
});

// Check if any data has changed
function checkForChanges() {
    hasChanges = false;

    $('.photo-row').each(function () {
        const photoId = $(this).attr('id');
        const currentCaption = $(this).find('textarea[name="captionEn"]').val();
        const currentIsCover = $(this).find('input[type="radio"]').prop('checked');
        const currentPosition = $(this).find('input[placeholder="Position"]').val();

        if (
            currentCaption !== originalData[photoId].caption ||
            currentIsCover !== originalData[photoId].isCover ||
            currentPosition !== originalData[photoId].position
        ) {
            hasChanges = true;
            return false; // exit the each loop
        }
    });

    updateSaveButton();
}

function updatePositions() {
    $('.photo-row').closest('.col-md-4').each(function (index) {
        $(this).find('input[placeholder="Position"]').val(index + 1);
    });
}
function initOriginalData() {
    $('.photo-row').each(function () {
        const photoId = $(this).attr('id');
        const caption = $(this).find('textarea[name="captionEn"]').val();
        const isCover = $(this).find('input[type="radio"]').prop('checked');
        const position = $(this).closest('.col-md-4').index() + 1;

        originalData[photoId] = {
            caption: caption,
            isCover: isCover,
            position: position
        };
    });
}

// 3. Update save button text and visibility based on conditions
function updateSaveButton() {
    const photosData = $('.photo-row').length;
    const newPhotos = $('#picture2')[0]?.files?.length || 0;

    if (photosData === 0 && newPhotos === 0) {
        $("#saveBtn").prop('disabled', true).hide();
    } else if (photosData !== 0 && newPhotos !== 0) {
        $("#saveBtn").prop('disabled', false).show().text('Update & Add Photos');
    } else if (photosData !== 0 && newPhotos === 0) {
        if (hasChanges) {
            $("#saveBtn").prop('disabled', false).show().text('Update Photos Details');
        } else {
            $("#saveBtn").prop('disabled', true).hide();
        }
    } else {
        $("#saveBtn").prop('disabled', false).show().text('Add Photos');
    }
}

// Listen for file input changes
$('#picture2').on('change', function () {
    updateSaveButton();
});

// Function to save changes
// function saveChanges() {
//     // Collect all photo data
//     const photos = [];
//     $('.photo-row').each(function (index) {
//         const photoId = $(this).attr('id').split('-')[1];
//         const position = index + 1;
//         const caption = $(this).find('textarea[name="captionEn"]').val();
//         const isCover = $(this).find('input[type="radio"]').prop('checked');
//         photos.push({
//             id: photoId,
//             position: position,
//             caption_en: caption,
//             is_cover: isCover ? 1 : 0
//         });
//     });
//     // Create FormData with existing photos data and new photos
//     const formData = new FormData();
//     formData.append('photosData', JSON.stringify(photos)); // Encode as JSON string
//     formData.append('action', 'editPhotos'); // Important: Add action parameter
//     formData.append('csrf_token', csrfToken); // Send CSRF token

//     // Add new photos if any
//     const fileInput = document.getElementById('picture2');
//     if (fileInput && fileInput.files.length > 0) {
//         for (let i = 0; i < fileInput.files.length; i++) {
//             formData.append('newPhotos[]', fileInput.files[i]);
//         }
//     }

//     // Get album ID from URL
//     const albumId = new URLSearchParams(window.location.search).get("album_id");
//     formData.append('album_id', albumId);

//     $("#loader").show();
//     // Submit the data
//     $.ajax({
//         url: "src/controllers/gallery/album_api.php",
//         type: 'POST',
//         data: formData,
//         processData: false,
//         contentType: false,
//         success: function (response) {
//             location.reload();
//             const messageTemplate = `
//           <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
//               <strong>Photos updated successfully!</strong>
//               <button type="button" class="close" data-dismiss="alert" aria-label="Close">
//                   <span aria-hidden="true">&times;</span>
//               </button>
//           </div>`;
//             $("#msg").html(messageTemplate);
//             $('html, body').animate({ scrollTop: 0 }, 'fast');
//         },
//         error: function (xhr, status, error) {
//             $("#loader").hide();
//             const messageTemplate = `
//           <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
//               <strong>Error updating photos: ${error}</strong>
//               <button type="button" class="close" data-dismiss="alert" aria-label="Close">
//                   <span aria-hidden="true">&times;</span>
//               </button>
//           </div>`;
//             $("#msg").html(messageTemplate);
//             $('html, body').animate({ scrollTop: 0 }, 'fast');
//         }
//     });
// }

function saveChanges() {
    // Collect all photo data
    const photos = [];
    $('.photo-row').each(function (index) {
        const photoId = $(this).attr('id').split('-')[1];
        const position = index + 1;
        const caption = $(this).find('textarea[name="captionEn"]').val();
        const isCover = $(this).find('input[type="radio"]').prop('checked');

        // Check if a replacePic file was uploaded
        const replaceInput = $(this).find('input[name="replacePic"]')[0];
        const hasReplacedImage = replaceInput && replaceInput.files.length > 0;

        // Push photo data
        photos.push({
            id: photoId,
            position: position,
            caption_en: caption,
            is_cover: isCover ? 1 : 0,
            has_replaced_image: hasReplacedImage ? 1 : 0,
        });
    });

    // Create FormData
    const formData = new FormData();
    formData.append('photosData', JSON.stringify(photos));
    formData.append('action', 'editPhotos');
    formData.append('csrf_token', csrfToken);

    // Add new photos
    const fileInput = document.getElementById('picture2');
    if (fileInput && fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('newPhotos[]', fileInput.files[i]);
        }
    }

    // Add replaced photos
    // Replace this block in the saveChanges() function
    $('.photo-row').each(function () {
        const photoId = $(this).attr('id').split('-')[1];
        const replaceInput = $(this).find('input[name="replacePic"]')[0];
        if (replaceInput && replaceInput.files.length > 0) {
            formData.append(`replacedPhotos[${photoId}]`, replaceInput.files[0]);
        }
    });

    // Get album ID from URL
    const albumId = new URLSearchParams(window.location.search).get("album_id");
    formData.append('album_id', albumId);

    // Show loader
    $("#loader").show();

    // Submit data
    $.ajax({
        url: "src/controllers/gallery/album_api.php",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            location.reload();
           
        },
        error: function (xhr, status, error) {
            $("#loader").hide();
        }
    });
}


function previewImage(input, previewId) {

    $(`#error-${input.id}`).empty();

    const file = input.files[0];
    const maxFileSizeBytes = maxFileSizeKB * 1024;

    const errorMessageElement = document.createElement('div');
    errorMessageElement.id = `error-${input.id}`;
    input.parentNode.insertBefore(errorMessageElement, input.nextSibling);

    errorMessageElement.textContent = '';

    if (file) {
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(fileExtension)) {
            errorMessageElement.textContent = `File format not supported. Please upload a file with the extension jpg, jpeg, or png.`;
            input.value = '';
            return;
        }
        if (file.size > maxFileSizeBytes) {
            errorMessageElement.textContent = `File size exceeds the allowed limit of ${maxFileSizeKB} KB. Please choose a smaller file.`;
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const imgElement = document.getElementById(previewId);
            if (imgElement && imgElement.tagName === 'IMG') {
                imgElement.src = e.target.result;
            } else {
                errorMessageElement.textContent = `Preview element not found or is not an image.`;
            }
        };
        reader.readAsDataURL(file);
    }
    hasChanges = true;
}

let selectedFiles = []; // Store valid files

// Function to check file count and preview images
function checkFiles(input) {
    const previewContainer = document.getElementById('previewImage2');

    // Clear previous previews and errors
    previewContainer.innerHTML = '';
    selectedFiles = [];

    // Horizontal layout styling
    previewContainer.style.display = 'flex';
    previewContainer.style.flexWrap = 'wrap';
    previewContainer.style.gap = '10px';

    Array.from(input.files).forEach((file, index) => {
        if (!validateFile(file)) {
            return; // Skip invalid files
        }
        selectedFiles.push(file); // Add valid file to array

        const reader = new FileReader();
        reader.onload = function (e) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'preview-item';
            previewDiv.dataset.index = index;
            previewDiv.innerHTML = `
                <img src="${e.target.result}" style="height: 100px; width: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                <button type="button" class="remove-btn" onclick="removeFile(${index})" style="display: block; margin-top: 5px; background: #ff4d4d; color: white; border: none; padding: 5px; cursor: pointer; border-radius: 3px;">
                    Remove
                </button>
            `;
            previewContainer.appendChild(previewDiv);
        };
        reader.readAsDataURL(file);
    });

    updateFileInput(input);
    updateSaveButton();
}

// Function to remove file from selectedFiles and update input
function removeFile(index) {
    selectedFiles.splice(index, 1); // Remove file from array
    const input = document.querySelector('input[type="file"][id="picture2"]');
    updateFileInput(input); // Refresh file input
    checkFiles(input); // Refresh previews
}

// Update file input with selectedFiles array
function updateFileInput(input) {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    input.files = dataTransfer.files;
}

// Validate file size and type
function validateFile(file) {
    const errorMessageElement = $("#newPhotoError");
    errorMessageElement.text("");

    const maxFileSizeBytes = maxFileSizeKB * 1024;
    const fileExtension = file.name.split('.').pop().toLowerCase();

    if (file.size > maxFileSizeBytes) {
        errorMessageElement.text(`File "${file.name}" exceeds the allowed limit of ${maxFileSizeKB} KB.`);
        return false;
    }

    if (!allowedExtensions.includes(fileExtension)) {
        errorMessageElement.text(`File "${file.name}" format not supported. Allowed extensions: ${allowedExtensions.join(", ")}.`);
        return false;
    }

    return true;
}

setTimeout(function () {
    $('.cke_notifications_area').remove();
}, 1000);


function deletePhoto(photoId) {
    if (!confirm("Are you sure you want to delete this photo?")) {
        return;
    }
    const isCover = $(`#${photoId}`).find("input[type='radio']").prop("checked");

    if (isCover) {
        alert("Cannot delete the cover photo. Please change the cover photo first.");
        return;
    }
    const photo = photoId.split("-")[1];

    $("#loader").show();
    $.ajax({
        url: 'src/controllers/gallery/deletePhotosController.php',
        type: 'POST',
        data: { photoId: photo },
        success: function (response) {
            window.location.reload();
            const alertType = response.includes("Success!") ? "success" : "danger";
            const messageTemplate = `
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <strong>${response}</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`;

            $("#msg").append(messageTemplate);
            $('html, body').animate({ scrollTop: 0 }, 'fast');
            $(`#${photoId}`).remove();
        },
        error: function (xhr, status, error) {
            $("#loader").hide();
            console.error('Error deleting photo:', error);
        }
    });
}
