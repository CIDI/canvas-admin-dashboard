/*jslint browser: true, sloppy: false, eqeq: false, vars: false, maxerr: 5, indent: 4, plusplus: true */
/*global $ */
var grandTotal = 0,
    notUsingCanvasTotal = 0,
    notUsingCanvasPer = 0,
    usingCanvasTotal = 0,
    usingCanvasPer = 0,
    noSyllabusTotal = 0,
    noSyllabusPer = 0,
    hasSyllabusTotal = 0,
    hasSyllabusPer = 0,
    usedWizardTotal = 0,
    usedWizardPer = 0;

function checkTotals() {
    'use strict';
    grandTotal = $(".courses li:visible").length;
    notUsingCanvasTotal = $(".courses .fa-times-circle:visible").length;
    notUsingCanvasPer = Math.floor((notUsingCanvasTotal / grandTotal) * 100);
    usingCanvasTotal = grandTotal - notUsingCanvasTotal;
    usingCanvasPer = Math.floor((usingCanvasTotal / grandTotal) * 100);
    noSyllabusTotal = $(".courses .fa-question-circle:visible").length;
    noSyllabusPer = Math.floor((noSyllabusTotal / usingCanvasTotal) * 100);
    hasSyllabusTotal = $(".courses .fa-check-circle:visible").length;
    hasSyllabusPer = Math.floor((hasSyllabusTotal / usingCanvasTotal) * 100);
    usedWizardTotal = $(".courses .icon-magic:visible").length;
    usedWizardPer = Math.floor((usedWizardTotal / usingCanvasTotal) * 100);

    $('.grandTotal').html(grandTotal);
    $('.notUsingCanvasTotal').html(notUsingCanvasTotal);
    $('.notUsingCanvasPer').html(notUsingCanvasPer + '%');
    $('.usingCanvasTotal').html(usingCanvasTotal);
    $('.usingCanvasPer').html(usingCanvasPer + '%');
    $('.noSyllabusTotal').html(noSyllabusTotal);
    $('.noSyllabusPer').html(noSyllabusPer + '%');
    $('.hasSyllabusTotal').html(hasSyllabusTotal);
    $('.hasSyllabusPer').html(hasSyllabusPer + '%');
    $('.usedWizardTotal').html(usedWizardTotal);
    $('.usedWizardPer').html(usedWizardPer + '%');

    $('#canvasUse').highcharts({
        chart: {type: 'column'},
        colors: [
            '#999999',
            '#428bca'
        ],
        title: {text: null },
        xAxis: {categories: ['Canvas Usage'] },
        yAxis: {
            min: 0,
            title: {text: 'Course Percentage'}
        },
        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
            shared: true
        },
        plotOptions: {
            column: {stacking: 'percent'}
        },
        series: [{
            name: 'Not In Canvas',
            data: [notUsingCanvasTotal]
        }, {
            name: 'In Canvas',
            data: [usingCanvasTotal]
        }]
    });
    $('#syllabusState').highcharts({
        chart: {type: 'column'},
        colors: [
            '#B94A48',
            '#468847'
        ],
        title: {text: null },
        xAxis: {categories: ['Syllabus Usage'] },
        yAxis: {
            min: 0,
            title: {text: '% Courses Using Canvas'}
        },
        tooltip: {
            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
            shared: true
        },
        plotOptions: {
            column: {stacking: 'percent'}
        },
        series: [{
            name: 'No Content in Syllabus Page',
            data: [noSyllabusTotal]
        }, {
            name: 'Content in Syllabus Page',
            data: [hasSyllabusTotal]
        }]
    });
    // Data specific to "All USU Courses"
    var collegeList = [],
        collegeTotalCourses = [],
        collegePublished = [],
        collegeUnpublished = [],
        collegeUsingSyllabus = [],
        collegeNotUsingSyllabus = [];
    $('h2').each(function () {
        collegeList.push($(this).text());
        collegeTotalCourses.push($(this).parents(".college-list").find('li:visible').length);
        collegePublished.push($(this).parents(".college-list").find('.using-canvas:visible').length);
        collegeUnpublished.push($(this).parents(".college-list").find('.not-using-canvas:visible').length);
        collegeUsingSyllabus.push($(this).parents(".college-list").find('.fa-check-circle:visible').length);
        collegeNotUsingSyllabus.push($(this).parents(".college-list").find('.fa-question-circle:visible').length);
        var myClass = $(this).index();
            // noSyllabus = $(this).next('.collegeGroup').find('.fa-question-circle').length,
            // hasSyllabus = $(this).next('.collegeGroup').find('.fa-check-circle').length;
            // totalCourses = noSyllabus + hasSyllabus,
            // name = $(this).html();
        $(this).before('<a name="' + myClass + '"></a>');
        $(this).append('<a class="topLink" href="#top"><i class="icon-circle-arrow-up"></i> Top</a>');
    });
    $('#collegeCount').highcharts({
        chart: {type: 'bar'},
        colors: [
            '#B94A48',                '#468847',
            '#428bca',
            '#999999',                '#000000'
        ],
        title: {
            text: 'Canvas Usage'
        },
        subtitle: {
            text: 'By College (Based on courses with student enrollments)'
        },
        xAxis: {
            categories: collegeList,
            title: {text: null }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Number of Courses',
                align: 'high'
            },
            labels: {overflow: 'justify'}
        },
        tooltip: {valueSuffix: ' Courses'},
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 100,
            floating: true,
            borderWidth: 1,
            backgroundColor: '#FFFFFF',
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: [{
            name: 'No Content in Syllabus Page',
            data: collegeNotUsingSyllabus
        }, {
            name: 'Content in Syllabus Page',
            data: collegeUsingSyllabus
        }, {
            name: 'Courses Published',
            data: collegePublished
        }, {
            name: 'Courses UnPublished',
            data: collegeUnpublished
        }, {
            name: 'Total Courses',
            data: collegeTotalCourses
        }]
    });
}
$(function () {
    'use strict';
    $("[data-toggle=tooltip]").tooltip({html: true});
    checkTotals();
});
// // the following function grays out dropdown choices that are not in the returned courses
// function checkVisible() {
//     'use strict';
//     var i, campusCode = ["CC-0", "CC-A", "CC-B", "CC-C", "CC-E", "CC-F", "CC-I", "CC-K", "CC-L", "CC-N", "CC-P", "CC-S", "CC-T", "CC-U", "CC-X", "CC-Y", "CC-Z", "DM-B", "DM-O", "DM-S", "DM-T"];
//     for (i = 0; i < campusCode.length; i++) {
//         if ($("." + campusCode[i] + ":visible").length > 0) {
//             $("#" + campusCode[i]).removeClass("disabled");
//         } else {
//             $("#" + campusCode[i]).addClass("disabled");
//         }
//     }
//     if ($(".deliverySelect:not('.disabled')").length > 0) {
//         $(".deliveryDropdown").removeClass("disabled");
//     } else {
//         $(".deliveryDropdown").addClass("disabled");
//     }
// }
// // Hide all courses then show those that fit filter criteria
// function filterResults() {
//     'use strict';
//     var showItems = "";
//     $(".courses li").hide();
//     $('.filters .active').each(function () {
//         showItems += $(this).attr("rel");
//     });
//     $(showItems).show();
//     checkTotals();
// }
// function resetFilters() {
//     'use strict';
//     $(".deliverySelect").each(function () {
//         $(this).removeClass("active");
//     });
//     $(".deliveryDropdown").removeClass("activeToggle");
//     $(".campusSelect").each(function () {
//         $(this).removeClass("active");
//     });
//     $(".campusDropdown").removeClass("activeToggle");
//     $(".courses li").show();
//     $(".selectInstructorDropdown").removeClass("activeToggle").html('Instructor <span class="caret"></span>');
//     $(".selectedInstructor").each(function () {
//         $(this).parent().removeClass("active");
//     });
//     checkTotals();
// }

