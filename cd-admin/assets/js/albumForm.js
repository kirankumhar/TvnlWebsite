// Define functions outside the document ready function

function isSetEmpty(setId) {
    const albumPic = $('#' + setId).find('input[name="albumPic[]"]').val();
    const caption = $('#' + setId).find('input[name="caption[]"]').val();
    
    return !albumPic || !caption;
}

function previewImage(input, previewId, maxFileSizeKB = 500) {

    $(`#error-${input.id}`).empty();

    const file = input.files[0];
    const maxFileSizeBytes = maxFileSizeKB * 1024; 

    const errorMessageElement = document.createElement('div');
    errorMessageElement.id = `error-${input.id}`;
    input.parentNode.insertBefore(errorMessageElement, input.nextSibling);

    errorMessageElement.textContent = '';
    document.getElementById(previewId).innerHTML = '';

    if (file) {

        const allowedExtensions = ['jpg', 'jpeg', 'png'];
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(fileExtension)) {
            
            errorMessageElement.textContent = `File format not supported. Please upload a file with the extension jpg, jpeg, or png.`;
            input.value = ''; 
            return; 
        }
        if (file.size > maxFileSizeBytes) {
            // File size exceeds the allowed limit
            errorMessageElement.textContent = `File size exceeds the allowed limit of ${maxFileSizeKB} KB. Please choose a smaller file.`;
            input.value = ''; // Reset the file input field to remove the selected file
            return; // Stop further execution of the function
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" alt="Image Preview" style="max-width: 100px;">`;
        };
        reader.readAsDataURL(file);
    } else {
        // $(`error-${input.id}`).empty();
    }
}

// Usage: Make sure to pass the previewId and file input element to the function when the change event is triggered.

function checkSet(element) {
    const setId = $(element).closest('.image-caption-set').attr('id');
    const radioButton = $('#' + setId).find('.form-check-input');

    if (isSetEmpty(setId)) {
        radioButton.prop('disabled', true);
    } else {
        radioButton.prop('disabled', false);
    }
}

function deleteSet(setId) {
    $('#' + setId).remove();
    reorderSets();
}

function reorderSets() {
    let currentSetCount = 1;
    $('.image-caption-set').each(function () {
        $(this).find('label').text(`Picture Attachment ${currentSetCount}`);
        $(this).find('.form-check-input').attr('id', `coverPhoto${currentSetCount}`).val(currentSetCount);
        $(this).find('.form-check-label').attr('for', `coverPhoto${currentSetCount}`);
        checkSet($(this));
        currentSetCount++;
    });
}

function addNewSet(setCount) {
    const newSet = `
        <div class="form-group row mt-3 image-caption-set" id="set-${setCount}">
            <label for="albumPic${setCount}" class="col-md-4 col-form-label">Picture Attachment ${setCount}</label>
            <div class="col-md-8 d-flex align-items-center">
                <div class="flex-grow-1">
                    <input type="file" name="albumPic[]" id="albumPic${setCount}" class="form-control" accept="image/*" onchange="previewImage(this, 'previewImage${setCount}')" required>
                    <input type="text" name="caption[]" id="caption${setCount}" class="form-control mt-2" placeholder="Caption for image" onchange="checkSet(this)" required>
                    <div id="previewImage${setCount}" class="mt-2"></div>
                </div>
                <div class="d-flex flex-column ms-3">
                    <button type="button" class="btn btn-danger mb-2" onclick="deleteSet('set-${setCount}')">Delete Set</button>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="coverPhoto" id="coverPhoto${setCount}" value="${setCount}" disabled>
                        <label for="coverPhoto${setCount}" class="form-check-label">Mark as Cover Photo</label>
                    </div>
                </div>
            </div>
        </div>`;

    $('#image-caption-sets').append(newSet);
}

// Add jQuery event listener for adding more sets
$(document).ready(function () {
    let setCount = 1;
    const maxSets = 20;

    $('#add-set-btn').on('click', function () {
        let anySetIsBlank = false;
        $('.image-caption-set').each(function () {
            if (isSetEmpty($(this).attr('id'))) {
                anySetIsBlank = true;
                return false; // Stop iterating
            }
        });

        if (anySetIsBlank) {
            alert("Cannot add more sets when any existing set is blank. Please fill in all fields.");
        } else {
            if (setCount < maxSets) {
                setCount++;
                addNewSet(setCount);
            } else {
                alert("Maximum of 20 sets reached. Cannot add more.");
            }
        }
    });
});

// ********************* Video /////////////////////////////

