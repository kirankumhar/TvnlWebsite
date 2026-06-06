const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute("content");

function checkFiles(input) {
  var files = input.files;
  var maxFiles = 6;
  var fileCountMessage = document.getElementById("fileCountMessage");

  if (files.length > maxFiles) {
    input.value = "";
    fileCountMessage.classList.remove("hidden");
    return false;
  } else {
    fileCountMessage.classList.add("hidden");
  }
}

$(document).ready(function () {
  $("#addVideo").click(function () {
    var $videoGroups = $('div[id^="video"]');
    var $lastVideoGroup = $videoGroups.last();
    var nextVideoNumber = $videoGroups.length + 1;
    var countHidden = 0;

    $videoGroups.each(function () {
      if ($(this).css("display") === "none") {
        countHidden++;
      }
    });
    if (countHidden != 0) {
      nextVideoIndex = 5 - countHidden;
      var $videoAttachInput = $("#video" + (nextVideoIndex - 1)).find(
        'input[name="videoAttach' + (nextVideoIndex - 1) + '"]'
      );
      var $videoAttachTitleInput = $("#video" + (nextVideoIndex - 1)).find(
        'input[name="videoAttach_title' + (nextVideoIndex - 1) + '"]'
      );

      if (
        $videoAttachInput.val().trim() === "" &&
        $videoAttachTitleInput.val().trim() === ""
      ) {
        alert(
          "Please fill data in Youtube Attachment URL and Youtube Video Title."
        );
        return;
      }

      $("#video" + nextVideoIndex).show();
    } else {
      $(this).hide();
    }
  });

  $("#deleteVideo").click(function () {
    if (confirm("Are you sure you want to delete this video?")) {
      var $videoGroups = $('div[id^="video"]');
      var $lastVideoGroup = $videoGroups.last();
      var nextVideoNumber = $videoGroups.length + 1;
      var countHidden = 0;

      $videoGroups.each(function () {
        if ($(this).css("display") === "none") {
          countHidden++;
        }
      });
      if (countHidden != 3) {
        nextVideoIndex = 4 - countHidden;

        var $videoAttachInput = $("#video" + (nextVideoIndex + 1))
          .find('input[name="videoAttach' + (nextVideoIndex + 1) + '"]')
          .empty();
        var $videoAttachTitleInput = $("#video" + (nextVideoIndex + 1))
          .find('input[name="videoAttach_title' + (nextVideoIndex + 1) + '"]')
          .empty();
        $("#video" + nextVideoIndex).hide();
      }
      $("#addVideo").show();
    }
  });

  $("#addPdf").click(function () {
    var $pdfGroups = $('div[id^="pdf"]');
    var $lastPDFGroup = $pdfGroups.last();
    var nextVideoNumber = $pdfGroups.length + 1;
    var countHidden = 0;

    $pdfGroups.each(function () {
      if ($(this).css("display") === "none") {
        countHidden++;
      }
    });
    if (countHidden != 0) {
      nextVideoIndex = 9 - countHidden;

      var $pdfAttachInput = $("#pdf" + (nextVideoIndex - 1)).find(
        'input[name="pdf_attachment' + (nextVideoIndex - 1) + '"]'
      );
      var $pdfAttachTitleInput = $("#pdf" + (nextVideoIndex - 1)).find(
        'input[name="pdf_attachement_title' + (nextVideoIndex - 1) + '"]'
      );

      if (
        ($pdfAttachInput.length && $pdfAttachInput[0].files.length === 0) ||
        $pdfAttachTitleInput.val().trim() === ""
      ) {
        alert("Please select a pdf file and fill in the title field.");
        return;
      }
      $("#pdf" + nextVideoIndex).show();
    } else {
      $(this).hide();
    }
  });

  $("#deletePdf").click(function () {
    if (
      confirm(
        "Are you sure you want to delete last PDF Attachement and title ?"
      )
    ) {
      var $pdfGroups = $('div[id^="pdf"]');
      var countHidden = 0;

      $pdfGroups.each(function () {
        if ($(this).css("display") === "none") {
          countHidden++;
        }
      });
      if (countHidden != 7) {
        nextVideoIndex = 1 - countHidden;

        var $videoAttachInput = $("#video" + (nextVideoIndex + 1))
          .find('input[name="pdf_attachment' + (nextVideoIndex + 1) + '"]')
          .empty();
        var $videoAttachTitleInput = $("#video" + (nextVideoIndex + 1))
          .find(
            'input[name="pdf_attachement_title' + (nextVideoIndex + 1) + '"]'
          )
          .empty();
        $("#video" + nextVideoIndex).hide();
      }
      $("#pdf" + nextVideoIndex).hide();
      $("#addPdf").show();
    }
  });
});

setTimeout(function () {
  $(".cke_notifications_area").remove();
}, 1000);

