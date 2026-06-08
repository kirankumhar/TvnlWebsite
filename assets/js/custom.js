"use strict";

//Submenu Dropdown Toggle
if ($(".main-header li.dropdown ul").length) {
  $(".main-header .navigation li.dropdown").append(
    '<div class="dropdown-btn"><span class="fa fa-angle-right"></span></div>',
  );
}

//Mobile Nav Hide Show
if ($(".mobile-menu").length) {
  $(".mobile-menu .menu-box").mCustomScrollbar();

  var mobileMenuContent = $(".main-header .nav-outer .main-menu").html();
  $(".mobile-menu .menu-box .menu-outer").append(mobileMenuContent);
  $(".sticky-header .main-menu").append(mobileMenuContent);

  //Dropdown Button
  $(".mobile-menu li.dropdown .dropdown-btn").on("click", function () {
    $(this).toggleClass("open");
    $(this).prev("ul").slideToggle(500);
  });

  //Dropdown Button
  $(".mobile-menu li.dropdown .dropdown-btn").on("click", function () {
    $(this).prev(".megamenu").slideToggle(900);
  });

  //Menu Toggle Btn
  $(".mobile-nav-toggler").on("click", function () {
    $("body").addClass("mobile-menu-visible");
  });

  //Menu Toggle Btn
  $(".mobile-menu .menu-backdrop,.mobile-menu .close-btn").on(
    "click",
    function () {
      $("body").removeClass("mobile-menu-visible");
    },
  );
}

// Scroll to a Specific Div
if ($(".scroll-to-target").length) {
  $(".scroll-to-target").on("click", function () {
    var target = $(this).attr("data-target");
    // animate
    $("html, body").animate(
      {
        scrollTop: $(target).offset().top,
      },
      1000,
    );
  });
}

//Parallax Scene for Icons
if ($(".parallax-scene-1").length) {
  var scene = $(".parallax-scene-1").get(0);
  var parallaxInstance = new Parallax(scene);
}
if ($(".parallax-scene-2").length) {
  var scene = $(".parallax-scene-2").get(0);
  var parallaxInstance = new Parallax(scene);
}
if ($(".parallax-scene-3").length) {
  var scene = $(".parallax-scene-3").get(0);
  var parallaxInstance = new Parallax(scene);
}
if ($(".parallax-scene-4").length) {
  var scene = $(".parallax-scene-4").get(0);
  var parallaxInstance = new Parallax(scene);
}
if ($(".parallax-scene-5").length) {
  var scene = $(".parallax-scene-5").get(0);
  var parallaxInstance = new Parallax(scene);
}

//Add One Page nav
if ($(".scroll-nav").length) {
  $(".scroll-nav").onePageNav();
}

// body-layout
function bodylayout() {
  if ($(".boxed_switch_menu").length) {
    $(".body_switch_btn button").on("click", function () {
      $(".body_switcher").toggleClass("switcher-show");
    });

    $("#myonoffswitch").on("click", function () {
      $(".fixed").toggleClass("static");
    });

    $("#boxed").on("click", function () {
      $(".main_page").addClass("active_boxlayout");
      $("body").addClass("bg");
    });
    $("#full_width").on("click", function () {
      $(".main_page").removeClass("active_boxlayout");
      $("body").removeClass("bg");
    });
  }
}

// Color switcher
function swithcerMenu() {
  if ($(".switch_menu").length) {
    $(".switch_btn button").on("click", function () {
      $(".switch_menu").toggle(500);
    });

    $("#styleOptions").styleSwitcher({
      hasPreview: true,
      fullPath: "assets/css/color/",
      cookie: {
        expires: 30,
        isManagingLoad: true,
      },
    });
  }
}

//Update Header Style and Scroll to Top
function headerStyle() {
  if ($(".main-header").length) {
    var windowpos = $(window).scrollTop();
    var siteHeader = $(".main-header");
    var scrollLink = $(".scroll-top");
    if (windowpos >= 350) {
      siteHeader.addClass("fixed-header");
      scrollLink.fadeIn(300);
    } else {
      siteHeader.removeClass("fixed-header");
      scrollLink.fadeOut(300);
    }
  }
}
headerStyle();

