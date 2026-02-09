"use strict";

define([
    'jquery',
    'core/ajax',
    'core/str',
    'core/templates',
    'theme_remui/jquery-toolbar',
    'core/notification'
], function ($, Ajax, str, templates, toolbar, Notification) {

    var categorySearch=0;
    // Globals.
    var filterobj;
    var langstrings;

    var categoryfilter = $('#categoryfilter .categoryselector');

    var categorylink = '[category-filter-link]';

    var cardswrapperareadurata = $('.course-cards-durata');
    var singleitemsdurata = $('.single-item-durata');
    var button_mostra_altrodurata = $('#button_mostra_altro_durata');
    var linkdurata = $('.linkdurata');

    var cardswrapperareastato = $('.course-cards-stato');
    var singleitemsstato = $('.single-item-stato');
    var button_mostra_altrostato = $('#button_mostra_altro_stato');
    var linkstato = $('.linkstato');

    var cardswrapperareabrand = $('.course-cards-brand');
    var singleitemsbrand = $('.single-item-brand');
    var button_mostra_altrobrand = $('#button_mostra_altro_brand');
    var linkbrand = $('.linkbrand');

    var cardswrapperareapriority = $('.course-cards-priority');
    var singleitemspriority = $('.single-item-priority');
    var button_mostra_altropriority = $('#button_mostra_altro_priority');
    var linkpriority = $('.linkpriority');

    var globalCoursesOfShow;

    var cardswrapperarea = $('.course-cards0');
    var cardspagination = $('.cards-pagination');
    var numElementsOfShow = 15;
    var numElementsOfShowTemp = numElementsOfShow;
    var globalElementsOfShow = numElementsOfShow;
    var incrementElemsofShow = 6;
    var button_mostra_altro_link = $('#button_mostra_altro_link');
    var coursecounter = $('.course-counter span.course-number');
    var idDaMostrare = "";
    var container_mostra_altro = $('.container_mostra_altro_default');

    var loader_id_all = $('#loader_id_all');


     $(linkdurata).on('click', function(e) {
         clearAll();
         e.preventDefault();
         showElements(button_mostra_altrodurata, cardswrapperareadurata);
         numElementsOfShow = globalElementsOfShow;
         document.getElementById(e.currentTarget.dataset.id).style.display = "";
         idDaMostrare = e.currentTarget.dataset.id;
         globalCoursesOfShow = $('.individual-course-durata_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
         coursecounter.text( globalCoursesOfShow.length);
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altrodurata).hide();
         else $(button_mostra_altrodurata).show();
         categorySearch = "linkdurata";
         getCourses();
         $('#categoryfilter').attr('style',' margin-bottom: -35px; !important');
         location.href = "#begin"; 
     });

     $(linkstato).on('click', function(e) {
         clearAll();
         e.preventDefault();
         showElements(button_mostra_altrostato, cardswrapperareastato);
         numElementsOfShow = globalElementsOfShow;
         document.getElementById(e.currentTarget.dataset.id).style.display = "";
         idDaMostrare = e.currentTarget.dataset.id;
         globalCoursesOfShow = $('.individual-course-stato_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
         coursecounter.text( globalCoursesOfShow.length);
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altrostato).hide();
         else $(button_mostra_altrostato).show();
         categorySearch = "linkstato";
         getCourses();
         $('#categoryfilter').attr('style',' margin-bottom: -35px; !important');
         location.href = "#begin"; 
     });

     $(linkbrand).on('click', function(e) {
         clearAll();
        e.preventDefault();
         showElements(button_mostra_altrobrand, cardswrapperareabrand);
         numElementsOfShow = globalElementsOfShow;
         document.getElementById(e.currentTarget.dataset.id).style.display = "";
         idDaMostrare = e.currentTarget.dataset.id;
         globalCoursesOfShow = $('.individual-course-brand_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
         coursecounter.text( globalCoursesOfShow.length);
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altrobrand).hide();
         else $(button_mostra_altrobrand).show();
         categorySearch = "linkbrand";
         getCourses();
         $('#categoryfilter').attr('style',' margin-bottom: -35px; !important');
         location.href = "#begin"; 
     });

     $(linkpriority).on('click', function(e) {
         clearAll();
         e.preventDefault();
         showElements(button_mostra_altropriority, cardswrapperareapriority);
         numElementsOfShow = globalElementsOfShow;
         document.getElementById(e.currentTarget.dataset.id).style.display = "";
         idDaMostrare = e.currentTarget.dataset.id;
         globalCoursesOfShow = $('.individual-course-priority_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
         coursecounter.text( globalCoursesOfShow.length);
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altropriority).hide();
         else $(button_mostra_altropriority).show();
         categorySearch = "linkpriority";
         getCourses();
         $('#categoryfilter').attr('style',' margin-bottom: -35px; !important');
          window.location.href = "#begin"; 
     });

     $(button_mostra_altrodurata).on('click', function(e) {
         e.preventDefault();
         numElementsOfShow+=incrementElemsofShow; ;
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altrodurata).hide();
         else $(button_mostra_altrodurata).show();
         globalCoursesOfShow = $('.individual-course-durata_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
     });

    $(button_mostra_altrostato).on('click', function(e) {
         e.preventDefault();
         numElementsOfShow+=incrementElemsofShow;
         $('.individual-course-stato').hide();
         globalCoursesOfShow = $('.individual-course-stato_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altrostato).hide();
         else $(button_mostra_altrostato).show();
     });

    $(button_mostra_altrobrand).on('click', function(e) {
         e.preventDefault();
         numElementsOfShow+=incrementElemsofShow;
         $('.individual-course-brand').hide();
         globalCoursesOfShow = $('.individual-course-brand_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altrobrand).hide();
         else $(button_mostra_altrobrand).show();
     });

    $(button_mostra_altropriority).on('click', function(e) {
         e.preventDefault();
         numElementsOfShow+=incrementElemsofShow;
         $('.individual-course-priority').hide();
         globalCoursesOfShow = $('.individual-course-priority_'+idDaMostrare); 
         globalCoursesOfShow.slice(0,numElementsOfShow).show();
         if( globalCoursesOfShow.length <= numElementsOfShow) $(button_mostra_altropriority).hide();
         else $(button_mostra_altropriority).show();
     });

    $(button_mostra_altro_link).on('click', function(e) {
         e.preventDefault();
         numElementsOfShow+=incrementElemsofShow; 
         $('#categoryfilter').attr('style',' margin-bottom: 0px; !important');
         showElements(cardswrapperarea, cardspagination, $('.simplesearchform'), $('.singleselect'), container_mostra_altro);
         hideElements(button_mostra_altrodurata, cardswrapperareadurata, singleitemsdurata);
         updatePage();
    });

    var pageheaderactions = '.page-header-actionss';
    // View templates.
    var gridtemplate = 'theme_remui/course_card_grid';

    var searchfilter = $('#categoryfilter .simplesearchform .simplesearchform');
    
    var mycoursescheckbox = $('.custom-switch');


    var tagswrapper = $('.course-tags .tag-wrapper');

    /**
     * Main category filters class.
     * @param  {Integer} defaultCategory Default category to select.
     * @return {Object}  Filter object
     */
    var categoryFilters = function(defaultCategory) {

        var _pageobj = {courses: 0, mycourses: 0};
        var _obj = {
            // Category id.
            category: defaultCategory,
            // Sorting.
            sort: null,
            // Searching string.
            search: "",
            // If true, means mycourses tab is active.
            tab: false,
            // This object consist of page number that is currently active, has mycourses and all courses tab page number.
            page: _pageobj,
            // If True, regenerate the pagination on any action performed.
            pagination: true,
            // Initially it is null to detect initial change in view, String grid - view in grid format, String list - list format.
            // view: null,
            // This filterModified true will tell that we need to fetch the courses otherwise show old fetched data.
            isFilterModified: true
        };

        _obj.initAttributes = function() {
            _obj.category = defaultCategory;
            _obj.sort = 'ASC';
            _obj.search = '';
            _obj.tab = false;
            _obj.page = _pageobj;
            _obj.pagination = true;
            // _obj.view = null;
            _obj.isFilterModified = true;
        };

        _obj.initPagination = function() {
            _obj.page = {courses: 0, mycourses: 0};
        };
        return _obj;
    }

    function hideElements(...args){
        args.forEach(element => element.hide());
    }

    function showElements(...args){
        args.forEach(element => element.show());
    }

    function clearAll(){
        $('.singleselect').attr('style','display:none !important');
        hideElements(loader_id_all, container_mostra_altro, cardswrapperarea, cardspagination, $('.simplesearchform'), button_mostra_altrodurata, button_mostra_altro_link);
        hideElements(cardswrapperareadurata, $('.single-item-durata'), $('.individual-course-durata'), button_mostra_altrodurata);
        hideElements(cardswrapperareastato, $('.single-item-stato'), $('.individual-course-stato'), button_mostra_altrostato);
        hideElements(cardswrapperareabrand, $('.single-item-brand'), $('.individual-course-brand'), button_mostra_altrobrand);
        hideElements(cardswrapperareapriority, $('.single-item-priority'), $('.individual-course-priority'), button_mostra_altropriority);
    }

    /**
     * Filters Generation
     * @param  {Object} filterdata Filter data
     */
    function generateFilters(filterdata) {
        // $(".selectpicker").each(function() {
        //     $(this).selectpicker();
        // });
        if (filterdata.category !== "") {
            $("#categoryfilter .categoryselector").val(filterdata.category);
        }

        if (filterdata.tab == true) {
            $('#switch-label1, #switch-label2').prop('checked', true);
        } else {
            $('#switch-label1, #switch-label2').prop('checked', false);
        }

        // if (filterdata.sort !== null) {
        //     $("#sortfilter.selectpicker").selectpicker('val', filterdata.sort);
        // }

        if (filterdata.search !== "") {
            $("#coursesearchbox").val(filterdata.search);
        }

        // Put animation over here.
        // $(".category-filters").removeClass('d-none');
    }

    /**
     * Update page content
     */
    function updatePage() {
        loader_id_all.show();
        // Destroy the cards from page.
        destroyCourseCards();
        // Create courses cards again.
        generateCourseCards();
        setTimeout(hideLoading, 2000);
    }

    /*
     * Populate the tags section.
     */
    function populate_tags(){
        var serviceName = 'theme_remui_get_tags';
        var getcourses = Ajax.call([{
            methodname: serviceName,
            args: {
                data: JSON.stringify(filterobj)
            }
        }]);
        getcourses[0].done(function(response) {
            tagswrapper.empty().append(response);
        }).fail(Notification.exception);
    }

    /**
     * Course cards initialization function.
     */
    function generateCourseCards() {
        // Check if Filters are modified and need to fetch the courses.
        if (!filterobj.isFilterModified) {
            return;
        }
        // Fetch the courses.
        getCourses();
    }
    /**
     * Destroy courses cards
     */
    function destroyCourseCards() {
        window.scrollTo(0, 0);
        // Find active tab to append the course cards.
        // var destroytab = (filterobj.tab) ? mycoursesregion : coursesregion;
        // Empty the courses region.
        $(cardswrapperarea).empty();

        // Destroy the pagination also.
        if (filterobj.pagination) {
            // var destroypagination = (filterobj.tab) ? mycoursespagination : coursespagination;
            // $(destroypagination).empty();
            cardspagination.empty();
        }
    }

    /**
     * Ajax to fetch the course and also append those courses to the page.
     * If pagination is enabled it will also generate new pagination.
     */
    function getCourses() {
        loader_id_all.show();
        $('.courses-tabs .courses-loader-wrap').show();
        // Find active tab to append the course cards.
        // var appendtab = (filterobj.tab) ? mycoursesregion : coursesregion;
        // var appendpagination = (filterobj.tab) ? mycoursespagination : coursespagination;
        var serviceName = 'theme_remui_get_courses';
        // settare correttamente il nome del servizio
        filterobj.pagination=null;
        var getcourses = Ajax.call([{
            methodname: serviceName,
            args: {
                data: JSON.stringify(filterobj)
            }
        }]);
        getcourses[0].done(function(response) {
            response = JSON.parse(response);
            


            // Empty the action button on top header, and add new ones.
            $(pageheaderactions).empty();
            if (response.hasmanagebutton == true) {
                $(pageheaderactions).append(response.managebuttons);
            }

            // Show category management dropdown button when user has 'moodle/category:manage' capability.
            if (response.dropdown != undefined) {
                $('#page-header .page-header-actionss').append(response.dropdown);
            } else {
                $('#page-header .page-header-actionss [data-enhance="moodle-core-actionmenu"]').remove();
            }

            // Get the view.
            // var viewobj = (filterobj.view === null) ? response.view : filterobj.view;
            var viewobj = 'grid'; // View is always in grid form.

            // Select the template to render according to view.
            // var rendertemplate = (viewobj == 'grid' || response.latest_card) ? gridtemplate : listtemplate;
            var rendertemplate = gridtemplate;

            // Always render grid teplate on mobile screen and when latest cards setting is on.
            // if (window.screen.width <= 480 || response.latest_card) {
            //     rendertemplate = gridtemplate;
            //     viewobj = 'grid';
            // }

            // Update the view.
            // updateView(viewobj);

            // updateCards(response.latest_card);

            var courses = response.courses;
            if( categorySearch != 'linkpriority' && categorySearch != 'linkdurata' && categorySearch != 'linkstato' && categorySearch != 'linkbrand') { 
                container_mostra_altro.show();
                coursecounter.text(courses.length);
                if(numElementsOfShow == numElementsOfShowTemp) location.href = "#begin"; 
            }
            if( courses.length <= numElementsOfShow) $(button_mostra_altro_link).hide();
            else $(button_mostra_altro_link).show();
            courses = courses.slice(0,numElementsOfShow);
            if (response.latest_card) {
                cardswrapperarea.addClass('latest-cards');
            }
            $(coursecounter).text(response.totalcoursescount);
            // $(coursecounter).text(courses.totalcoursescount); se voglio solo i miei corsi
            if (courses.length > 0) {
                for (var i = 0; i < courses.length; i++) {
                    // This will call the function to load and render our template.
                    templates.render(rendertemplate, courses[i])
                    // It returns a promise that needs to be resoved.
                    /* eslint no-loop-func: 0 */
                    .then(function(html, js) {
                        // Here eventually I have my compiled template, and any javascript that it generated.
                        // The templates object has append, prepend and replace functions.
                        // templates.appendNodeContents(appendtab, html, js);
                        templates.appendNodeContents(cardswrapperarea, html, js);

                        // Show options button on course card.
                        // check if not mycourse tab.
                        // This is very bad code, couldn't do it another way.
                        // it get called each time a single card is added to dom, try to improve it.
                        // if (!filterobj.tab && !response.latest_card) {
                        if (!response.latest_card) {
                            /* eslint promise/always-return: 0 */
                            $('.course-cards .showoptions').each(function() {
                                $(this).toolbar({
                                    content: $(this).data('toolbar'),
                                    style: 'primary'
                                });
                            });
                        }
                        if( categorySearch != 'linkpriority' && categorySearch != 'linkdurata' && categorySearch != 'linkstato' && categorySearch != 'linkbrand') {
                            setTimeout(hideLoading, 2000);   
                        }
                        else {hideLoading()}
                    }).fail(Notification.exception);
                }

            } else {
                var htmldata = '<div class="alert alert-warning alert-dismissible  w-full mx-10" role="alert">';
                htmldata += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">×</span><span class="sr-only">Close</span></button>' + langstrings[0]+ '</div>';
                templates.appendNodeContents(cardswrapperarea, htmldata, '');

            }

            // // Pagination html.
            // // Check if pagination is enabled.
            if (filterobj.pagination) {
                cardspagination.empty();
                templates.appendNodeContents(cardspagination, response.pagination, '');
            }
            // $('.courses-tabs .courses-loader-wrap').hide();
        }).fail(Notification.exception);
    }

    // This is for, Toolbar redirection not working.
    $(document).delegate('.tool-item', 'click', function() {
        window.location = $(this).attr('href');
    });

    $(categoryfilter).on('change', function(e) {
        loader_id_all.show();
        categorySearch = "nolink";
        numElementsOfShow = globalElementsOfShow;
        $('#categoryfilter').attr('style',' margin-bottom: 0px; !important');
        showElements(button_mostra_altro_link, cardswrapperarea, cardspagination, $('.simplesearchform'), $('.singleselect'));
        hideElements(button_mostra_altrodurata, cardswrapperareadurata, singleitemsdurata);
        hideElements(button_mostra_altrostato, cardswrapperareastato, singleitemsstato);
        filterobj.category = e.target.value;
        filterobj.initPagination();
        window.history.replaceState('pagechange', document.title, M.cfg.wwwroot + '/course/index.php?category=' +
                    encodeURI(e.target.value));
        updatePage();
        populate_tags();
        setTimeout(hideLoading, 2000);
    });

    // Search Filter.
    $(searchfilter).on('submit', function(e) {
        loader_id_all.show();
        e.preventDefault();
        filterobj.initPagination();
        filterobj.search = $('#categoryfilter .simplesearchform .input-group input[type="text"]').val();
        updatePage();
        setTimeout(hideLoading, 2000);
    });

    $('#switch-label1, #switch-label2').on('change.bootstrapSwitch', function(e, data){
        filterobj.tab = e.target.checked;
        updatePage();
        populate_tags();
    });

    // Pagination Click Event.
    $(document).delegate('.cards-pagination .pagination .page-item .page-link', 'click', function(e) {
        e.preventDefault();
        // Update the page number in object for mycourses as well as all courses tab.
        var linkdata = e.target.href;
        if (linkdata === undefined) {
            linkdata = e.target.parentElement.href;
            if (linkdata === undefined) {
                linkdata = e.target.parentElement.parentElement.href;
            }
        }

        var hashes = linkdata.slice(linkdata.indexOf('?') + 1).split('&');
        var vars = [],
        hash;
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }

        if (filterobj.tab) {
            filterobj.page.mycourses = vars.page;
        } else {
            filterobj.page.courses = vars.page;
        }

        updatePage();
    });

    function hideLoading(){
        loader_id_all.hide();
    }

    var init = function(defaultCategory) {
        hideElements(cardswrapperareadurata, button_mostra_altrodurata);
        $(categorylink).on('click', function(event) {
            loader_id_all.show();
            categorySearch = "nolink";
            numElementsOfShow = globalElementsOfShow;
            showElements(button_mostra_altro_link, cardswrapperarea, cardspagination);
            hideElements(cardswrapperareadurata, singleitemsdurata);
            hideElements(cardswrapperareastato, singleitemsstato);
            hideElements(cardswrapperareabrand, singleitemsbrand);
            hideElements(cardswrapperareapriority, singleitemspriority);
            event.preventDefault();
            $(categoryfilter).val($(this).data('id')).trigger('change');
            return false;
        });
        var strings = [
            {
                key: 'nocoursefound',
                component: 'theme_remui'
            }
        ];
        str.get_strings(strings).then(function (stringres) {
            langstrings = stringres;

            filterobj = categoryFilters(defaultCategory); // Global object for filters.
            // Initialize global filter object with default values.

            var vars = [],
            hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            
            if (vars.categoryid && vars.categoryid != 0) {
                filterobj.category = vars.categoryid;
            }

            if (vars.categorysort != undefined) {
                filterobj.sort = vars.categorysort;
            }

            if (vars.search != undefined) {
                filterobj.search = vars.search;
            }

            if (vars.mycourses && vars.mycourses != 0) {
                filterobj.tab = true;
                if ($("body").hasClass("notloggedin")) {
                    filterobj.tab = false;
                }
            }

            generateFilters(filterobj); // This will create filters.
            generateCourseCards(); // Course cards Generation.
            populate_tags();
            setTimeout(hideLoading, 2000);
        });
    }

    return {
     init: init
    };
});