jQuery(document).ready(function () {
  $("input[type=file]").change(function () {
    if (jQuery(this).attr("name") == "picture1") {
      jQuery("#previewImage1").empty();
      var preview = jQuery("#previewImage1");
    } else if (jQuery(this).attr("id") == "picture2") {
      jQuery("#previewImage2").empty();
      var preview = jQuery("#previewImage2");
    }
    var files = this.files;

    for (var i = 0; i < files.length; i++) {
      (function (file, index) {
        if (file) {
          if (/\.(jpe?g|png|gif)$/i.test(file.name)) {
            var reader = new FileReader();
            reader.onload = function (e) {
              var image = $("<img/>", {
                height: 150,
                class: "preview-image", // Add class for styling
                title: file.name,
                src: e.target.result,
              });
              // Add numbering
              var numbering = $("<span>").text(index + 1 + ". ");
              preview.append(numbering);
              preview.append(image);
            };
            reader.readAsDataURL(file);
          }
        }
      })(files[i], i);
    }
  });
});

$(document).on("click", ".delete-photo-albums-button", function (e) {
  e.preventDefault();

  if (confirm("Are you sure you want to delete this album?")) {
    let albumId = $(this).data("id");

    $(this).prop("disabled", true);
    const formData = new FormData();
    formData.append("albumId", albumId);
    formData.append("action", "deleteAlbum"); // Ensure action matches the backend function
    formData.append("csrf_token", csrfToken);

    $.ajax({
      url: "src/controllers/gallery/album_api.php",
      type: "POST",
      data: formData,
      processData: false, // Required for FormData
      contentType: false, // Required for FormData
      success: function (response) {
        location.reload(); // Refresh page after delete
      },
      error: function (xhr, status, error) {
        alert("Error deleting album: " + xhr.responseText);
      },
    });
  }
});

$(document).on("click", ".delete-domains-button", function (e) {
  e.preventDefault();

  if (confirm("Are you sure you want to delete this Domains?")) {
    let commrId = $(this).data("id");

    $(this).prop("disabled", true);
    const formData = new FormData();
    formData.append("uid", commrId);
    formData.append("action", "deleteDomains"); // Ensure action matches the backend function
    formData.append("csrf_token", csrfToken);

    $.ajax({
      url: "src/controllers/DomainsController.php",
      type: "POST",
      data: formData,
      processData: false, // Required for FormData
      contentType: false, // Required for FormData
      success: function (response) {
        location.reload(); // Refresh page after delete
      },
      error: function (xhr, status, error) {
        alert("Error deleting album: " + xhr.responseText);
      },
    });
  }
});

$(document).on("click", ".delete-category-button", function (e) {
  e.preventDefault();

  if (confirm("Are you sure you want to delete this Category?")) {
    let catId = $(this).data("id");

    $(this).prop("disabled", true);
    const formData = new FormData();
    formData.append("uid", catId);
    formData.append("action", "deleteCategory"); // Ensure action matches the backend function
    formData.append("csrf_token", csrfToken);

    $.ajax({
      url: "src/controllers/CategoryController.php",
      type: "POST",
      data: formData,
      processData: false, // Required for FormData
      contentType: false, // Required for FormData
      success: function (response) {
        location.reload(); // Refresh page after delete
      },
      error: function (xhr, status, error) {
        alert("Error deleting album: " + xhr.responseText);
      },
    });
  }
});

$(document).on("click", ".delete-child-sub-category-button", function (e) {
  e.preventDefault();

  if (confirm("Are you sure you want to delete this Child Sub Category?")) {
    let catId = $(this).data("id");

    $(this).prop("disabled", true);
    const formData = new FormData();
    formData.append("uid", catId);
    formData.append("action", "deleteChildSubCategory"); // Ensure action matches the backend function
    formData.append("csrf_token", csrfToken);

    $.ajax({
      url: "src/controllers/ChildSubCategoryController.php",
      type: "POST",
      data: formData,
      processData: false, // Required for FormData
      contentType: false, // Required for FormData
      success: function (response) {
        location.reload(); // Refresh page after delete
      },
      error: function (xhr, status, error) {
        alert("Error deleting album: " + xhr.responseText);
      },
    });
  }
});

$(document).on("click", ".delete-sub-category-button", function (e) {
  e.preventDefault();

  if (confirm("Are you sure you want to delete this Sub Category?")) {
    let subcatId = $(this).data("id");

    $(this).prop("disabled", true);
    const formData = new FormData();
    formData.append("uid", subcatId);
    formData.append("action", "deleteSubCategory"); // Ensure action matches the backend function
    formData.append("csrf_token", csrfToken);

    $.ajax({
      url: "src/controllers/SubCategoryController.php",
      type: "POST",
      data: formData,
      processData: false, // Required for FormData
      contentType: false, // Required for FormData
      success: function (response) {
        location.reload(); // Refresh page after delete
      },
      error: function (xhr, status, error) {
        alert("Error deleting album: " + xhr.responseText);
      },
    });
  }
});