//Accordion Box
function accordion() {
  if ($(".accordion-box").length) {
    $(".accordion-box").on("click", ".accord-btn", function () {
      if ($(this).hasClass("active") !== true) {
        $(".accordion .accord-btn").removeClass("active");
      }

      if ($(this).next(".accord-content").is(":visible")) {
        $(this).removeClass("active");
        $(this).next(".accord-content").slideUp(500);
      } else {
        $(this).addClass("active");
        $(".accordion .accord-content").slideUp(500);
        $(this).next(".accord-content").slideDown(500);
      }
    });
  }
}

//===Language switcher===
function languageSwitcher() {
  if ($("#polyglot-language-options").length) {
    $("#polyglotLanguageSwitcher").polyglotLanguageSwitcher({
      effect: "slide",
      animSpeed: 500,
      testMode: true,
      onChange: function (evt) {
        alert("The selected language is: " + evt.selectedItem);
      },
    });
  }
}

//===Search box ===
function searchbox() {
  //Search Box Toggle
  if ($(".seach-toggle").length) {
    //Dropdown Button
    $(".seach-toggle").on("click", function () {
      $(this).toggleClass("active");
      $(this).next(".search-box").toggleClass("now-visible");
    });
  }
}

// Date picker
function datepicker() {
  if ($("#datepicker").length) {
    $("#datepicker").datepicker();
  }
}

// Time picker
function timepicker() {
  if ($('input[name="time"]').length) {
    $('input[name="time"]').ptTimeSelect();
  }
}

//Hide Loading Box (Preloader)
function handlePreloader() {
  if ($(".loader-wrap").length) {
    $(".loader-wrap").delay(1000).fadeOut(500);
  }
  TweenMax.to($(".loader-wrap .overlay"), 1.2, {
    force3D: true,
    left: "100%",
    ease: Expo.easeInOut,
  });
}

if ($(".preloader-close").length) {
  $(".preloader-close").on("click", function () {
    $(".loader-wrap").delay(200).fadeOut(500);
  });
}

//  Fact counter
function CounterNumberChanger() {
  var timer = $(".timer");
  if (timer.length) {
    timer.appear(function () {
      timer.countTo();
    });
  }
}

// Price Filter
function priceFilter() {
  if ($(".price-ranger").length) {
    $(".price-ranger #slider-range").slider({
      range: true,
      min: 10,
      max: 200,
      values: [11, 99],
      slide: function (event, ui) {
        $(".price-ranger .ranger-min-max-block .min").val("$" + ui.values[0]);
        $(".price-ranger .ranger-min-max-block .max").val("$" + ui.values[1]);
      },
    });
    $(".price-ranger .ranger-min-max-block .min").val(
      "$" + $(".price-ranger #slider-range").slider("values", 0),
    );
    $(".price-ranger .ranger-min-max-block .max").val(
      "$" + $(".price-ranger #slider-range").slider("values", 1),
    );
  }
}

function tabBox() {
  $(".tvnl-updates-card").each(function () {
    var $card = $(this);

    // Attach click handler for each tab group independently
    $card
      .find(".tab-btn")
      .off("click")
      .on("click", function (e) {
        e.preventDefault();

        var target = $($(this).attr("data-tab"));
        if (target.length === 0) return;

        // Deactivate buttons within this card only
        $card.find(".tab-btn").removeClass("active-btn");
        $(this).addClass("active-btn");

        // Hide and show tabs within this card only
        $card.find(".tab").fadeOut(0).removeClass("active-tab");
        target.fadeIn(300).addClass("active-tab");
      });
  });
}

$(document).ready(function () {
  tabBox();
});

// ===Project===
function projectMasonaryLayout() {
  if ($(".masonary-layout").length) {
    $(".masonary-layout").isotope({
      layoutMode: "masonry",
    });
  }
  if ($(".post-filter").length) {
    $(".post-filter li")
      .children(".filter-text")
      .on("click", function () {
        var Self = $(this);
        var selector = Self.parent().attr("data-filter");
        $(".post-filter li").removeClass("active");
        Self.parent().addClass("active");
        $(".filter-layout").isotope({
          filter: selector,
          animationOptions: {
            duration: 500,
            easing: "linear",
            queue: false,
          },
        });
        return false;
      });
  }

  if ($(".post-filter.has-dynamic-filters-counter").length) {
    // var allItem = $('.single-filter-item').length;
    var activeFilterItem = $(".post-filter.has-dynamic-filters-counter").find(
      "li",
    );
    activeFilterItem.each(function () {
      var filterElement = $(this).data("filter");
      var count = $(".filter-layout").find(filterElement).length;
      $(this)
        .children(".filter-text")
        .append('<span class="count">' + count + "</span>");
    });
  }
}