// // The following function will remove duplicate entries from an array
// function getUnique(inputArray) {
//     'use strict';
//     var i, outputArray = [];
//     for (i = 0; i < inputArray.length; i++) {
//         if (($.inArray(inputArray[i], outputArray)) === -1) {
//             outputArray.push(inputArray[i]);
//         }
//     }
//     return outputArray;
// }

// // Gather totals of displayed courses and update graphs
// function getInstructors() {
//     'use strict';
//     var newArray, instructorArray = [];
//     // Gather instructor names and create a dropdown list
//     $(".instructorName").each(function () {
//         if ($(this).parents("li:visible").length > 0) {
//             instructorArray.push($(this).text());
//         }
//     });
//     instructorArray.sort();
//     $(".chooseInstructor").html("");
//     newArray = getUnique(instructorArray);
//     $.each(newArray, function (index, value) {
//         $(".chooseInstructor").append('<li><a href="#" class="selectedInstructor" rel="' + value + '">' + value + '</a></li>');
//     });
//     $(".selectedInstructor").each(function () {
//         var selectedInstructor = $(this).attr("rel"),
//             numCourses = $('.instructorName:contains(' + selectedInstructor + ')').length;
//         // var numCourses = $('.instructorName:contains("Norman Jones")').length;
//         $(this).append(" (" + numCourses + ")");
//     });
//     $('.dropdown-toggle').dropdown();
//     // Filter results when an instructor is selected
//     $(".selectedInstructor").click(function () {
//         $(".selectedInstructor").each(function () {
//             $(this).parent().removeClass("active");
//         });
//         resetFilters();
//         $(this).parent().addClass("active");
//         filterResults();
//         $(".showAll").addClass("btn-warning").removeClass("disabled");
//         var selectedInstructor = $(this).attr("rel");
//         $('.instructorName:contains(' + selectedInstructor + ')').each(function () {
//             $(this).parents("li").show();
//         });
//         $(".selectInstructorDropdown").addClass("activeToggle").html(selectedInstructor + ' <span class="caret"></span>');
//     });
// }