function addNewVideo() {

    const albumId = new URLSearchParams(window.location.search).get("album_id");
    const VideoList = document.getElementById("VideoList");
    const currentVideoCount = VideoList.getElementsByClassName("Video-row").length;

    if (currentVideoCount >= 12) {
        alert("You cannot add more than 20 Videos.");
        return;
    }

    const VideoInput = $("#newVideoInput").val();
    const captionEn = $("#newVideoCaptionEn").val();

    if (!captionEn || !VideoInput) {
        alert("English Caption and Youtube Video Link are required.");
        return;
    }

    // const formData = new FormData();
    // formData.append('albumId', albumId);
    // formData.append('Video', VideoInput);
    // formData.append('caption_en', captionEn);
    // formData.append('caption_hn', captionHn);

    // $.ajax({
    //     url: 'editInsertVideoAlbum.php',
    //     type: 'POST',
    //     data: formData,
    //     processData: false,
    //     contentType: false,
    //     success: function (response) {
    //         // location.reload();
    //     },
    //     error: function (xhr, status, error) {
    //         console.error('Error adding Video:', error);
    //         // location.reload();
    //     }
    // });
}

function deleteVideo(videoId) {
    if (!confirm("Are you sure you want to delete this Video?")) {
        return;
    }
    const isCover = $(`#${videoId}`).find("input[type='radio']").prop("checked");

    if (isCover) {
        alert("Cannot delete the cover Video. Please change the cover Video first.");
        return;
    }
    const Video = videoId.split("-")[1];

    $.ajax({
        url: 'deleteVideoAlnumPics.php',
        type: 'POST',
        data: { videoId: Video },
        success: function (response) {
            const alertType = response.includes("Success!") ? "success" : "danger";
            const messageTemplate = `
                                                                                            <div class="alert alert-${alertType} alert-dismissible fade show mt-3" role="alert">
                                                                                                <strong>${response}</strong>
                                                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                                                    <span aria-hidden="true">&times;</span>
                                                                                                </button>
                                                                                            </div>`;

            $("#msg").append(messageTemplate);
            $('html, body').animate({ scrollTop: 0 }, 'fast');
            $(`#${videoId}`).remove();
        },
        error: function (xhr, status, error) {
            console.error('Error deleting Video:', error);
        }
    });
}

function saveChanges() {
    const albumId = new URLSearchParams(window.location.search).get("album_id");
    const sub_cat = $("#category").val();
    const enAlbumTitle = $("#enalbumTitle").val();
    // const enAlbumDescription = $("#enalbumDescription").val();
    // const hnAlbumDescription = $("#hnalbumDescription").text();
    const enAlbumDescription = CKEDITOR.instances.enalbumDescription.getData(); // Get CKEditor content
    const dateOfEvent = $("#dateOfEvent").val();
    const location = $("#location").val();

    const VideosData = [];
    $("#VideoList .Video-row").each(function () {
        const videoId = $(this).attr("id").split("-")[1];
        const videoLink = $(this).find("input[type='text']").eq(0).val();  // First input
        const captionEn = $(this).find("input[type='text']").eq(1).val();  // Middle input
        const captionHn = $(this).find("input[type='text']").eq(2).val();  // Last input

        const isCover = $(this).find("input[type='radio']:checked").val();

        // VideosData.push({
        //     id: videoId,
        //     videoLink: videoLink,
        //     captionEn: captionEn,
        //     captionHn: captionHn,
        //     isCover: isCover
        // });
    });

    // $.ajax({
    //     enctype: 'multipart/form-data',
    //     url: "editVideoAlbumController.php",
    //     type: "POST",
    //     processData: false,
    //     contentType: false,
    //     data: JSON.stringify({
    //         sub_cat_id: sub_cat,
    //         albumId: albumId,
    //         enAlbumTitle: enAlbumTitle,
    //         hnAlbumTitle: hnAlbumTitle,
    //         enAlbumDescription: enAlbumDescription,
    //         hnAlbumDescription: hnAlbumDescription,
    //         dateOfEvent: dateOfEvent,
    //         location: location,
    //         Videos: VideosData
    //     }),
    //     // contentType: "application/json",
    //     success: function (response) {
    //         // console.log("Changes saved:", response);
    //         const success = `<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    //                                                                                                                                     <strong>Success!</strong> Changes saved successfully.
    //                                                                                                                                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    //                                                                                                                                         <span aria-hidden="true">&times;</span>
    //                                                                                                                                     </button>
    //                                                                                                                                 </div>`;

    //         $("#msg").append(success);
    //         $('html, body').animate({ scrollTop: 0 }, 'fast');
    //     },
    //     error: function (xhr, status, error) {

    //         const errorMsg = `<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    //                                                                                                                                     <strong>Error!</strong> Something went wrong!.
    //                                                                                                                                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    //                                                                                                                                         <span aria-hidden="true">&times;</span>
    //                                                                                                                                     </button>
    //                                                                                                                                 </div>`;

    //         $("#msg").append(errorMsg);
    //         $('html, body').animate({ scrollTop: 0 }, 'slow');
    //         // console.error("Error saving changes:", error);
    //     }
    // });
}