//=== CountDownTimer===
function countDownTimer() {
  if ($(".time-countdown").length) {
    $(".time-countdown").each(function () {
      var Self = $(this);
      var countDate = Self.data("countdown-time"); // getting date

      Self.countdown(countDate, function (event) {
        $(this).html("<h2>" + event.strftime("%D : %H : %M : %S") + "</h2>");
      });
    });
  }
  if ($(".time-countdown-two").length) {
    $(".time-countdown-two").each(function () {
      var Self = $(this);
      var countDate = Self.data("countdown-time"); // getting date

      Self.countdown(countDate, function (event) {
        $(this).html(
          '<li> <div class="box"> <span class="days">' +
            event.strftime("%D") +
            '</span> <span class="timeRef">days</span> </div> </li> <li> <div class="box"> <span class="hours">' +
            event.strftime("%H") +
            '</span> <span class="timeRef clr-1">Hours</span> </div> </li> <li> <div class="box"> <span class="minutes">' +
            event.strftime("%M") +
            '</span> <span class="timeRef clr-2">Minutes</span> </div> </li> <li> <div class="box"> <span class="seconds">' +
            event.strftime("%S") +
            '</span> <span class="timeRef clr-3">Seconds</span> </div> </li>',
        );
      });
    });
  }
}

// ===Image Hover Script===
function onHoverthreeDmovement() {
  var tiltBlock = $(".js-tilt");
  if (tiltBlock.length) {
    $(".js-tilt").tilt({
      maxTilt: 20,
      perspective: 700,
      glare: true,
      maxGlare: 0,
    });
  }
}

// Cart Touch Spin
function cartTouchSpin() {
  if ($(".quantity-spinner").length) {
    $("input.quantity-spinner").TouchSpin({
      verticalbuttons: true,
    });
  }
}

// page direction
function directionswitch() {
  if ($(".page_direction").length) {
    $(".direction_switch button").on("click", function () {
      $(".boxed_wrapper").toggleClass(function () {
        return $(this).is(".rtl, .ltr") ? "rtl ltr" : "rtl";
      });
    });
  }
}

$(function () {
  // Helper: Wait until all background images and <img> tags are loaded
  function waitForImages($el, callback) {
    var total = 0,
      count = 0;
    var elements = $el
      .find("*")
      .addBack()
      .filter(function () {
        var bg = $(this).css("background-image");
        return (bg && bg !== "none") || this.tagName === "IMG";
      });

    total = elements.length;
    if (total === 0) return callback();

    elements.each(function () {
      var $item = $(this);
      var bg = $item.css("background-image");
      var urlMatch = /url\(["']?([^"')]+)["']?\)/.exec(bg);
      var img = new Image();

      img.onload = img.onerror = function () {
        count++;
        if (count === total) callback();
      };

      if ($item.is("img")) {
        img.src = $item.attr("src");
      } else if (urlMatch) {
        img.src = urlMatch[1];
      } else {
        count++;
        if (count === total) callback();
      }
    });
  }

  function initCarouselAfterImages($el, options) {
    waitForImages($el, function () {
      $el.owlCarousel(options);
    });
  }

  // Theme Carousel
  if ($(".theme-carousel").length) {
    $(".theme-carousel").each(function () {
      var $owlAttr = {},
        $extraAttr = $(this).data("options");
      $.extend($owlAttr, $extraAttr);
      initCarouselAfterImages($(this), $owlAttr);
    });
  }

  // Slider Carousel
  if ($(".slider-carousel").length) {
    var $carousel = $(".slider-carousel");
    var options = {
      items: 1,
      loop: true,
      nav: true,
      dots: false,
      autoplay: true,
      autoplayTimeout: 5000,
      navText: [
        '<span class="flaticon-left-arrow" title="left-arrow"></span>',
        '<span class="flaticon-right-arrow" title="right-arrow"></span>',
      ],
      autoHeight: true,
    };

    initCarouselAfterImages($carousel, options);

    $(window).on("resize", function () {
      $carousel.trigger("refresh.owl.carousel");
    });
  }

  // RTL Carousel
  if ($(".banner-carousel-rtl").length) {
    var $rtl = $(".banner-carousel-rtl");
    var rtlOptions = {
      animateOut: "fadeOut",
      animateIn: "fadeIn",
      loop: true,
      margin: 0,
      dots: false,
      nav: true,
      items: 1,
      smartSpeed: 1500,
      autoplay: true,
      rtl: true,
      autoplayTimeout: 6000,
      navText: [
        '<span class="fas fa fa-angle-left"></span>',
        '<span class="fas fa fa-angle-right"></span>',
      ],
    };
    initCarouselAfterImages($rtl, rtlOptions);
  }
});