// $(function () {
//     'use strict';
//     console.log('javascript loaded');
//     $("#college").change(function () {
//         // Determine if it is a single college or all university
//         if ($("#college").attr("value") === "") {
//             $(".retrieveAllCourses").hide();
//             $(".retrieveCourses").hide();
//         } else if ($("#college").attr("value") === "all") {
//             $(".retrieveAllCourses").show();
//             $(".retrieveCourses").hide();
//         } else {
//             $(".retrieveAllCourses").hide();
//             $(".retrieveCourses").show();
//         }
//     }).trigger("change");
//     // Retrieve data for a single college
//     $(".retrieveCourses").click(function (e) {
//         e.preventDefault();
//         // Remove bar graph for entire university
//         $("#collegeCount").remove();
//         // Provide visual feedback that something is happening
//         $(this).html('<i class="icon-spinner icon-spin icon-large"></i> Gathering Data').addClass("disabled");
//         // Retrieve college data based on which college is selected and which term
//         var indexNum = $("#college").attr("value"),
//             term = $("#term").attr("value");
//         $('.courses').load('collegeDetails.php?term=' + term + '&indexNum=' + indexNum + ' .collegeList', function () {
//             // Gather department data and populate dropdown once data is returned
//             $(".departmentList").html("");
//             $('.courses h3').each(function () {
//                 var deptTitle = $(this).text(),
//                     deptIndexNum = $(this).attr("rel");
//                 $(".departmentList").append('<li><a href="#" class="deptSelect" rel="' + deptIndexNum + '">' + deptTitle + '</li>');
//             });
//             $(".deptSelect").click(function (e) {
//                 e.preventDefault();
//                 $(".showAll").addClass("btn-warning").removeClass("disabled");
//                 $('.courses h3').hide();
//                 $('.courses ol').hide();
//                 var deptIndexNum = $(this).attr("rel");
//                 $('#deptHeading-' + deptIndexNum).show();
//                 $('#deptCourseList-' + deptIndexNum).show();
//                 checkTotals();
//                 checkVisible();
//             });
//             $(".departmentDropdown").show();
//         });
//         $(".filters").hide();
//         $(".showAll").removeClass("btn-warning").addClass("disabled");
//         $(".deliveryDropdown, .campusDropdown").removeClass("disabled");
//         resetFilters();
//     });
//     // Retrieve data for entire university
//     $(".retrieveAllCourses").click(function (e) {
//         e.preventDefault();
//         // Remove bar graphs to prevent duplication
//         $("#collegeCount").remove();
//         // Hide department dropdown
//         $(".courses").html("");
//         $(".departmentDropdown").hide();
//         // Provide visual feedback that something is happening
//         $(this).html('<i class="icon-spinner icon-spin icon-large"></i> Gathering Data').addClass("disabled");
//         // Identify selected term
//         var i,
//             term = $("#term").attr("value"),
//         // Count how many colleges there are in the dropdown
//             optionCount = $("#college option").length,
//         // account for "Choose a College" and "All USU Courses" options
//             collegeCount = optionCount - 2;
//         // Retrieve data for each college and append it to container
//         for (i = 0; i < collegeCount; i++) {
//             $.get('collegeDetails.php?term=' + term + '&indexNum=' + i, function (data) {
//                 $(data).appendTo(".courses").fadeIn("slow");
//             });
//         }
//     });
//     // Control filtering by campus
//     $(".campusSelect").click(function (e) {
//         e.preventDefault();
//         $(".campusSelect").each(function () {
//             $(this).removeClass("active");
//         });
//         $(this).addClass("active");
//         filterResults();
//         $(".showAll").addClass("btn-warning").removeClass("disabled");
//         $(".campusDropdown").addClass("activeToggle");
//     });
//     // Control filtering by delivery method
//     $(".deliverySelect").click(function (e) {
//         e.preventDefault();
//         $(".deliverySelect").each(function () {
//             $(this).removeClass("active");
//         });
//         $(this).addClass("active");
//         filterResults();
//         $(".showAll").addClass("btn-warning").removeClass("disabled");
//         $(".deliveryDropdown").addClass("activeToggle");
//     });
//     // Button to reset filters
//     $(".showAll").click(function (e) {
//         e.preventDefault();
//         resetFilters();
//         $(this).removeClass("btn-warning");
//         $(".courses li").show();
//         $(".showAll").addClass("disabled");
//         $('.courses h3').show();
//         $('.courses ol').show();
//     });
//     // Button Hovers
//     $(".usingCanvasBtn").mouseover(function () {
//         var el = $(this),
//             timeoutID = setTimeout(function () {
//                 $(".usingCanvas").addClass("hover");
//             }, 500);
//         el.mouseout(function () {
//             $(".usingCanvas").removeClass("hover");
//             clearTimeout(timeoutID);
//         });
//     });
//     $(".notUsingCanvasBtn").mouseover(function () {
//         var el = $(this),
//             timeoutID = setTimeout(function () {
//                 $(".notUsingCanvas").addClass("hover");
//             }, 500);
//         el.mouseout(function () {
//             $(".notUsingCanvas").removeClass("hover");
//             clearTimeout(timeoutID);
//         });
//     });
//     $(".hasSyllabusBtn").mouseover(function () {
//         var el = $(this),
//             timeoutID = setTimeout(function () {
//                 $(".hasSyllabus").addClass("hover");
//             }, 500);
//         el.mouseout(function () {
//             $(".hasSyllabus").removeClass("hover");
//             clearTimeout(timeoutID);
//         });
//     });
//     $(".noSyllabusBtn").mouseover(function () {
//         var el = $(this),
//             timeoutID = setTimeout(function () {
//                 $(".noSyllabus").addClass("hover");
//             }, 500);
//         el.mouseout(function () {
//             $(".noSyllabus").removeClass("hover");
//             clearTimeout(timeoutID);
//         });
//     });
//     // Button Clicks
//     $(".usingCanvasBtn").click(function (e) {
//         e.preventDefault();
//         $(".courses li").hide();
//         $(".usingCanvas").show();
//         $(".showAll").removeClass("disabled").addClass("btn-warning");
//     });
//     $(".notUsingCanvasBtn").click(function (e) {
//         e.preventDefault();
//         $(".courses li").hide();
//         $(".notUsingCanvas").show();
//         $(".showAll").removeClass("disabled").addClass("btn-warning");
//     });
//     $(".hasSyllabusBtn").click(function (e) {
//         e.preventDefault();
//         $(".courses li").hide();
//         $(".hasSyllabus").show();
//         $(".showAll").removeClass("disabled").addClass("btn-warning");
//     });
//     $(".noSyllabusBtn").click(function (e) {
//         e.preventDefault();
//         $(".courses li").hide();
//         $(".noSyllabus").show();
//         $(".showAll").removeClass("disabled").addClass("btn-warning");
//     });
//     // Data Status
//     $("#term").change(function () {
//         var selectedTerm = $("#term").attr("value");
//         if (selectedTerm !== "") {
//             $('#termInfo').load('reportData/fileData.php?term=' + selectedTerm);
//         }
//     });
//     var selectedTerm = $("#term").attr("value");
//     if (selectedTerm !== "") {
//         $('#termInfo').load('reportData/fileData.php?term=' + selectedTerm, function () {
//             if ($("#termInfo:contains('not yet generated')").length > 0) {
//                 $('.updateData').attr("title", "Data not available for<br><strong>" + $("#term option:selected").text() + "</strong").tooltip({html: true}).trigger("mouseover").focus();
//             }
//         });
//     }
//     // Data Gathering modal options
//     $("#generateTerm").change(function () {
//         $("#processComplete").hide();
//         $("#step1").attr("class", "pending").html('<i class="icon-circle-blank"></i> Create Course List');
//         $("#step2").attr("class", "pending").html('<i class="icon-circle-blank"></i> Check Student Enrollments');
//         $("#step3").attr("class", "pending").html('<i class="icon-circle-blank"></i> Gather Course Details');
//         selectedTerm = $("#generateTerm").attr("value");
//         if (selectedTerm !== "") {
//             $('#fileDetails').load('reportData/fileData.php?term=' + selectedTerm);
//         }
//         $(".generateData").show();
//     });
//     function gatherDetails(indexNum, generateTermID, numToComplete) {
//         $.get('reportData/list3-gatherDetails.php?term=' + generateTermID + '&indexNum=' + indexNum, function () {
//             var numCompleted = parseInt($("#detailsStepCount").text(), 10),
//                 percentComplete = Math.floor((numCompleted / numToComplete) * 100);
//             numCompleted++;
//             $(".generateProgress .bar").css('width', percentComplete + "%");
//             $("#detailsStepCount").text(numCompleted);

