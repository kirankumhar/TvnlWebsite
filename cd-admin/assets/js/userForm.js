function validateFile(file) {

    const maxFileSizeKB = 2000;
    const allowedExtensions = ["jpg", "jpeg", "png", "pdf"];

    const errorMessageElement = $("#newPhotoError");
    errorMessageElement.text("");

    const maxFileSizeBytes = maxFileSizeKB * 1024;
    if (file.size > maxFileSizeBytes) {
        errorMessageElement.text(`File size exceeds the allowed limit of ${maxFileSizeKB} KB.`);
        return false;
    }

    const fileExtension = file.name.split(".").pop().toLowerCase();
    if (!allowedExtensions.includes(fileExtension)) {
        errorMessageElement.text(`File format not supported. Allowed extensions: ${allowedExtensions.join(", ")}.`);
        return false;
    }
    return true;
}

$(document).ready(function () {

    // if ($('#sessionYear').length > 0) {
    //     var currentYear = new Date().getFullYear();

    //     for (var i = currentYear; i >= currentYear - 10; i--) {
    //         var option = document.createElement('option');
    //         option.value = i;
    //         option.textContent = i;
    //         document.getElementById('sessionYear').appendChild(option);
    //     }
    // }

    // if ($('#financialYear').length > 0) {
    //     let date = new Date();
    //     let startYear = date.getMonth() >= 3 ? date.getFullYear() : date.getFullYear() - 1;
    //     let $select = $("#financialYear");

    //     for (let i = 0; i < 10; i++) {
    //         let year = startYear - i;
    //         $select.append(`<option value="${year}-${year + 1}">${year}-${year + 1}</option>`);
    //     }
    // }

    // $("#type").change(function () {
    //     var getType = $(this).val();

    //     if (getType === "fy") {
    //         $("#syField").find("input").prop("disabled", true);
    //         $("#syField").find("select").prop("disabled", true);
    //         $("#syField").hide();

    //         $("#fyField").find("input").prop("disabled", false);
    //         $("#fyField").find("select").prop("disabled", false);
    //         $("#fyField").show();
    //     } else {
    //         $("#fyField").find("input").prop("disabled", true);
    //         $("#fyField").find("select").prop("disabled", true);
    //         $("#fyField").hide();

    //         $("#syField").find("input").prop("disabled", false);
    //         $("#syField").find("select").prop("disabled", false);
    //         $("#syField").show();
    //     }
    // })

    $("#attachment").change(function () {
        var file = $("#attachment")[0].files[0];
        $("#size_error").hide();
        $("#preview-attachment").empty();

        if (!validateFile(file)) {
            $("#size_error").show();
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("preview-attachment").innerHTML = `<embed src="${e.target.result}" alt="Attachment Preview" style="max-width: 300px;">`;

        };
        reader.readAsDataURL(file);
    })

    // $('button[type="submit"]').click(function () {
    //     $('#loader').show();
    // });

    $('form').on('submit', function (event) {
        $('#loader').show();
    });


    $('.delete-post-button').on('click', function () {
        var postingId = $(this).data('id');

        if (confirm('Are you sure you want to delete this posting?')) {
            $('#loader').show();
            $.ajax({
                url: '/cdrms/src/controllers/PostingController.php',
                method: 'POST',
                data: {
                    post: postingId, action: 'deletePost'
                },
                success: function (response) {
                    $('#loader').hide();
                    alert('Posting deleted successfully.');
                    location.reload();
                },
                error: function (error) {
                    // console.error(error);
                    $('#loader').hide();
                    alert('Failed to delete posting.');
                }
            });
        }
    });

    $('#delete-attach-button').on('click', function () {
        var postingId = $(this).data('id');

        if (confirm('Are you sure you want to delete this posting?')) {
            $('#loader').show();
            $.ajax({
                url: '/cdrms/src/controllers/PostingController.php',
                method: 'POST',
                data: {
                    post: postingId, action: 'deleteAttach'
                },
                success: function (response) {
                    $('#loader').hide();
                    alert('Posting deleted successfully.');
                    location.reload();
                },
                error: function (error) {
                    $('#loader').hide();
                    // console.error(error);
                    alert('Failed to delete posting.');
                }
            });
        }
    });

    $('.delete-category-button').on('click', function () {
        var catId = $(this).data('id');

        if (confirm('Are you sure you want to delete this category?')) {
            $('#loader').show();
            $.ajax({
                url: '/cdrms/src/controllers/CategoryController.php',
                method: 'POST',
                data: {
                    uid: catId, action: 'deleteCategory'
                },
                success: function (response) {
                    // $('#loader').hide();
                    location.reload();
                },
                error: function (error) {
                    $('#loader').hide();
                    console.error(error);
                    alert('Failed to delete Session/Financial.');
                }
            });
        }
    });

    $('.delete-session-button').on('click', function () {
        var syfyId = $(this).data('id');

        if (confirm('Are you sure you want to delete this session or financial year?')) {
            $('#loader').show();
            $.ajax({
                url: '/cdrms/src/controllers/SyFyController.php',
                method: 'POST',
                data: {
                    uid: syfyId, action: 'deleteSyFy'
                },
                success: function (response) {
                    $('#loader').hide();
                    if (response.status === 'success') {
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                    location.reload();
                },
                error: function (error) {
                    console.error(error);
                    alert('Failed to delete Session/Financial.');
                }
            });
        }
    });

    $('.delete-sub-category-button').on('click', function () {
        var subcatId = $(this).data('id');

        if (confirm('Are you sure you want to delete this sub category?')) {
            $('#loader').show();
            $.ajax({
                url: '/cdrms/src/controllers/SubCategoryController.php',
                method: 'POST',
                data: {
                    uid: subcatId, action: 'deleteSubCategory'
                },
                success: function (response) {
                    $('#loader').hide();
                    alert('Sub Category deleted successfully.');
                    location.reload();
                },
                error: function (error) {
                    console.error(error);
                    alert('Failed to delete sub category.');
                }
            });
        }
    });

    $('.delete-child-sub-category-button').on('click', function () {
        var subcatId = $(this).data('id');

        if (confirm('Are you sure you want to delete this child sub category?')) {
            $('#loader').show();
            $.ajax({
                url: '/cdrms/src/controllers/ChildSubCategoryController.php',
                method: 'POST',
                data: {
                    uid: subcatId, action: 'deleteChildSubCategory'
                },
                success: function (response) {
                    // $('#loader').hide();
                    location.reload();
                },
                error: function (error) {
                    // console.error(error);
                    location.reload();
                }
            });
        }
    });

    $('#category').on('change', function () {
        var categoryId = $(this).val();
        $('#loader').show();
        if (categoryId) {
            $.ajax({
                url: '/cdrms/src/controllers/PostingController.php',
                method: 'GET',
                data: {
                    action: 'fetchSubCategories',
                    categoryId: categoryId
                },
                success: function (response) {
                    $('#loader').hide();
                    $('#subCategory').empty();
                    $('#subCategory').append('<option value="">Select a sub-category</option>');

                    response.forEach(function (subCategory) {
                        var option = $('<option>')
                            .val(subCategory.id)
                            .text(subCategory.sub_category_name);
                        $('#subCategory').append(option);
                    });
                },
                error: function (error) {
                    $('#loader').hide();
                    // console.error('Failed to fetch sub-categories:', error);
                    alert('Failed to fetch sub-categories:');
                }
            });
        } else {
            $('#loader').hide();
            $('#sub_category').empty();
            $('#sub_category').append('<option value="">Select a sub-category</option>');
        }
    });

    $('#new_tag').on('change', function () {
        var new_tag = $(this).val();

        if (new_tag === 'Y') {
            $('#newTagDays').val('1').prop('readonly', false);
        } else {
            $('#newTagDays').val('0').prop('readonly', true);
        }
    });
    $('#syFyTable').DataTable();
    $('#postingTable').DataTable();

    $('#post_category').on('change', function () {
        var categoryId = $(this).val();
        $('#loader').show();
        if (categoryId) {
            $.ajax({
                url: '/cdrms/src/controllers/PostingController.php',
                method: 'GET',
                data: {
                    action: 'fetchType',
                    categoryId: categoryId
                },
                success: function (response) {

                    $('#cat_type').empty();
                    $('#cat_type').append('<option value="">Choose type...</option>');

                    $('#loader').hide();
                    response.forEach(function (cat_type) {
                        if (cat_type.type == 'sy') {
                            var option = $('<option>')
                                .val(cat_type.id)
                                .text(cat_type.calender_year);
                        } else {
                            var option = $('<option>')
                                .val(cat_type.id)
                                .text(cat_type.financial_year);
                        }

                        $('#cat_type').append(option);
                    });
                },
                error: function (error) {
                    $('#loader').hide();
                    console.error('Failed to fetch type:', error);
                }
            });
        } else {
            $('#loader').hide();
            $('#cat_type').empty();
            $('#cat_type').append('<option value="">Choose type...</option>');
        }
    });

    $('#search-post').on('click', function (event) {
        var category = $('#post_category').val();
        var type = $('#cat_type').val();
        $('#loader').show();

        $.ajax({
            url: '/cdrms/src/controllers/PostingController.php',
            method: 'GET',
            data: {
                action: 'searchCatType',
                categoryId: category,
                type: type
            },
            success: function (response) {
                $('#loader').hide();
                var base_url = 'https://jspc.computered.co.in/cdrms';
                var postingsTableBody = $('#postingsTableBody');
                postingsTableBody.empty();
                $.each(response, function (index, row) {
                    var subCategory = row.sub_category ? row.sub_category_name : 'NA';
                    var tableRow = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${row.cat_name}</td>
                            <td>${subCategory}</td>
                            <td><a href="${base_url}/view-posting.php?id=${row.id}">${row.title}</a></td>
                            <td>${row.created_on}</td>
                            <td><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewDocModal" data-pdf="${base_url}/${row.attachment}">View</button></td>
                            <td>Active</td>
                            <td>
                                <a href="${base_url}/view-posting.php?id=${row.id}" class="btn btn-success btn-sm"><i class="ti ti-eye"></i></a>&nbsp;&nbsp;
                                <a href="${base_url}/edit-postings.php?id=${row.id}" class="btn btn-info btn-sm"><i class="ti ti-edit"></i></a>&nbsp;&nbsp;
                                <button class="btn btn-danger btn-sm delete-post-button" data-id="${row.id}"><i class="ti ti-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    postingsTableBody.append(tableRow);
                })
            },
            error: function (error) {
                $('#loader').hide();
                // console.error('Failed to fetch type:', error);
            }
        });
    })

    $('#viewDocModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var pdfLink = button.data('pdf');
        var modal = $(this);
        modal.find('.modal-body embed').attr('src', pdfLink);
    });


});