// Main Slider Carousel
if ($(".banner-carousel-rtl").length) {
  $(".banner-carousel-rtl").owlCarousel({
    animateOut: "fadeOut",
    animateIn: "fadeIn",
    loop: true,
    margin: 0,
    dots: false,
    nav: true,
    singleItem: true,
    smartSpeed: 1500,
    autoplay: true,
    rtl: true,
    autoplayTimeout: 6000,
    navText: [
      '<span class="fas fa fa-angle-left"></span>',
      '<span class="fas fa fa-angle-right"></span>',
    ],
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      1024: {
        items: 1,
      },
    },
  });
}

//  Partner Carousel
if ($(".partner-carousel").length) {
  $(".partner-carousel").owlCarousel({
    loop: true,
    margin: 0,
    dots: false,
    nav: false,
    stagePadding: 0,
    singleItem: true,
    smartSpeed: 500,
    autoplay: true,
    autoplayTimeout: 6000,
    navText: [
      '<span class="flaticon-next left"></span>',
      '<span class="flaticon-next right"></span>',
    ],
    responsive: {
      0: {
        items: 2,
      },
      600: {
        items: 3,
      },
      1024: {
        items: 5,
      },
    },
  });
}

// One Item Carousel
if ($(".one-item-carousel").length) {
  $(".one-item-carousel").owlCarousel({
    loop: true,
    margin: 50,
    dots: false,
    nav: true,
    stagePadding: 0,
    singleItem: true,
    smartSpeed: 500,
    autoplay: true,
    autoplayTimeout: 6000,
    navText: [
      '<span class="fa fa-angle-left left"></span>',
      '<span class="fa fa-angle-right right"></span>',
    ],
    responsive: {
      0: {
        items: 1,
      },
      768: {
        items: 1,
      },
      850: {
        items: 1,
      },
      992: {
        items: 1,
      },
      1200: {
        items: 1,
      },
    },
  });
}

// Three Item Carousel
if ($(".three-item-carousel").length) {
  $(".three-item-carousel").owlCarousel({
    loop: true,
    margin: 50,
    dots: true,
    nav: false,
    stagePadding: 0,
    singleItem: true,
    smartSpeed: 500,
    autoplay: true,
    autoplayTimeout: 6000,
    navText: [
      '<span class="fa fa-long-arrow-left"></span><p>Prev</p>',
      '<span class="fa fa-long-arrow-right right"></span><p>Next</p>',
    ],
    responsive: {
      0: {
        items: 1,
      },
      768: {
        items: 1,
      },
      850: {
        items: 2,
      },
      992: {
        items: 2,
      },
      1200: {
        items: 3,
      },
    },
  });
}

//Client Testimonial Carousel
if (
  $(".client-testimonial-carousel").length &&
  $(".client-thumbs-carousel").length
) {
  var $sync3 = $(".client-testimonial-carousel"),
    $sync4 = $(".client-thumbs-carousel"),
    flag = false,
    duration = 500;

  $sync3
    .owlCarousel({
      loop: true,
      items: 1,
      margin: 0,
      nav: true,
      navText: [
        '<span class="fa fa-angle-left"></span>',
        '<span class="fa fa-angle-right"></span>',
      ],
      dots: false,
      autoplay: true,
      autoplayTimeout: 5000,
    })
    .on("changed.owl.carousel", function (e) {
      if (!flag) {
        flag = false;
        $sync4.trigger("to.owl.carousel", [e.item.index, duration, true]);
        flag = false;
      }
    });

  $sync4
    .owlCarousel({
      loop: true,
      margin: 0,
      items: 1,
      nav: false,
      navText: [
        '<span class="icon fa fa-long-arrow-left"></span>',
        '<span class="icon fa fa-long-arrow-right"></span>',
      ],
      dots: true,
      center: false,
      autoplay: true,
      autoplayTimeout: 5000,
      responsive: {
        0: {
          items: 1,
          autoWidth: false,
        },
        400: {
          items: 1,
          autoWidth: false,
        },
        600: {
          items: 1,
          autoWidth: false,
        },
        1000: {
          items: 1,
          autoWidth: false,
        },
        1200: {
          items: 1,
          autoWidth: false,
        },
      },
    })

    .on("click", ".owl-item", function () {
      $sync3.trigger("to.owl.carousel", [$(this).index(), duration, true]);
    })
    .on("changed.owl.carousel", function (e) {
      if (!flag) {
        flag = true;
        $sync3.trigger("to.owl.carousel", [e.item.index, duration, true]);
        flag = false;
      }
    });
}