//             if (numCompleted === numToComplete) {
//                 $("#step2").attr("class", "completed").html('<i class="fa-check-circle"></i> Course Details Gathered');
//                 $("#processComplete").slideDown();
//                 $(".generateProgress").slideUp();
//                 $(".generateData").hide();
//             }
//         });
//     }
//     function checkEnrollments(indexNum, generateTermID, numToComplete) {
//         $.get('reportData/list2-checkEnrollments.php?term=' + generateTermID + '&indexNum=' + indexNum, function () {
//             var numCompleted = parseInt($("#detailsStepCount").text(), 10),
//                 percentComplete = Math.floor((numCompleted / numToComplete) * 100);
//             numCompleted++;
//             $(".generateProgress .bar").css('width', percentComplete + "%");
//             $("#detailsStepCount").text(numCompleted);
//             // Trigger next request to gather details about courses
//             gatherDetails(indexNum, generateTermID, numToComplete);
//         });
//     }
//     $(".generateData").click(function (e) {
//         e.preventDefault();
//         $(".generateProgress .bar").css('width', "0%");
//         var i,
//             generateTermID = $("#generateTerm").attr("value"),
//             optionCount = $("#college option").length,
//             // account for "Choose a College" and "All USU Courses" options
//             collegeCount = optionCount - 2,
//             numToComplete = collegeCount * 2;
//         // Retrieve data for each college and append it to container
//         if (generateTermID !== "") {
//             $("#step1").attr("class", "active").html('<i class="icon-cog icon-spin"></i> Creating Course List');
//             // Step 1 create course list
//             $.get("reportData/list1-initialCourses.php?term=" + generateTermID, function () {
//                 $(".generateProgress").slideDown();
//                 $("#step1").attr("class", "completed").html('<i class="fa-check-circle"></i> Course List Created');
//                 $("#step2").attr("class", "active").html('<i class="icon-cog icon-spin"></i> Gathering Course Details (Part <span id="detailsStepCount">0</span>/' + numToComplete + ' Complete)');
//                 // Step 2 check courses for student enrollments
//                 for (i = 0; i < collegeCount; i++) {
//                     checkEnrollments(i, generateTermID, numToComplete);
//                 }
//             });
//         }
//     });


