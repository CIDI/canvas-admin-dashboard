$(function() {
			// Sort the list of colleges alphabetically
			$.fn.sortMainList = function() {
				var mylist = $(this);
				var listitems = $('li:not("ol li")', mylist).get();
				listitems.sort(function(a, b) {
				    var compA = $.trim($(a).text()).toUpperCase();
				    var compB = $.trim($(b).text()).toUpperCase();
				    return (compA < compB) ? -1 : 1;
				});
				$.each(listitems, function(i, itm) {
				    mylist.append(itm);
				});
			}
			// Sort department lists alphabetically
			$.fn.sortSubList = function() {
				var mylist = $(this);
				var listitems = $('li', mylist).get();
				listitems.sort(function(a, b) {
				    var compA = $.trim($(a).text()).toUpperCase();
				    var compB = $.trim($(b).text()).toUpperCase();
				    return (compA < compB) ? -1 : 1;
				});
				$.each(listitems, function(i, itm) {
				    mylist.append(itm);
				});
			}
			// For each account (should only be one) get the subaccount data
			$("h2").each(function (e){
				var accountID = $(this).attr("rel");
				$.get('reportData/getSubaccounts.php?accountID='+accountID, function(data) {
				    $('#subaccounts_'+accountID).html(data);
				    $("ul").each(function(){
						$(this).sortMainList();
					});
					$("ol").each(function(){
						$(this).sortSubList();
					})
				    bindButtons();
				});
			});
			// Sends the data to a JSON file when clicked
			$('.generateData').click(function (e){
				e.preventDefault();
				gatherData();
				formSubmit();
				$("#formData").submit();
			})
		});
		function bindButtons(){
			$(".removeCollege").click(function (e){
				e.preventDefault();
				$(this).parents(".college").addClass("exclude").removeClass("include").find(".subaccounts").hide();
				$(this).parents(".college").find(".department").addClass("exclude").removeClass("include")
			});
			$(".removeDept").click(function (e){
				e.preventDefault();
				$(this).parents(".department").addClass("exclude").removeClass("include");
			});

			$(".addCollege").click(function (e){
				e.preventDefault();
				$(this).parents(".college").addClass("include").removeClass("exclude").find(".subaccounts").show();
				$(this).parents(".college").find(".department").addClass("include").removeClass("exclude");
			});
			$(".addDept").click(function (e){
				e.preventDefault();
				$(this).parents("li").addClass("include").removeClass("exclude");
			});
			$(".well a").tooltip({delay:1000});
		}
		function gatherData(){
            collegeData = '';
			departmentData = '';
			// Loop through all of the colleges and departments to include and save information to hidden input
			// Those to add (lists will be exploded on ^ and | to process colleges and departments)
			$('.college.include').each(function(){
				collegeName = $(this).find('.collegeName').text();
				collegeID = $(this).attr("rel");
                collegeData += collegeID + '^' + collegeName + '^include|'; 
				$(this).find('.include').each(function(){
					departmentName = $(this).find('.deptName').text();
					deptID = $(this).attr('rel');
					departmentData += deptID + '^' + collegeID + '^' + departmentName + '^include|';
				});
				$(this).find('.exclude').each(function(){
					departmentName = $(this).find('.deptName').text();
					deptID = $(this).attr('rel');
					departmentData += deptID + '^' + collegeID + '^' + departmentName + '^exclude|';
				});
			});
			// Those to remove
			$('.college.exclude').each(function(){
				collegeName = $(this).find('.collegeName').text();
				collegeID = $(this).attr("rel");
                collegeData += collegeID + '^' + collegeName + '^exclude|'; 
				$(this).find('.include').each(function(){
					departmentName = $(this).find('.deptName').text();
					deptID = $(this).attr('rel');
					departmentData += deptID + '^' + collegeID + '^' + departmentName + '^include|';
				});
				$(this).find('.exclude').each(function(){
					departmentName = $(this).find('.deptName').text();
					deptID = $(this).attr('rel');
					departmentData += deptID + '^' + collegeID + '^' + departmentName + '^exclude|';
				});
			});
            $('#collegeData').text(collegeData);
			$('#departmentData').text(departmentData);
		}
		function formSubmit(){
			// variable to hold request
			var request;
			// bind to the submit event of our form
			$("#formData").submit(function(event){
			    // abort any pending request
			    if (request) {
			        request.abort();
			    }
			    // setup some local variables
			    var $form = $(this);
			    // let's select and cache all the fields
			    var $inputs = $form.find("input, select, button, textarea");
			    // serialize the data in the form
			    var serializedData = $form.serialize();

			    // let's disable the inputs for the duration of the ajax request
			    $inputs.prop("disabled", true);

			    // fire off the request
			    request = $.ajax({
			        url: "resources/tasks.php?task=collegeList",
			        type: "post",
			        data: serializedData
			    });

			    // callback handler that will be called on success
			    request.done(function (response, textStatus, jqXHR){
			        // log a message to the console
			        $(".message").slideDown().delay(3000).slideUp();
			        $(".trackerLink").show();
			    });

			    // callback handler that will be called on failure
			    request.fail(function (jqXHR, textStatus, errorThrown){
			        // log the error to the console
			        console.error(
			            "The following error occured: "+
			            textStatus, errorThrown
			        );
			    });

			    // callback handler that will be called regardless
			    // if the request failed or succeeded
			    request.always(function () {
			        // reenable the inputs
			        $inputs.prop("disabled", false);
			    });

			    // prevent default posting of form
			    event.preventDefault();
			});
		}