//=== Team Carousel===
if ($(".team-carousel").length) {
  $(".team-carousel").owlCarousel({
    dots: false,
    loop: true,
    margin: 30,
    nav: true,
    navText: [
      '<i class="fa fa-angle-left" aria-hidden="true"></i>',
      '<i class="fa fa-angle-right" aria-hidden="true"></i>',
    ],
    autoplayHoverPause: false,
    autoplay: 20000,
    smartSpeed: 2000,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      800: {
        items: 2,
      },
      1024: {
        items: 2,
      },
      1100: {
        items: 3,
      },
      1200: {
        items: 4,
      },
    },
  });
}

//=== Service Style3 Carousel===
if ($(".service-style3_carousel").length) {
  $(".service-style3_carousel").owlCarousel({
    dots: false,
    loop: true,
    margin: 30,
    nav: true,
    navText: [
      '<i class="fa fa-angle-left" aria-hidden="true"></i>',
      '<i class="fa fa-angle-right" aria-hidden="true"></i>',
    ],
    autoplayHoverPause: false,
    autoplay: 20000,
    smartSpeed: 2000,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      800: {
        items: 2,
      },
      1024: {
        items: 2,
      },
      1100: {
        items: 3,
      },
      1200: {
        items: 3,
      },
    },
  });
}

//  Testimonial Carousel
if ($(".testimonial-style2-carousel").length) {
  $(".testimonial-style2-carousel").owlCarousel({
    loop: true,
    margin: 50,
    dots: false,
    nav: false,
    stagePadding: 0,
    singleItem: true,
    smartSpeed: 500,
    autoplay: true,
    autoplayTimeout: 6000,
    navText: [
      '<span class="flaticon-next left"></span>',
      '<span class="flaticon-next right"></span>',
    ],
    responsive: {
      0: {
        items: 1,
      },
      992: {
        items: 2,
      },
      1399: {
        items: 3,
      },
    },
  });
}

//=== Testimonial Style3 Carousel===
if ($(".testimonial-style3_Carousel").length) {
  $(".testimonial-style3_Carousel").owlCarousel({
    dots: true,
    loop: true,
    margin: 30,
    nav: false,
    navText: [
      '<i class="fa fa-angle-left" aria-hidden="true"></i>',
      '<i class="fa fa-angle-right" aria-hidden="true"></i>',
    ],
    autoplayHoverPause: false,
    autoplay: 20000,
    smartSpeed: 2000,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      800: {
        items: 1,
      },
      1024: {
        items: 2,
      },
      1100: {
        items: 2,
      },
      1200: {
        items: 2,
      },
    },
  });
}

//=== Locations Carousel===
if ($(".locations-Carousel").length) {
  $(".locations-Carousel").owlCarousel({
    dots: true,
    loop: true,
    margin: 30,
    nav: false,
    navText: [
      '<i class="fa fa-angle-left" aria-hidden="true"></i>',
      '<i class="fa fa-angle-right" aria-hidden="true"></i>',
    ],
    autoplayHoverPause: false,
    autoplay: 20000,
    smartSpeed: 2000,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      800: {
        items: 1,
      },
      1024: {
        items: 1,
      },
      1100: {
        items: 1,
      },
      1200: {
        items: 1,
      },
    },
  });
}