$(document).ready(function () {
    $('#updatePass').on('submit', function () {

        var password = $('#UserPassword').val();
        var confirmPassword = $('#confirmPassword').val();
        var passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        if (password === '' || confirmPassword === '') {
            // If either password or confirm password is empty, show an error message and hide the loader
            $("#loader").hide();
            $('#passwordError').text("Both password and confirm password are required.");
            $('#passwordError').show();
            return false;
        } else {
            $('#passwordError').hide();
        }
        if (!passwordPattern.test(password)) {
            $("#loader").hide();
            $('#passwordError').show();
            $('#passwordMismatch').hide();
            return false;
        } else {
            $("#loader").hide();
            $('#passwordError').hide();
        }

        if (password !== confirmPassword) {
            $("#loader").hide();
            $('#passwordMismatch').show();
            return false;
        } else {
            $("#loader").hide();
            $('#passwordMismatch').hide();
        }
    });
});

// ******************************************

$(document).ready(function () {
    let maxFiles = 3;
    let fileCount = 1;

    $("#addPdf").click(function () {
        if (fileCount >= maxFiles) {
            alert("You can only add up to 3 attachments.");
            return;
        }

        let lastFileInput = $(`#pdf${fileCount} input[type="file"]`);
        let lastTextInput = $(`#pdf${fileCount} input[type="text"]`);
        
        if (!lastFileInput.val()  && lastTextInput.val().trim() === '') {
            alert("Please select a file and attachment title before adding a new attachment.");
            return;
        }

        fileCount++;
        $(`#pdf${fileCount}`).show();
    });

    $("#deletePdf").click(function () {
        if (fileCount > 1) {
            $(`#pdf${fileCount} input[type="file"]`).val(""); // Clear input
            $(`#pdf${fileCount} input[type="text"]`).val(""); 
            $(`#pdf${fileCount}`).hide();
            $(`#pdf${fileCount} .preview`).remove(); // Remove preview
            fileCount--;
        }
    });

    // Show PDF preview next to input field
    $("input[type='file']").change(function (event) {
        let file = event.target.files[0];
        let fileType = file.type;
        let fileSize = file.size / 1024 / 1024; // Convert size to MB
        let fileInputDiv = $(this).closest(".row"); // Get the parent row div

        // Allowed file types
        let validExtensions = [
            "application/pdf",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-powerpoint",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation"
        ];

        
        let previewDiv = fileInputDiv.find(".preview-container");
        
        if (!validExtensions.includes(fileType)) {
            alert("Invalid file type! Only PDF, Excel, and PPT are allowed.");
            $(this).val(""); // Clear input
            previewDiv.html("");
            return;
        }

        if (fileSize > 1) {
            alert("File size exceeds 1MB limit.");
            $(this).val(""); // Clear input
            previewDiv.html("");
            return;
        }


        // Remove existing preview
        previewDiv.html("");

        if (fileType === "application/pdf") {
            let objectURL = URL.createObjectURL(file);
            let previewElement = `<embed src="${objectURL}" class="preview" width="100%" height="150px" style="border:1px solid #ccc;">`;
            previewDiv.html(previewElement);
        }
    });

});