$(document).ready(function () {
  $("#pickDomainId").on("change", function () {
    let domainId = $(this).val();

    $("#categoryId").html('<option value="">Loading...</option>');

    // $(this).prop('disabled', true);
    const formData = new FormData();
    formData.append("domainId", domainId);
    formData.append("action", "fetchCategories");
    formData.append("csrf_token", csrfToken);

    if (domainId !== "") {
      $.ajax({
        url: "src/fetch_categories.php", // controller
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        dataType: "json",
        success: function (response) {
          $("#categoryId").html('<option value="">Choose Category...</option>');

          if (response.length > 0) {
            $.each(response, function (key, value) {
              $("#categoryId").append(
                '<option value="' +
                  value.id +
                  '">' +
                  value.category_name +
                  "</option>"
              );
            });
          }
        },
        error: function () {
          $("#categoryId").html(
            '<option value="">Error loading categories</option>'
          );
        },
      });
    } else {
      $("#categoryId").html('<option value="">Choose Category...</option>');
    }
  });
});

$(document).ready(function () {
  $("#subCategoryList").on("change", function () {
    let domainId = $(this).val();
    const currentPage = $("#currentPage").val() ?? '';
    $("#categoryId").html('<option value="">Loading...</option>');

    // $(this).prop('disabled', true);
    const formData = new FormData();
    formData.append("domainId", domainId);
    formData.append("action", "fetchSubCategories");
    formData.append("currentPage", currentPage);
    formData.append("csrf_token", csrfToken);

    if (domainId !== "") {
      $.ajax({
        url: "src/fetch_categories.php", // controller
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        dataType: "json",
        success: function (response) {
          $("#postcategoryId").html(
            '<option value="">Choose Category...</option>'
          );

          if (response.length > 0) {
            $.each(response, function (key, value) {
              $("#postcategoryId").append(
                '<option value="' +
                  value.id +
                  '">' +
                  value.sub_category_name +
                  "</option>"
              );
            });
          }
        },
        error: function () {
          $("#postcategoryId").html(
            '<option value="">Error loading categories</option>'
          );
        },
      });
    } else {
      $("#postcategoryId").html('<option value="">Choose Category...</option>');
    }
  });
});

$(document).ready(function () {
  $("#postcategoryId").on("change", function () {
    let subcatid = $(this).val();

    $("#SubCategoryId").html('<option value="">Loading...</option>');

    // $(this).prop('disabled', true);
    const formData = new FormData();
    formData.append("subcatid", subcatid);
    formData.append("action", "fetchChildSubCategories");
    formData.append("csrf_token", csrfToken);

    if (subcatid !== "") {
      $.ajax({
        url: "src/fetch_categories.php", // controller
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        dataType: "json",
        success: function (response) {
          $("#SubCategoryId").html(
            '<option value="">Choose Category...</option>'
          );

          if (response.length > 0) {
            $.each(response, function (key, value) {
              $("#SubCategoryId").append(
                '<option value="' +
                  value.id +
                  '">' +
                  value.child_sub_category_name +
                  "</option>"
              );
            });
          }
        },
        error: function () {
          $("#SubCategoryId").html(
            '<option value="">Error loading categories</option>'
          );
        },
      });
    } else {
      $("#SubCategoryId").html('<option value="">Choose Category...</option>');
    }
  });
});

$(document).ready(function () {
  $("#categoryId").on("change", function () {
    let catId = $(this).val();
    $("#subCategoryId").html('<option value="">Loading...</option>');

    const formData = new FormData();
    formData.append("catid", catId);
    formData.append("action", "fetchSubCategories");
    formData.append("csrf_token", csrfToken);

    if (catId !== "") {
      $.ajax({
        url: "src/fetch_categories.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        dataType: "json",
        success: function (response) {
          $("#subCategoryId").html(
            '<option value="">Choose Sub Category...</option>'
          );

          if (response.length > 0) {
            $.each(response, function (key, value) {
              $("#subCategoryId").append(
                '<option value="' +
                  value.id +
                  '">' +
                  value.sub_category_name +
                  "</option>"
              );
            });
          }
        },
        error: function () {
          $("#subCategoryId").html(
            '<option value="">Error loading categories</option>'
          );
        },
      });
    } else {
      $("#subCategoryId").html(
        '<option value="">Choose Sub Category...</option>'
      );
    }
  });
});

$(document).ready(function () {
  $("#subCategoryId").on("change", function () {
    let subcatId = $(this).val();
    $("#childSubCategoryId").html('<option value="">Loading...</option>');

    const formData = new FormData();
    formData.append("subcatid", subcatId);
    formData.append("action", "fetchChildSubCategories");
    formData.append("csrf_token", csrfToken);

    if (subcatId !== "") {
      $.ajax({
        url: "src/fetch_categories.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        dataType: "json",
        success: function (response) {
          $("#childSubCategoryId").html(
            '<option value="">Choose Child Sub Category...</option>'
          );

          if (response.length > 0) {
            $.each(response, function (key, value) {
              $("#childSubCategoryId").append(
                '<option value="' +
                  value.id +
                  '">' +
                  value.child_sub_category_name +
                  "</option>"
              );
            });
          }
        },
        error: function () {
          $("#childSubCategoryId").html(
            '<option value="">Error loading categories</option>'
          );
        },
      });
    } else {
      $("#childSubCategoryId").html(
        '<option value="">Choose Child Sub Category...</option>'
      );
    }
  });
});