//=== Shop Review Carousel===
if ($(".shop-review-carousel").length) {
  $(".shop-review-carousel").owlCarousel({
    dots: false,
    loop: true,
    margin: 30,
    nav: true,
    navText: [
      '<i class="fa fa-angle-left" aria-hidden="true"></i>',
      '<i class="fa fa-angle-right" aria-hidden="true"></i>',
    ],
    autoplayHoverPause: false,
    autoplay: 20000,
    smartSpeed: 2000,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 1,
      },
      800: {
        items: 1,
      },
      1024: {
        items: 1,
      },
      1100: {
        items: 2,
      },
      1200: {
        items: 2,
      },
    },
  });
}

if ($(".dial").length) {
  $(".dial").appear(
    function () {
      var elm = $(this);
      var color = elm.attr("data-fgColor");
      var perc = elm.attr("value");
      elm.knob({
        value: 0,
        min: 0,
        max: 100,
        skin: "tron",
        readOnly: true,
        thickness: 0.25,
        dynamicDraw: true,
        displayInput: false,
      });
      $({
        value: 0,
      }).animate(
        {
          value: perc,
        },
        {
          duration: 2000,
          easing: "swing",
          progress: function () {
            elm.val(Math.ceil(this.value)).trigger("change");
          },
        },
      );
      $(this).append(function () {});
    },
    {
      accY: 20,
    },
  );
}

//Hidden Sidebar
if ($(".hidden-bar").length) {
  var hiddenBar = $(".hidden-bar");
  var hiddenBarOpener = $(".hidden-bar-opener");
  var hiddenBarCloser = $(".hidden-bar-closer");
  var navToggler = $(".nav-toggler");
  $(".hidden-bar-wrapper").mCustomScrollbar();

  //Show Sidebar
  hiddenBarOpener.on("click", function () {
    hiddenBar.toggleClass("visible-sidebar");
    navToggler.toggleClass("open");
  });

  //Hide Sidebar
  hiddenBarCloser.on("click", function () {
    hiddenBar.toggleClass("visible-sidebar");
    navToggler.toggleClass("open");
  });
}

//Progress Bar / Levels
if ($(".progress-levels .progress-box .bar-fill").length) {
  $(".progress-box .bar-fill").each(
    function () {
      $(".progress-box .bar-fill").appear(function () {
        var progressWidth = $(this).attr("data-percent");
        $(this).css("width", progressWidth + "%");
      });
    },
    { accY: 0 },
  );
}

//Fact Counter + Text Count
if ($(".count-box").length) {
  $(".count-box").appear(
    function () {
      var $t = $(this),
        n = $t.find(".count-text").attr("data-stop"),
        r = parseInt($t.find(".count-text").attr("data-speed"), 10);

      if (!$t.hasClass("counted")) {
        $t.addClass("counted");
        $({
          countNum: $t.find(".count-text").text(),
        }).animate(
          {
            countNum: n,
          },
          {
            duration: r,
            easing: "linear",
            step: function () {
              $t.find(".count-text").text(Math.floor(this.countNum));
            },
            complete: function () {
              $t.find(".count-text").text(this.countNum);
            },
          },
        );
      }
    },
    { accY: 0 },
  );
}

//Accordion Box
if ($(".accordion-box").length) {
  $(".accordion-box").on("click", ".acc-btn", function () {
    var outerBox = $(this).parents(".accordion-box");
    var target = $(this).parents(".accordion");

    if ($(this).hasClass("active") !== true) {
      $(outerBox).find(".accordion .acc-btn").removeClass("active");
    }

    if ($(this).next(".acc-content").is(":visible")) {
      return false;
    } else {
      $(this).addClass("active");
      $(outerBox).children(".accordion").removeClass("active-block");
      $(outerBox).find(".accordion").children(".acc-content").slideUp(300);
      target.addClass("active-block");
      $(this).next(".acc-content").slideDown(300);
    }
  });
}

//====== Magnific Popup
$(".video-popup").magnificPopup({
  type: "iframe",
  // other options
});

//LightBox / Fancybox
if ($(".lightbox-image").length) {
  $(".lightbox-image").fancybox({
    openEffect: "fade",
    closeEffect: "fade",

    youtube: {
      controls: 0,
      showinfo: 0,
    },

    helpers: {
      media: {},
    },
  });
}

if ($(".paroller").length) {
  $(".paroller").paroller({
    factor: -0.1, // multiplier for scrolling speed and offset, +- values for direction control
    factorLg: -0.1, // multiplier for scrolling speed and offset if window width is less than 1200px, +- values for direction control
    type: "foreground", // background, foreground
    direction: "vertical", // vertical, horizontal
  });
}