//     // code below will be executed on completion of last outstanding ajax call
//     $(document).ajaxStop(function () {
//         if (!$("#myModal").is(":visible") || $("#college").attr("value") !== "") {
//             // Reset Retrieve buttons
//             $(".retrieveCourses").html('Retrieve Courses').removeClass("disabled");
//             $(".retrieveAllCourses").html('Retrieve All Courses').removeClass("disabled");
//             $('.syllabus').remove();
//             // Add Counts for all USU courses
//             if ($(".retrieveAllCourses:visible").length > 0) {
//                 // Put results back in original order (rather than first to return ajax response)
//                 var i,
//                     optionCount = $("#college option").length,
//                     collegeCount = optionCount - 2,
//                     chartHeight = collegeCount * 75 + 100;
//                 for (i = 0; i < collegeCount; i++) {
//                     $('.college-' + i).appendTo(".courses");
//                 }

//                 // Add in div for bar graph
//                 $('.courses').before('<div id="collegeCount" style="min-width: 310px; height: ' + chartHeight + 'px; margin: 0 auto" class="well"></div>');
//             }
//             // Check results and update totals and dropdown options
//             checkTotals();
//             checkVisible();
//             // Show filter options
//             $(".filters").slideDown();
//             // Change link title attributes to tooltips
//             $(".courses a").tooltip({html: true});
//             // Alphabetically sort department courses
//             $.fn.sortList = function () {
//                 var mylist = $(this),
//                     listitems = $('li', mylist).get();
//                 listitems.sort(function (a, b) {
//                     var compA = $.trim($(a).text()).toUpperCase(),
//                         compB = $.trim($(b).text()).toUpperCase();
//                     return (compA < compB) ? -1 : 1;
//                 });
//                 $.each(listitems, function (i, itm) {
//                     mylist.append(itm);
//                 });
//             }
//             $(".courses ol").each(function () {
//                 $(this).sortList();
//             });
//         }
//     });
//     $.ajaxSetup ({
//         // Disable caching of AJAX responses
//         cache: false
//     });
// });