if ($(".paroller-2").length) {
  $(".paroller-2").paroller({
    factor: 0.05, // multiplier for scrolling speed and offset, +- values for direction control
    factorLg: 0.05, // multiplier for scrolling speed and offset if window width is less than 1200px, +- values for direction control
    type: "foreground", // background, foreground
    direction: "horizontal", // vertical, horizontal
  });
}

// Elements Animation
if ($(".wow").length) {
  var wow = new WOW({
    boxClass: "wow", // animated element css class (default is wow)
    animateClass: "animated", // animation css class (default is animated)
    offset: 0, // distance to the element when triggering the animation (default is 0)
    mobile: false, // trigger animations on mobile devices (default is true)
    live: true, // act on asynchronously loaded content (default is true)
  });
  wow.init();
}

// AOS Animation
if ($("[data-aos]").length) {
  AOS.init({
    duration: 1000,
    mirror: true,
  });
}

//Contact Form Validation
if ($("#contact-form").length) {
  $("#contact-form").validate({
    submitHandler: function (form) {
      var form_btn = $(form).find('button[type="submit"]');
      var form_result_div = "#form-result";
      $(form_result_div).remove();
      form_btn.before(
        '<div id="form-result" class="alert alert-success" role="alert" style="display: none;"></div>',
      );
      var form_btn_old_msg = form_btn.html();
      form_btn.html(form_btn.prop("disabled", true).data("loading-text"));
      $(form).ajaxSubmit({
        dataType: "json",
        success: function (data) {
          if ((data.status = "true")) {
            $(form).find(".form-control").val("");
          }
          form_btn.prop("disabled", false).html(form_btn_old_msg);
          $(form_result_div).html(data.message).fadeIn("slow");
          setTimeout(function () {
            $(form_result_div).fadeOut("slow");
          }, 6000);
        },
      });
    },
  });
}

// Dom Ready Function
jQuery(document).on("ready", function () {
  (function ($) {
    // add your functions
    languageSwitcher();
    searchbox();
    datepicker();
    timepicker();
    tabBox();
    cartTouchSpin();
    directionswitch();

    CounterNumberChanger();
    priceFilter();
    accordion();
    bodylayout();
    swithcerMenu();
    onHoverthreeDmovement();
    countDownTimer();
  })(jQuery);
});

jQuery(window).on("scroll", function () {
  (function ($) {
    headerStyle();
  })(jQuery);
});

// Instance Of Fuction while Window Load event
jQuery(window).on("load", function () {
  (function ($) {
    handlePreloader();
    projectMasonaryLayout();
  })(jQuery);
});

// <<<<<<<<<<<< photo Gallery>>>>>>>>>>

const gallery = document.getElementById("gallery");
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightboxImg");
const closeBtn = document.getElementById("closeBtn");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

let images = [];
let index = 0;

if (gallery) {
  gallery.addEventListener("click", (e) => {
    if (e.target.tagName === "IMG") {
      images = [...gallery.querySelectorAll("img")];
      index = images.indexOf(e.target);
      openLightbox();
    }
  });
}

function openLightbox() {
  if (lightbox && lightboxImg && images[index]) {
    lightbox.classList.add("active");
    lightboxImg.src = images[index].src;
  }
}

function closeLightbox() {
  if (lightbox) {
    lightbox.classList.remove("active");
  }
}

function nextImage() {
  if (images.length > 0 && lightboxImg) {
    index = (index + 1) % images.length;
    lightboxImg.src = images[index].src;
  }
}

function prevImage() {
  if (images.length > 0 && lightboxImg) {
    index = (index - 1 + images.length) % images.length;
    lightboxImg.src = images[index].src;
  }
}

if (closeBtn) closeBtn.onclick = closeLightbox;
if (nextBtn) nextBtn.onclick = nextImage;
if (prevBtn) prevBtn.onclick = prevImage;

if (lightbox) {
  lightbox.onclick = (e) => {
    if (e.target === lightbox) closeLightbox();
  };
}

document.addEventListener("keydown", (e) => {
  if (lightbox && lightbox.classList.contains("active")) {
    if (e.key === "Escape") closeLightbox();
    if (e.key === "ArrowRight") nextImage();
    if (e.key === "ArrowLeft") prevImage();
  }
});
$(window).enllax();
